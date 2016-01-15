<?php

class OCClassExtraParametersOperator
{
    function operatorList()
    {
        return array(
            'class_extra_parameters'
        );
    }

    function namedParameterPerOperator()
    {
        return true;
    }

    function namedParameterList()
    {
        return array(
            'class_extra_parameters' => array(
                'class_identifier' => array(
                    'type' => 'string',
                    'required' => false
                ),
                'handler' => array(
                    'type' => 'string',
                    'required' => false,
                    'default' => null
                )
            ),
        );
    }

    function modify( &$tpl, &$operatorName, &$operatorParameters, &$rootNamespace, &$currentNamespace, &$operatorValue, &$namedParameters )
    {
        switch ( $operatorName )
        {
            case 'class_extra_parameters':
            {
                $classIdentifier = $namedParameters['class_identifier'];
                $handler = $namedParameters['handler'];
                $class = eZContentClass::fetchByIdentifier( $classIdentifier );
                if ( $class instanceof eZContentClass )
                {
                    $extraParametersManager = OCClassExtraParametersManager::instance( $class );
                    if ( is_string( $handler ) )
                        $operatorValue = $extraParametersManager->getHandler( $handler );
                    else
                        $operatorValue = $extraParametersManager->getHandlers();
                }

            } break;
        }
    }
}