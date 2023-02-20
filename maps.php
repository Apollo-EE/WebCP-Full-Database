<?php

$pagetitle = 'Map Database';

$NEEDPUB = true;
require 'common.php';

$maps = array();

foreach ($eoserv_maps->Data() as $map)
{
	if ($map->id == 0)
		continue;

	//$insec = false;

	$map = array(
		'id' => $map->id,
		'name' => $map->name
	);
	
	
	$maps[] = $map;
	sort($maps);
}

$tpl->maps = $maps;
$tpl->Execute('maps');
