<?php
/** @var eZModule $module */
$module = $Params['Module'];
$tpl = eZTemplate::factory();
$http = eZHTTPTool::instance();

$repositoryID = $Params['RepositoryID'];
$repositoryNodeID = $Params['NodeID'];
$localParentNodeID = $Params['ParentNodeID'];

try
{
    if ( OCCrossSearch::isAvailableRepository( $repositoryID ) )
    {
        $repository = OCCrossSearch::instanceRepository( $repositoryID );
        
        if ( $http->hasPostVariable( 'SelectedNodeIDArray' ) and
             $http->postVariable( 'BrowseActionName' ) == 'FindRepositoryImportParentNode' and
             !$http->hasPostVariable( 'BrowseCancelButton' ) )
        {
            $selectedNodeIDArray = $http->postVariable( 'SelectedNodeIDArray' );
            $localParentNodeID = $selectedNodeIDArray[0];
        }
        
        if ( !$localParentNodeID )
        {
            eZContentBrowse::browse( array( 'action_name' => 'FindRepositoryImportParentNode',
                                            'from_page' => '/repository/import/' . $repositoryID . '/' . $repositoryNodeID ),
                                             $module );
            return;
        }

        $repository->handleImport( $module, $tpl, $repositoryNodeID, $localParentNodeID );
    }
    else
    {
        $module->redirectTo( 'repository/client' );
    }
    
}
catch ( Exception $e )
{
    $Result = array();
    $tpl->setVariable( 'error', $e->getMessage() );
    eZDebug::writeNotice( $e->getTraceAsString(), $e->getMessage() );
    $Result['content'] = $tpl->fetch( 'design:repository/error.tpl' );
    $Result['path'] = array( array( 'text' => 'Repository', 'url' => false ) );
}