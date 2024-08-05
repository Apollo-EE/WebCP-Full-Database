<?php

$pagetitle = 'Inn Database';

$NEEDPUB = true;
require 'common.php';

$_GET['inn'] = preg_replace("/[^0-9]/", "", $_GET['inn'] );

if (!isset($_GET['inn']))
{
	$tpl->message = 'No inn ID specified.';
	$tpl->Execute(null);
	exit;
}

$inns = array();
$quizzes = array();

$inn = $eoserv_inns->Get($_GET['inn']);
if (!$inn)
{
	$tpl->message = 'Inn ID #'. $_GET['inn']. ' Does Not Exist';
	$tpl->Execute(null);
	exit;
}
$inn->lowname = $eoserv_maps->Get($inn->spawnmap)->name;
$inn->hiname = $eoserv_maps->Get($inn->hi_spawnmap)->name;
foreach ($inn->quizzes as $quiz)
{
	$quizzes[] = array(
			'question' => $quiz->question,
			'answer' => $quiz->answer,
		);	
	
}

$npc_count = 0;

foreach ($eoserv_npcs->Data() as $npc)
{
	if ($npc->type == 7 && $npc->shopid == $inn->id)
	{
		$image = 'npcimage/gif/' . (61 + $eoserv_npcs->Get($npc->id)->graphic * 40) . '.gif';
		if (!file_exists($image)) $image = null;
		$inns[] = array(
			'npcid' => $npc->id,
			'name' => $npc->name,
			'image' => $image,
			'row' => $npc_count % 4
		);
		++$npc_count;
	}
}
$tpl->inns = (count($inns) == 0) ? null : $inns;
$tpl->quizzes = (count($quizzes) == 0) ? null : $quizzes;

$tpl->inn = (array)$inn;
$tpl->Execute('inn');
