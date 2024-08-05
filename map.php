<?php

$pagetitle = 'Map Database';

$NEEDPUB = true;
require 'common.php';

$_GET['map'] = preg_replace("/[^a-zA-Z0-9]/", "", $_GET['map'] );
$_GET['map'] = substr($_GET['map'], 0, 24);

if (!isset($_GET['map']))
{
	$tpl->message = 'No map ID specified.';
	$tpl->Execute(null);
	exit;
}

$map = $eoserv_maps->Get($_GET['map']);

if (!$map)
{
	$tpl->message = 'Map ID #'. $_GET['map']. ' Does Not Exist';
	$tpl->Execute(null);
	exit;
}

$map->pk = $map->combat == 3;

$map->poison = $map->hazard == 1;
$map->vortex = $map->hazard == 2;
$map->quake1 = $map->hazard == 3;
$map->quake2 = $map->hazard == 4;
$map->quake3 = $map->hazard == 5;
$map->quake4 = $map->hazard == 6;
$map->respawn = ($map->rx != 0 && $map->ry != 0);

$items = array();

foreach ($map->mapitems as $item)
{
	$image = 'itemimage/gif/' . (100 + $eoserv_items->Get($item->id)->graphic * 2) . '.gif';
	if (!file_exists($image)) $image = null;
	
	$minutes = str_pad($item->spawntime % 60, 2, '0', STR_PAD_LEFT);
	$hours = ($item->spawntime - $minutes)/60;
	$mitem = $eoserv_items->Get($item->id);
	$key = $eoserv_items->GetKey($item->keyid);
	$keyid = $key ?  '#'.$eoserv_items->GetKey($item->keyid)->id : null;
	$keyname = $key ?  $eoserv_items->GetKey($item->keyid)->name : '-';
	$keyimage = $key ? 'itemimage/gif/' . (100 + $eoserv_items->GetKey($item->keyid)->graphic * 2) . '.gif' : '';
	if (!file_exists($keyimage)) $keyimage = null;
	$break = $key ? '<br>' : '';
	$items[] = array(
		'itemid' => $mitem->id,
		'name' => $eoserv_items->Get($mitem->id)->name,
		'amount' => $item->amount,
		'spawntime' => $hours.":".$minutes,
		'keyid' => $keyid,
		'keyname' => $keyname,
		'keyimage' => $keyimage,	
		'image' => $image,
		'br' => $break
	);
}

foreach ($map->mapkeys as $lkey)
{

}

$warps = array();
$itemkey;
$warplist = array();
$warpid;

foreach ($map->mapwarps as $warp)
{
	if (!in_array($warp->warpmap, $warplist) && $map->id != $warp->warpmap)
	{
		$keyname = "none";
		$keylocked = $warp->door > 1;
		$itemkey = $eoserv_items->GetKey($warp->door);
	
		$keyimage = $itemkey ? 'itemimage/gif/' . (100 + $eoserv_items->Get($itemkey->id)->graphic * 2) . '.gif' : null;
		if ($keyimage && !file_exists($keyimage)) $keyimage = null;
		$keyid = $itemkey ? $itemkey->id : null;
		$keyname = $itemkey ? $itemkey->name : null;
		$warps[] = array(
			'warpmap' => $warp->warpmap,
			'x' => $warp->xloc,
			'y' => $warp->yloc,
			'level' => $warp->levelreq,
			'keylocked' => $keylocked,
			'keyid' => $keyid,
			'keyname' => $keyname,
			'keyimage' => $keyimage,
			'warpname' => $eoserv_maps->Get($warp->warpmap)->name,
			'warpx' => $warp->warpx,
			'warpy' => $warp->warpy
		);
		$warplist[] = $warp->warpmap;
	}
}

$list = array_column($warps, 'warpmap');
array_multisort($list, SORT_ASC, $warps);

$friendlies = array();
$mobs = array();
$bosses = array();
$pets = array();
$mines = array();
$killers = array();
$shops = array();
$inns = array();
$lockers = array();
$bankers = array();
$barbers = array();
$guilds = array();
$priests = array();
$lawyers = array();
$trainers = array();
$quests = array();

$friendly_count = 0;
$boss_count =0;
$mob_count = 0;
$pet_count = 0;
$mine_count = 0;
$killer_count = 0;
$shop_count = 0;
$inn_count = 0;
$locker_count = 0;
$banker_count = 0;
$barber_count = 0;
$guild_count = 0;
$priest_count = 0;
$lawyer_count = 0;
$trainer_count = 0;
$quest_count = 0;

