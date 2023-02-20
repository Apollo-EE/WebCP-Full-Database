<?php

$pagetitle = 'Quest Database';

$NEEDPUB = true;
require 'common.php';

if (!isset($_GET['quest']))
{
	$tpl->message = 'No quest ID specified.';
	$tpl->Execute(null);
	exit;
}

$vendors = array();
$itemrewards = array();
$rewards = array();

$quest = $eoserv_quests->Get($_GET['quest']);

if (!$quest)
{
	$tpl->message = 'Quest ID #'. $_GET['quest']. ' Does Not Exist';
	$tpl->Execute(null);
	exit;
}

$oncedaily = ($quest->questdaily == 1);
$npc_count = 0;

foreach ($quest->questnpcs as $npc)
{
	$main = false;
	$qnpc = $eoserv_npcs->Get($npc->id);
	if ($qnpc)
	{
		if ($quest->id == $qnpc->shopid)
			$main = true;
		
		$image = 'npcimage/gif/' . (61 + $eoserv_npcs->Get($qnpc->id)->graphic * 40) . '.gif';
		if (!file_exists($image)) $image = null;
		
		if($qnpc)
		{
			$vendors[] = array(
				'main' => $main,
				'npcid' => $qnpc->id,
				'name' => $qnpc->name,
				'image' => $image,
				'row' => $npc_count % 4
			);
			++$npc_count;
		}
	}
}

foreach ($quest->itemrewards as $item)
{
	$image = 'itemimage/gif/' . (100 + $eoserv_items->Get($item->id)->graphic * 2) . '.gif';
	if (!file_exists($image)) $image = null;
	$itemrewards[] = array(
		'itemid' => $item->id,
		'name' => $eoserv_items->Get($item->id)->name,
		'amount' => $item->amount,
		'image' => $image
	);
}



foreach ($quest->exprewards as $exp)
{
	$rewards[] = array(
	'type' => "Experience",
	'given' => $exp->amount
	);
}

foreach ($quest->clasrewards as $clas)
{
	$rewards[] = array(
	'type' => "Class",
	'given' => '<a href="class.php?class='.$clas->id.'">'.$eoserv_classes->Get($clas->id)->name.'</a>'
	);
}

foreach ($quest->karmarewards as $karma)
{
	$rewards[] = array(
	'type' => "Karma",
	'given' => $karma->amount
	);
}

foreach ($quest->karmalosses as $karma)
{
	$rewards[] = array(
	'type' => "Karma",
	'given' => '-'.$karma->amount
	);
}

$tpl->showreward = ((count($itemrewards) != 0) || (count($rewards) != 0)) ? true : false;
$tpl->oncedaily = $oncedaily;
$tpl->vendors = (count($vendors) == 0) ? null : $vendors;
$tpl->itemrewards = (count($itemrewards) == 0) ? null : $itemrewards;
$tpl->rewards = (count($rewards) == 0) ? null : $rewards;
$tpl->quest = (array)$quest;

$tpl->Execute('quest');
