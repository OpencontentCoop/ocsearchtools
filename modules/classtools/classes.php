<?php

$module = $Params['Module'];
$tpl = eZTemplate::factory();
$classIdentifier = $Params['Identifier'];
if ( $classIdentifier )
{
    $class = eZContentClass::fetchByIdentifier( $classIdentifier );
    if ( $class instanceof eZContentClass )
    {
        $tpl->setVariable( 'class', $class );
    }
}

$tpl->setVariable( 'show_extra_link', OCClassExtraParametersManager::issetHandlers() && OCClassExtraParametersManager::currentUserCanEditHandlers() );

$Result = array();
$Result['content'] = $tpl->fetch( 'design:classtools/classes.tpl' );
$Result['path'] = array( array( 'text' => 'Classi di contenuto' ,
                                'url' => false ) );
