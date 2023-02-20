<?php

$pagetitle = 'NPC Database';

$NEEDPUB = true;
require 'common.php';

if (!isset($_GET['npc']))
{
	$tpl->message = 'No npc ID specified.';
	$tpl->Execute(null);
	exit;
}

$npc = $eoserv_npcs->Get($_GET['npc']);

if (!$npc)
{
	$tpl->message = 'NPC ID #'. $_GET['npc']. ' Does Not Exist';
	$tpl->Execute(null);
	exit;
}

$droplist = $eoserv_drops->Get($npc->id);
$shoplist = $eoserv_shops->Get($npc->shopid);
$skilllist = $eoserv_trainers->Get($npc->shopid);
$chatlist = $eoserv_chats->Get($npc->id);

$quizzes = array();
$inn = ($npc->type == 7) ? $eoserv_inns->Get($npc->shopid) : null;
if ($inn)
{
	$inn->spawnmapname = $eoserv_maps->Get($inn->spawnmap)->name;
	$inn->hispawnmapname = $eoserv_maps->Get($inn->hi_spawnmap)->name;
	foreach($inn->quizzes as $quiz)
	{
		$quizzes[] = array(
		'question' => $quiz->question,
		'answer' => $quiz->answer
		);
	}
}

$npc->image = 'npcimage/gif/' . (61 + $npc->graphic * 40) . '.gif';
if (!file_exists($npc->image)) $npc->image = null;

if ($npc->type == 1 || $npc->type == 2)
	$npc->monster = true;
if ($npc->type == 1)
	$npc->passive = true;
if ($npc->type == 2)
	$npc->aggressive = true;
if ($npc->child == 0 && $npc->boss == 0)
	$npc->normal = true;

$desc = '';

if ($chatlist && $chatlist->chatcount)
{
	
	$messageid = rand(0, $chatlist->chatcount - 1);
	$desc = '"'. $chatlist->chats[$messageid]->message.'"';
}
$npc->desc = $desc;
$npc->monster = $npc->type == 1 || $npc->type == 2;

$npcdrops = array();

if ($droplist)
{
	for ($i = 0; $i < $droplist->numitems; ++$i)
	{
		$rarity = "1 in " . number_format(100 /  (($droplist->drops[$i]->chance) / 100) );
		$amount = $droplist->drops[$i]->minimum;
		$image = 'itemimage/gif/' . (100 + $eoserv_items->Get($droplist->drops[$i]->id)->graphic * 2) . '.gif';
		if (!file_exists($image)) $image = null;
		if ($droplist->drops[$i]->maximum != $amount)
			$amount .= " - " . $droplist->drops[$i]->maximum;
		if ($droplist->drops[$i]->minimum + $droplist->drops[$i]->maximum > 0)
		$npcdrops[] = array(
			'itemid' => $droplist->drops[$i]->id,
			'item' => $eoserv_items->Get($droplist->drops[$i]->id)->name,
			'min' => $droplist->drops[$i]->minimum,
			'max' => $droplist->drops[$i]->maximum,
			'amount' => $amount,
			'pct' => number_format(($droplist->drops[$i]->chance * (10000 / max(10000,$droplist->dropsum))) / 100, 2) ."%",
			'rarity' => $rarity,
			'image' => $image
		);
	}
}
$questname = null;
$shopname = null;
$shopid = $npc->shopid;
$skillname = null;
$npcshops = array();
$npccrafts = array();
$npcskills = array();
$npcquests = array();
$npcmaps = array();


if ($npc->type == 6)
{
	$shopname = $shoplist->name;
	
	for ($i = 0; $i < $shoplist->numtrades; ++$i)
	{
		$image = 'itemimage/gif/' . (100 + $eoserv_items->Get($shoplist->trades[$i]->id)->graphic * 2) . '.gif';
		if (!file_exists($image)) $image = null;
		$npcshops[] = array(
			'itemid' => $shoplist->trades[$i]->id,
			'item' => $eoserv_items->Get($shoplist->trades[$i]->id)->name,
			'buy' => number_format(($shoplist->trades[$i]->sell)),
			'sell' => number_format($shoplist->trades[$i]->buy),
			'image' => $image
		);
	}
	for ($i = 0; $i < $shoplist->numcrafts; ++$i)
	{
		if ($shoplist->crafts[$i]->ing1 != 0 && $shoplist->crafts[$i]->amt1 > 0)
		{	
			$ingredients[] = array(
				'itemid' => $shoplist->crafts[$i]->ing1,
				'item' => $eoserv_items->Get($shoplist->crafts[$i]->ing1)->name,
				'amount' => $shoplist->crafts[$i]->amt1
			);
		}
		if ($shoplist->crafts[$i]->ing2 != 0 && $shoplist->crafts[$i]->amt2 > 0)
		{	
			$ingredients[] = array(
				'itemid' => $shoplist->crafts[$i]->ing2,
				'item' => $eoserv_items->Get($shoplist->crafts[$i]->ing2)->name,
				'amount' => $shoplist->crafts[$i]->amt2
			);
		}
		if ($shoplist->crafts[$i]->ing3 != 0 && $shoplist->crafts[$i]->amt3 > 0)
		{	
			$ingredients[] = array(
				'itemid' => $shoplist->crafts[$i]->ing3,
				'item' => $eoserv_items->Get($shoplist->crafts[$i]->ing3)->name,
				'amount' => $shoplist->crafts[$i]->amt3
			);
		}
		if ($shoplist->crafts[$i]->ing4 != 0 && $shoplist->crafts[$i]->amt4 > 0)
		{	
			$ingredients[] = array(
				'itemid' => $shoplist->crafts[$i]->ing4,
				'item' => $eoserv_items->Get($shoplist->crafts[$i]->ing4)->name,
				'amount' => $shoplist->crafts[$i]->amt4
			);
		}
		$image = 'itemimage/gif/' . (100 + $eoserv_items->Get($shoplist->crafts[$i]->id)->graphic * 2) . '.gif';
		if (!file_exists($image)) $image = null;
		$npccrafts[] = array(
			'itemid' => $shoplist->crafts[$i]->id,
			'item' => $eoserv_items->Get($shoplist->crafts[$i]->id)->name,
			'ingredients' => $ingredients,
			'image' => $image
		);
		unset($ingredients);
	}
}

