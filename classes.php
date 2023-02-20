<?php

$pagetitle = 'Class Database';

$NEEDPUB = true;
require 'common.php';

$classes = array();

foreach ($eoserv_classes->Data() as $ecfitem)
{
	if ($ecfitem->id == 0)
		continue;

	$insec = false;

	$class = array(
		'id' => $ecfitem->id,
		'name' => $ecfitem->name,
		'base' => $ecfitem->base,
		'children' => array()
	);
	
	$data = '';
	
	$class['data'] = $data;
	
	$classes[$ecfitem->id] = $class;
}

$class_tree = array();

foreach ($classes as $k => $class)
{
	if (!$class['base'])
		$class_tree[$k] = $class;
}

foreach ($classes as $k => $class)
{
	if ($class['base'])
		$class_tree[$class['base']]['children'][] = $class;
}

$tpl->classes = $classes;
$tpl->Execute('classes');
