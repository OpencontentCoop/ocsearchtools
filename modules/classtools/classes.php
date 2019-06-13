<?php

$module = $Params['Module'];
$tpl = eZTemplate::factory();
$classIdentifier = $Params['Identifier'];
$class = null;
if ( $classIdentifier )
{
    $class = eZContentClass::fetchByIdentifier( $classIdentifier );
    if ( $class instanceof eZContentClass )
    {
        $tpl->setVariable( 'class', $class );
        
        function createGroupedDataMap( $class )
        {
            $groupedDataMap  = array();
            $contentINI      = eZINI::instance( 'content.ini' );
            $categorys       = $contentINI->variable( 'ClassAttributeSettings', 'CategoryList' );
            $defaultCategory = $contentINI->variable( 'ClassAttributeSettings', 'DefaultCategory' );
            foreach( $class->dataMap() as $classAttribute )
            {            
                $attributeCategory   = $classAttribute->attribute('category');
                $attributeIdentifier = $classAttribute->attribute( 'identifier' );
                if ( !isset( $categorys[ $attributeCategory ] ) || !$attributeCategory )
                    $attributeCategory = $defaultCategory;
    
                if ( !isset( $groupedDataMap[ $attributeCategory ] ) )
                    $groupedDataMap[ $attributeCategory ] = array();
    
                $groupedDataMap[ $attributeCategory ][$attributeIdentifier] = $classAttribute;
    
            }
            return $groupedDataMap;
        }

        
        $tpl->setVariable( 'attributes_grouped', createGroupedDataMap( $class ) );

        $extraParametersManager = OCClassExtraParametersManager::instance( $class );
        $handlers = OCClassExtraParametersManager::currentUserCanEditHandlers() ? $extraParametersManager->getHandlers() : array();
        $tpl->setVariable( 'extra_handlers', $handlers );
    }
}

$Result = array();
$Result['content'] = $tpl->fetch( 'design:classtools/classes.tpl' );
$Result['node_id'] = 0;
$contentInfoArray = array( 'url_alias' => 'classtools/classes', 'class_identifier' => null );
$contentInfoArray['persistent_variable'] = array(
    'show_path' => true
);
if (is_array($tpl->variable('persistent_variable'))) {
    $contentInfoArray['persistent_variable'] = array_merge($contentInfoArray['persistent_variable'], $tpl->variable('persistent_variable'));
}
$Result['content_info'] = $contentInfoArray;
$Result['path'] = array(
    array(
        'text' => 'Informazioni e utilità per le classi',
        'url' => 'classtools/classes',
        'node_id' => null
    )
);
if ( $class instanceof eZContentClass )
{
    $Result['path'][] = array(
        'text' => $class->attribute( 'name' ),
        'url' => false,
        'node_id' => null
    );
}