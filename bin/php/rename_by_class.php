<?php
require 'autoload.php';

$script = eZScript::instance( array( 'description' => ( "Rename\n\n" ),
                                     'use-session' => false,
                                     'use-modules' => true,
                                     'use-extensions' => true ) );

$script->startup();

$options = $script->getOptions( '[class:]',
                                '',
                                array( 'class'  => 'Identificatore della classe' )
);
$script->initialize();
$script->setUseDebugAccumulators( true );

$cli = eZCLI::instance();

try
{
    if ( isset( $options['class'] ) )
    {
        $classIdentifier = $options['class'];
    }
    else
    {
        throw new Exception( "Specificare la classe" );
    }
    
    $class = eZContentClass::fetchByIdentifier( $classIdentifier );
    if ( !$class instanceof eZContentClass )
    {
        throw new Exception( "Classe $classIdentifier non trovata" );
    }
    
    $objects = eZPersistentObject::fetchObjectList( eZContentObject::definition(),
                                                    array( 'id' ),
                                                    array( 'contentclass_id' => $class->attribute( 'id' ) ),
                                                    null,
                                                    null,
                                                    false );
    $ids = array();
    foreach( $objects as $object )
    {
        $ids[] = $object['id'];
    }
    
    $pendingAction = 'rename';
    
    if ( count( $ids ) > 0 )
    {
        $count = count( $ids );
        $output = new ezcConsoleOutput();
        $progressBarOptions = array( 'emptyChar' => ' ', 'barChar'  => '=' );
        $progressBarOptions['minVerbosity'] = 10;    
        $progressBar = new ezcConsoleProgressbar( $output, intval( $count ), $progressBarOptions );
        $progressBar->start();
        
        foreach( $ids as $id )
        {            
            $progressBar->advance();
            eZDB::instance()->query( "INSERT INTO ezpending_actions( action, param ) VALUES ( '$pendingAction', '$id' )" );
        }
        $progressBar->finish();
    }
    
    $script->shutdown();
}
catch( Exception $e )
{    
    $errCode = $e->getCode();
    $errCode = $errCode != 0 ? $errCode : 1; // If an error has occured, script must terminate with a status other than 0
    $script->shutdown( $errCode, $e->getMessage() );
}


$cli->output( "Starting processing pending search engine modifications" );

$eZSolr = eZSearch::getEngine();
if ( !( $eZSolr instanceof eZSolr ) )
{
    $script->shutdown( 1, 'The current search engine plugin is not eZSolr' );
}

$contentObjects = array();
$db = eZDB::instance();

$key = 'rename';


$entries = $db->arrayQuery( "SELECT param FROM ezpending_actions WHERE action = '$key'" );

if ( is_array( $entries ) && count( $entries ) != 0 )
{        
    foreach ( $entries as $entry )
    {
        $objectIDs = $entry['param'];
        $items = explode( '-', $objectIDs );
        foreach ( $items as $objectID )
        {
            $cli->output( "Renaming object ID #$objectID" );
            $object = eZContentObject::fetch( $objectID );
            
            $class = $object->contentClass();
            $object->setName( $class->contentObjectName( $object ) );
            $object->store();
            
            if ( $object )
            {                    
                $eZSolr->addObject( $object, false );
            }
            eZContentObject::clearCache();
        }
        $db->begin();
        $db->query( "DELETE FROM ezpending_actions WHERE action = '$key' AND param = '$objectIDs'" );
        $db->commit();
    }
    $eZSolr->commit();
    
}


$cli->output( "Done" );
