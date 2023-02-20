<?php

$pagetitle = 'Quest Database';

$NEEDPUB = true;
require 'common.php';

$quests = array();

foreach ($eoserv_quests->Data() as $quest)
{
	if ($quest->id == 0)
		continue;

	//$insec = false;

	$quest = array(
		'id' => $quest->id,
		'name' => $quest->name
	);
	
	
	$quests[] = $quest;
	sort($quests);
}

$tpl->quests = $quests;
$tpl->Execute('quests');
