<?php
/** @var eZModule $module */
$module = $Params['Module'];
$tpl = eZTemplate::factory();
$classIdentifier = $Params['Identifier'];
$http = eZHTTPTool::instance();

if ( $classIdentifier )
{
    $class = eZContentClass::fetchByIdentifier( $classIdentifier );
    if ( $class instanceof eZContentClass )
    {
        $tpl->setVariable( 'class', $class );
        $extraParametersManager = OCClassExtraParametersManager::instance( $class );
        $handlers = $extraParametersManager->getHandlers();

        if ( $http->hasVariable( 'StoreExtraParameters' ) )
        {
            foreach( $handlers as $identifier => $handler )
            {
                if ( $http->hasVariable( 'extra_handler_' . $identifier ) )
                {
                    $data = $http->variable( 'extra_handler_' . $identifier );
                    $handler->storeParameters( $data );
                }
            }
            $module->redirectTo( 'classtools/extra/' . $classIdentifier );
        }

        $tpl->setVariable( 'extra_handlers', $handlers );
    }
}
$Result = array();
$Result['content'] = $tpl->fetch( 'design:classtools/extra.tpl' );
$Result['path'] = array( array( 'text' => 'Classi di contenuto' ,
                                'url' => false ) );