foreach ($map->mapnpcs as $npc)
{	
	$mnpc = $eoserv_npcs->Get($npc->id);
	$boss = $mnpc->boss || $mnpc->child; 
	$friendly = $mnpc->type == 0;
	$combat = $mnpc->type == 1 || $mnpc->type == 2;
	$pet = $mnpc->type == 3;
	$mine = $mnpc->type == 4;
	$killer = $mnpc->type == 5;
	$shop = $mnpc->type == 6;
	$inn = $mnpc->type == 7;
	$locker = $mnpc->type == 8;
	$banker = $mnpc->type == 9;
	$barber = $mnpc->type == 10;
	$guild = $mnpc->type == 11;
	$priest = $mnpc->type == 12;
	$lawyer = $mnpc->type == 13;
	$trainer = $mnpc->type == 14;
	$quest = $mnpc->type == 15;
	$image = 'npcimage/gif/' . (61 + $eoserv_npcs->Get($mnpc->id)->graphic * 40) . '.gif';
	if (!file_exists($image)) $image = null;
	if ($boss && $combat)
	{
		$bosses[] = array(
			'npcid' => $mnpc->id,
			'name' => $mnpc->name,
			'boss' => $mnpc->boss,
			'image' => $image,
			'row' => $boss_count % 4
		);
		++$boss_count;		
	}
	
	if(!$boss && $combat)
	{
		$mobs[] = array(
			'npcid' => $mnpc->id,
			'name' => $mnpc->name,
			'image' => $image,
			'row' => $mob_count % 4
		);
		++$mob_count;
	}
	if($pet)
	{
		$pets[] = array(
			'npcid' => $mnpc->id,
			'name' => $mnpc->name,
			'image' => $image,
			'row' => $pet_count % 4
		);
		++$pet_count;
	}
	if($mine)
	{
		$mines[] = array(
			'npcid' => $mnpc->id,
			'name' => $mnpc->name,
			'image' => $image,
			'row' => $mine_count % 4
		);
		++$mine_count;
	}
	if($killer)
	{
		$killers[] = array(
			'npcid' => $mnpc->id,
			'name' => $mnpc->name,
			'image' => $image,
			'row' => $killer_count % 4
		);
		++$killer_count;
	}	
	if($shop)
	{
		$shops[] = array(
			'npcid' => $mnpc->id,
			'name' => $mnpc->name,
			'image' => $image,
			'row' => $shop_count % 4
		);
		++$shop_count;
	}
	if($inn)
	{
		$inns[] = array(
			'npcid' => $mnpc->id,
			'name' => $mnpc->name,
			'image' => $image,
			'row' => $inn_count % 4
		);
		++$inn_count;
	}
	if($locker)
	{
		$lockers[] = array(
			'npcid' => $mnpc->id,
			'name' => $mnpc->name,
			'image' => $image,
			'row' => $locker_count % 4
		);
		++$locker_count;
	}
	if($banker)
	{
		$bankers[] = array(
			'npcid' => $mnpc->id,
			'name' => $mnpc->name,
			'image' => $image,
			'row' => $banker_count % 4
		);
		++$banker_count;
	}
	if($barber)
	{
		$barbers[] = array(
			'npcid' => $mnpc->id,
			'name' => $mnpc->name,
			'image' => $image,
			'row' => $barber_count % 4
		);
		++$barber_count;
	}
	if($guild)
	{
		$guilds[] = array(
			'npcid' => $mnpc->id,
			'name' => $mnpc->name,
			'image' => $image,
			'row' => $guild_count % 4
		);
		++$guild_count;
	}
	if($priest)
	{
		$priests[] = array(
			'npcid' => $mnpc->id,
			'name' => $mnpc->name,
			'image' => $image,
			'row' => $priest_count % 4
		);
		++$priest_count;
	}
	if($lawyer)
	{
		$lawyers[] = array(
			'npcid' => $mnpc->id,
			'name' => $mnpc->name,
			'image' => $image,
			'row' => $lawyer_count % 4
		);
		++$lawyer_count;
	}
	if($trainer)
	{
		$trainers[] = array(
			'npcid' => $mnpc->id,
			'name' => $mnpc->name,
			'image' => $image,
			'row' => $trainer_count % 4
		);
		++$trainer_count;
	}
	if($quest)
	{
		$quests[] = array(
			'npcid' => $mnpc->id,
			'name' => $mnpc->name,
			'image' => $image,
			'row' => $quest_count % 4
		);
		++$quest_count;
	}
	if($friendly)
	{
		$friendlies[] = array(
			'npcid' => $mnpc->id,
			'name' => $mnpc->name,
			'image' => $image,
			'row' => $friendly_count % 4
		);
		++$friendly_count;
	}	
}

$tpl->items = (count($items) == 0) ? null : $items;
$tpl->warps = (count($warps) == 0) ? null : $warps;
$tpl->friendlies = (count($friendlies) == 0) ? null : $friendlies;
$tpl->bosses = (count($bosses) == 0) ? null : $bosses;
$tpl->mobs = (count($mobs) == 0) ? null : $mobs;
$tpl->pets = (count($pets) == 0) ? null : $pets;
$tpl->mines = (count($mines) == 0) ? null : $mines;
$tpl->killers = (count($killers) == 0) ? null : $killers;
$tpl->shops = (count($shops) == 0) ? null : $shops;
$tpl->inns = (count($inns) == 0) ? null : $inns;
$tpl->lockers = (count($lockers) == 0) ? null : $lockers;
$tpl->bankers = (count($bankers) == 0) ? null : $bankers;
$tpl->barbers = (count($barbers) == 0) ? null : $barbers;
$tpl->guilds = (count($guilds) == 0) ? null : $guilds;
$tpl->priests = (count($priests) == 0) ? null : $priests;
$tpl->lawyers = (count($lawyers) == 0) ? null : $lawyers;
$tpl->trainers = (count($barbers) == 0) ? null : $trainers;
$tpl->quests = (count($quests) == 0) ? null : $quests;
$tpl->map = (array)$map;
$tpl->Execute('map');
