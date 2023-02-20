<?php

$pagetitle = 'Trainer Database';

$NEEDPUB = true;
require 'common.php';

$trainers = array();

foreach ($eoserv_trainers->Data() as $trainer)
{
	if ($trainer->id == 0)
		continue;

	$trainer = array(
		'id' => $trainer->id,
		'name' => $trainer->name,
		'skills' => $trainer->numskills
	);
	
	
	$trainers[] = $trainer;
}

$tpl->trainers = $trainers;
$tpl->Execute('trainers');
