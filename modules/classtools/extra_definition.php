<?php

$module = $Params['Module'];
$id = $Params['ID'];

try
{
    $class = eZContentClass::fetchByIdentifier($id);
    if (!$class instanceof eZContentClass){
        throw new Exception("Class $id not found", 1);      
    }
    
    $result = OCClassExtraParametersManager::instance($class)->getAllParameters();    
}
catch( Exception $e )
{
   $result = array( 'error' => $e->getMessage() ); 
}

header('Content-Type: application/json');
echo json_encode( $result );    
eZExecution::cleanExit();