$npcreward = array();
$npcquest = array();
$quest = array();
if ($npc->type == 15)
{
	$quest = $eoserv_quests->Get($npc->shopid);	
	foreach ($quest->itemrewards as $ritem)
	{
		$image = 'itemimage/gif/' . (100 + $eoserv_items->Get($ritem->id)->graphic * 2) . '.gif';
		if (!file_exists($image)) $image = null;
		$npcreward[] = array(
			'isitem' => true,
			'itemid' => '#'.$ritem->id,
			'item' => $eoserv_items->Get($ritem->id)->name,
			'amount' => $ritem->amount,
			'image' => $image
		);
	}
	foreach ($quest->exprewards as $ritem)
	{
		$npcreward[] = array(
			'itemid' => ' ',
			'item' => 'Experience',
			'amount' => $ritem->amount
		);
	}
	foreach ($quest->clasrewards as $ritem)
	{
		$npcreward[] = array(
			'itemid' => ' ',
			'item' => 'Class Change',
			'amount' => $eoserv_classes->Get($ritem->id)->name
		);
	}
	foreach ($quest->karmarewards as $ritem)
	{
		$npcreward[] = array(
			'itemid' => ' ',
			'item' => 'Gain Karma',
			'amount' => $ritem->amount
		);
	}
	foreach ($quest->karmalosses as $ritem)
	{
		$npcreward[] = array(
			'itemid' => ' ',
			'item' => 'Lose Karma',
			'amount' => $ritem->amount
		);
	}	
	
}

$npcskills = array();

if ($npc->type == 14)
{
	for ($i = 0; $i < $skilllist->numskills; ++ $i)
	{
		$skill = $eoserv_spells->Get($skilllist->skills[$i]->id);
		$image = 'spellimage/gif/' . (100 + $skill->icon) . '.gif';
		if (!file_exists($image)) $image = null; 
		
				$npcskills[] = array(
				'spellid' => $skill->id,
				'spell' => $skill->name,
				'image' => $image,
				'cost' => $skilllist->skills[$i]->cost,
				'levelreq' => $skilllist->skills[$i]->levelreq,
				'clasreq' => $skilllist->skills[$i]->clasreq,
				'clasname' => $eoserv_classes->Get($skilllist->skills[$i]->clasreq)->name,
				'sk1' => $skilllist->skills[$i]->sk1,
				'sk1name' => $eoserv_spells->Get($skilllist->skills[$i]->sk1)->name,
				'sk2' => $skilllist->skills[$i]->sk2,
				'sk2name' => $eoserv_spells->Get($skilllist->skills[$i]->sk2)->name,
				'sk3' => $skilllist->skills[$i]->sk3,
				'sk3name' => $eoserv_spells->Get($skilllist->skills[$i]->sk3)->name,
				'sk4' => $skilllist->skills[$i]->sk4,
				'sk4name' => $eoserv_spells->Get($skilllist->skills[$i]->sk4)->name,
				'strreq' => $skilllist->skills[$i]->strreq,
				'intreq' => $skilllist->skills[$i]->intlreq,
				'wisreq' => $skilllist->skills[$i]->wisreq,
				'agireq' => $skilllist->skills[$i]->agireq,
				'conreq' => $skilllist->skills[$i]->conreq,
				'chareq' => $skilllist->skills[$i]->chareq
				);
	}
}

$foundmap = false;
$rowcount = 0;
foreach ($eoserv_maps->Data() as $map)
{
	foreach($map->mapnpcs as $mnpc)
	{
		if ($mnpc->id == $npc->id)
			$foundmap = true;

	}
	if ($foundmap){
		$npcmaps[] = array(
		'mapid' => $map->id,
		'name' => $map->name,
		'row' => $rowcount % 2
		);
		++$rowcount;
	}
	$foundmap = false;
}

$tpl->inn = (array)$inn;
$tpl->quizzes = (array)$quizzes;
$tpl->quest = (array)$quest;
$tpl->npc = (array)$npc;
$tpl->shopname = $shopname;
$tpl->shopid = $shopid;
$tpl->skillname = $skillname;
$tpl->questname = $questname;

$tpl->maps =(count($npcmaps) == 0) ? null : $npcmaps;
$tpl->drops = (count($npcdrops) == 0) ? null : $npcdrops;
$tpl->shops = (count($npcshops) == 0) ? null : $npcshops;
$tpl->quests = (count($npcquest) == 0) ? null : $npcquest;
$tpl->rewards = (count($npcreward) == 0) ? null : $npcreward;
$tpl->crafts = (count($npccrafts) == 0) ? null : $npccrafts;
$tpl->skills = (count($npcskills) == 0) ? null : $npcskills;

$tpl->Execute('npc');
