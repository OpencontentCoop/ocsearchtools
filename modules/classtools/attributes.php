<?php

$module = $Params['Module'];
$tpl = eZTemplate::factory();
$attributes = eZContentClassAttribute::fetchList();
$classes = eZContentClass::fetchAllClasses();
$classById = array();
foreach ($classes as $class) {
	$classById[$class->attribute('id')] = $class;
}
$tpl->setVariable('attributes', $attributes);
$tpl->setVariable('class_by_id', $classById);
$Result = array();
$Result['content'] = $tpl->fetch( 'design:classtools/attributes.tpl' );
$Result['path'] = array( array( 'text' => 'Attributi' ,
                                'url' => false ) );
