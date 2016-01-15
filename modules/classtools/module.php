<?php


$Module = array(
    'name' => 'OpenContent Class Tools',
    'variable_params' => true
);

$ViewList = array();

$ViewList['compare'] = array(
    'functions' => array( 'class' ),
    'script' => 'compare.php',
    'ui_context' => 'edit',
    'default_navigation_part' => 'ezsetupnavigationpart',
    'single_post_actions' => array(
        'SyncButton' => 'Sync',
        'InstallButton' => 'Install'
    ),
    'params' => array( 'ID' ),
    'unordered_params' => array()
);

$ViewList['compare_overview'] = array(
    'functions' => array( 'class' ),
    'script' => 'compare_overview.php',
    'ui_context' => 'edit',
    'default_navigation_part' => 'ezsetupnavigationpart',
    'params' => array(),
    'unordered_params' => array()
);

$ViewList['definition'] = array(
    'functions' => array( 'definition' ),
    'script' => 'definition.php',
    'ui_context' => 'edit',
    'default_navigation_part' => 'ezsetupnavigationpart',
    'params' => array( 'ID' ),
    'unordered_params' => array()
);

$ViewList['classes'] = array(
    'functions' => array( 'definition' ),
    'script' => 'classes.php',
    'params' => array( 'Identifier' ),
    'default_navigation_part' => 'ezsetupnavigationpart',
    'unordered_params' => array()
);

$ViewList['extra'] = array(
    'functions' => array( 'class' ),
    'script' => 'extra.php',
    'params' => array( 'Identifier', 'HandlerIdentifier' ),
    'default_navigation_part' => 'ezsetupnavigationpart',
    'unordered_params' => array()
);

$ViewList['relations'] = array(
    'functions' => array( 'definition' ),
    'script' => 'relations.php',
    'params' => array( 'ID' ),
    'default_navigation_part' => 'ezsetupnavigationpart',
    'unordered_params' => array()
);


$FunctionList['definition'] = array();
$FunctionList['class'] = array();
