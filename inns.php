<?php

$pagetitle = 'Inn Database';

$NEEDPUB = true;
require 'common.php';

$inns = array();

foreach ($eoserv_inns->Data() as $inn)
{
	if ($inn->id == 0)
		continue;

	$insec = false;

	$inn = array(
		'id' => $inn->id,
		'name' => $inn->name
	);
		
	$inns[] = $inn;
}

$tpl->inns = $inns;
$tpl->Execute('inns');
