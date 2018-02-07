<?php
/** @var eZModule $module */
$module = $Params['Module'];
$id = $Params['ID'];
$http = eZHTTPTool::instance();
$tpl = eZTemplate::factory();
$format = 'ez';
$action = false;
if ( $http->hasVariable( 'format' ) )
{
    $format = $http->variable( 'format' );
}

$remoteRequest = false;
$remoteRequestUrl = null;
$remoteRequestSuffix = false;
if ( $http->hasVariable( 'remote' ) )
{
    $remoteRequest = $http->variable( 'remote' );
    if ( file_exists( $remoteRequest ) )
    {
        $remoteRequestUrl = $remoteRequest;
    }
    elseif( eZHTTPTool::getDataByURL( $remoteRequest ) )
    {
        $remoteRequestUrl = rtrim( $remoteRequest, '/' ) . '/classtools/definition/';
    }
    if ( $remoteRequestUrl )
        $remoteRequestSuffix = '?remote=' . $remoteRequest;
}

$remote = null;

try
{
    if ( $module->isCurrentAction( 'Install' ) )
    {        
        $tools = new OCClassTools( $id, true, array(), $remoteRequestUrl );
        $tools->sync();
        return $module->redirectTo( '/classtools/compare/' . $id . $remoteRequestSuffix );
    }
    
    $tools = new OCClassTools( $id, false, array(), $remoteRequestUrl );
    
    $remote = $tools->getRemote();
    $locale = $tools->getLocale();    
    if ( $remote === null )
    {
        throw new Exception( 'Impossibile trovare la classe remota' );
    }

    if ( $module->isCurrentAction( 'Sync' ) )
    {
        $force = false;
        $removeExtra = false;
        if ( $http->hasPostVariable( 'ForceSync' ) )
        {
            $force = $http->postVariable( 'ForceSync' ) == 1;
        }
        if ( $http->hasPostVariable( 'RemoveExtra' ) )
        {
            $removeExtra = $http->postVariable( 'RemoveExtra' ) == 1;
        }
        $tools->sync( $force, $removeExtra );

        $redirect = '/classtools/compare/' . $id . $remoteRequestSuffix;
        if (class_exists('ezpKernelWeb')){
            $redirect = '/classtools/compare/' . $id;
        }
        return $module->redirectTo( $redirect );
    }
    
    $tools->compare();
    $result = $tools->getData();                
    $tpl->setVariable( 'locale', $locale );    
    $tpl->setVariable( 'id', $id );    
    $missingLocale = array();
    foreach( $tools->getData()->missingAttributes as $item )
    {
        $missingLocale[] = new Item( $item );
    }
    $tpl->setVariable( 'missing_in_locale', $missingLocale );
    
    $missingRemote = array();
    foreach( $tools->getData()->extraAttributes as $item )
    {
        $obj = new Item( $item );
        $missingRemote[] = $obj;        
    }
    $tpl->setVariable( 'missing_in_remote', $missingRemote );
    $tpl->setVariable( 'missing_in_remote_details', $tools->getData()->extraDetails );
    
    if ( $tools->getData()->hasError || $tools->getData()->hasWarning )
    {
        $tpl->setVariable( 'diff', $tools->getData()->diffAttributes );
        $tpl->setVariable( 'warnings', $tools->getData()->warnings );
        $tpl->setVariable( 'errors', $tools->getData()->errors );            
    }
    else
    {
        $tpl->setVariable( 'diff', array() );   
        $tpl->setVariable( 'errors', array() );           
        $tpl->setVariable( 'warnings', array() );  
    }
    
    $tpl->setVariable( 'diff_properties', $tools->getData()->diffProperties );
    
}
catch( Exception $e )
{
   eZDebug::writeError( $e->getMessage(), __FILE__ );
   $result = array( 'error' => $e->getMessage() ); 
}

if ( $format == 'json' ) 
{
    header('Content-Type: application/json');
    echo json_encode( $result );    
    eZExecution::cleanExit();
}
else
{
    $tpl->setVariable( 'remote_request_suffix', $remoteRequestSuffix );
    $tpl->setVariable( 'request_id', $id );
    $tpl->setVariable( 'locale_not_found', empty( $id ) ? false : true );
    if ( $remote !== null  && ( eZContentClass::fetchByIdentifier( $id ) || eZContentClass::fetch( intval( $id ) ) ) )
    {
        $tpl->setVariable( 'locale_not_found', false );
    }
    $tpl->setVariable( 'data', $result );

    $Result = array();
    $Result['content'] = $tpl->fetch( 'design:classtools/compare.tpl' );
    $Result['node_id'] = 0;
    $contentInfoArray = array( 'url_alias' => 'classtools/classes', 'class_identifier' => null );
    $contentInfoArray['persistent_variable'] = array(
        'show_path' => true
    );
    $Result['content_info'] = $contentInfoArray;
    $Result['path'] = array(
        array(
            'text' => 'Informazioni e utilità per le classi',
            'url' => 'classtools/classes/',
            'node_id' => null
        )
    );
    if ( isset( $locale ) )
    {
        $Result['path'][] = array(
            'text' =>  $locale->attribute( 'name' ),
            'url' => 'classtools/classes/' . $locale->attribute( 'identifier' ),
            'node_id' => null
        );
    }
}


class Item
{    
    protected $item;
    public $attributes;
    function __construct( $item )
    {        
        $this->item = $item;
        foreach( $this->item as $property => $value )
        {
            $this->attributes[$property] = $this->item->{$property};
        }
    }    
    
    public function attributes()
    {        
        return array_keys( $this->attributes );
    }
    
    public function hasAttribute( $name )
    {
        return isset( $this->attributes[$name] );
    }
    
    public function attribute( $name )
    {
        if ( isset( $this->attributes[$name] ) )
        {
            if ( is_string( $this->attributes[$name] ) )
            {
                return $this->attributes[$name];  
            }
        }
        return false;
        
    }
    
}
