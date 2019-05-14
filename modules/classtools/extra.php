<?php
/** @var eZModule $module */
$module = $Params['Module'];
$tpl = eZTemplate::factory();
$classIdentifier = $Params['Identifier'];
$handlerIdentifier = $Params['HandlerIdentifier'];
$http = eZHTTPTool::instance();

if ( $http->hasPostVariable( 'handler' ) && $http->hasPostVariable( 'class' ) )
{
    $module->redirectTo( 'classtools/extra/' . $http->postVariable( 'class' ) . '/' . $http->postVariable( 'handler' ) );
}

if ( $classIdentifier )
{
    $class = eZContentClass::fetchByIdentifier( $classIdentifier );
    if ( $class instanceof eZContentClass )
    {

        $extraParametersManager = OCClassExtraParametersManager::instance( $class );
        $handlers = OCClassExtraParametersManager::currentUserCanEditHandlers() ? $extraParametersManager->getHandlers() : array();
        $tpl->setVariable( 'extra_handlers', $handlers );

        if ( !$handlerIdentifier ){
            $firstHandler = array_shift( $handlers );
            $handlerIdentifier = $firstHandler->getIdentifier();
            $handlers = array_unshift( $handlers, $firstHandler );
        }


        $handler = $extraParametersManager->getHandler( $handlerIdentifier );
        if ( $handlerIdentifier && OCClassExtraParametersManager::currentUserCanEditHandlers() && $handler instanceof OCClassExtraParametersHandlerInterface)
        {
            $tpl->setVariable( 'class', $class );
            if ( $http->hasVariable( 'StoreExtraParameters' ) )
            {
                if ( $http->hasVariable( 'extra_handler_' . $handlerIdentifier ) )
                {
                    $data = $http->variable( 'extra_handler_' . $handlerIdentifier );
                    $handler->storeParameters( $data );
                }
                $module->redirectTo( 'classtools/extra/' . $classIdentifier . '/' . $handlerIdentifier );
            }

            $tpl->setVariable( 'handler', $handler );

            $Result = array();
            $Result['content'] = $tpl->fetch( 'design:classtools/extra.tpl' );
            $Result['node_id'] = 0;
            $contentInfoArray = array( 'url_alias' => 'classtools/classes', 'class_identifier' => null );
            $contentInfoArray['persistent_variable'] = array(
                'show_path' => true
            );
            if ( is_array( $tpl->variable( 'persistent_variable' ) ) ) {
                $contentInfoArray['persistent_variable'] = array_merge( $contentInfoArray['persistent_variable'], $tpl->variable( 'persistent_variable' ) );
            }
            $Result['content_info'] = $contentInfoArray;
            $Result['path'] = array(
                array(
                    'text' => 'Informazioni e utilità per le classi',
                    'url' => 'classtools/classes/',
                    'node_id' => null
                ),
                array(
                    'text' => $class->attribute( 'name' ),
                    'url' => 'classtools/classes/' . $class->attribute( 'identifier' ),
                    'node_id' => null
                ),
                array(
                    'text' => $handler->getName(),
                    'url' => false,
                    'node_id' => null
                ),
            );


        }
        else
        {
            $module->redirectTo( 'classtools/classes/' . $classIdentifier );
        }
    }
}
else
{
    $module->redirectTo( 'classtools/classes/' );
}