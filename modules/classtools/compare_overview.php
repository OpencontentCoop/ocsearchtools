<?php

$module = $Params['Module'];
$tpl = eZTemplate::factory();
$http = eZHTTPTool::instance();

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
        $remoteRequestSuffix = 'remote=' . $remoteRequest;
}

$tpl->setVariable('remote', $remoteRequest);
$tpl->setVariable('remote_query_string', $remoteRequestSuffix);

$Result = array();
$Result['content'] = $tpl->fetch( 'design:classtools/list.tpl' );
$Result['path'] = array(
    array(
        'text' => 'Informazioni e utilità per le classi',
        'url' => 'classtools/classes/',
        'node_id' => null
    )
);