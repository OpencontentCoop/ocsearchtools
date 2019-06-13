<?php

$module = $Params['Module'];
$id = $Params['ID'];
$http = eZHTTPTool::instance();
$tpl = eZTemplate::factory();

$error = false;
$data = array();

try
{
    $class = eZContentClass::fetchByIdentifier($id);
    if (!$class instanceof eZContentClass){
        throw new Exception("Class $id not found", 1);      
    }
    
    if ( $http->hasVariable( 'remote' ) ){
        $remoteUrl = $http->variable( 'remote' );
        $remoteRequestUrl = rtrim( $remoteUrl, '/' ) . '/classtools/extra_definition/' . $class->attribute('identifier');
        $remoteData = json_decode(eZHTTPTool::getDataByURL($remoteRequestUrl), true);

        if($remoteData == false){
        	throw new Exception("Dati remoti non trovati", 1);	
        }

        if ( $module->isCurrentAction( 'Sync' ) ){
			OCClassExtraParametersManager::instance($class)->sync($remoteData);        	
        }

        $diff = OCClassExtraParametersManager::instance($class)->compare($remoteData);
    }else{
    	throw new Exception("Remote url non trovato", 1);
    }
}
catch( Exception $e )
{
   $error = $e->getMessage(); 
}

$tpl->setVariable('remote_url', $remoteUrl);
$tpl->setVariable('class', $class);
$tpl->setVariable('diff', $diff);
$tpl->setVariable('error', $error);

$Result = array();
$Result['content'] = $tpl->fetch( 'design:classtools/extraparameters/compare.tpl' );
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
        'url' => 'classtools/classes/',
        'node_id' => null
    )
);
$Result['path'][] = array(
    'text' =>  $class->attribute( 'name' ),
    'url' => 'classtools/classes/' . $class->attribute( 'identifier' ),
    'node_id' => null
);
