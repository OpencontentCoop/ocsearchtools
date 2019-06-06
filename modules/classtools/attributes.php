<?php

$module = $Params['Module'];
$tpl = eZTemplate::factory();
$attributes = eZPersistentObject::fetchObjectList( eZContentClassAttribute::definition());
$classes = eZPersistentObject::fetchObjectList( eZContentClass::definition());
$classById = array();
foreach ($classes as $class) {
	$classById[$class->attribute('id')] = $class;
}
$tpl->setVariable('attributes', $attributes);
$tpl->setVariable('class_by_id', $classById);
$Result = array();
$Result['content'] = $tpl->fetch( 'design:classtools/attributes.tpl' );
$Result['path'] = array(
    array(
        'text' => 'Informazioni e utilità per le classi',
        'url' => 'classtools/classes/',
        'node_id' => null
    )
);