<?php

$pagetitle = 'Item Database';

$NEEDPUB = true;
require 'common.php';

$_GET['item'] = preg_replace("/[^0-9]/", "", $_GET['item'] );

if (!isset($_GET['item']))
{
	$tpl->message = 'No item ID specified.';
	$tpl->Execute(null);
	exit;
}

$item = $eoserv_items->Get($_GET['item']);

if (!$item)
{
	$tpl->message = 'Item ID #'. $_GET['item']. ' Does Not Exist';
	$tpl->Execute(null);
	exit;
}

$item->image = 'itemimage/gif/' . (100 + $item->graphic * 2) . '.gif';
if (!file_exists($item->image)) $item->image = null;


$item->hasreq = $item->levelreq || $item->classreq || $item->strreq || $item->intreq || $item->wisreq || $item->agireq || $item->conreq || $item->chareq;
$item->haselement = $item->element != 'None';
if ($item->classreq)
	$item->classreq_name = $eoserv_classes->Get($item->classreq)->name;
if (!$item->classreq)
	$item->classreq_name = 'Any';

$desc = '';

switch ($item->special)
{
	case 0: $desc .= "Normal"; break;
	case 1: $desc .= "Rare"; break;
	case 2: $desc .= "Legendary"; break;
	case 3: $desc .= "Unique"; break;
	case 4: $desc .= "Lore"; break;
	case 5: $desc .= "Cursed"; break;
}

$type_str = EIFReader::TypeString($item->type);

if ($type_str == "Weapon" && $item->subtype == 1)
	$desc .= " Ranged";

if ($type_str == "Armor")
{
	switch ($item->spec2)
	{
		case 0: $desc .= " Female"; break;
		case 1: $desc .= " Male"; break;
	}
}

if ($item->type == 4)
	$item->teleport = true;
$equipment = false;
$transform = false;
$skin;

switch ($type_str)
{
	case "Money": $desc .= " Currency"; break;
	case "Heal": $desc .= " Consumable"; break;
	case "Teleport": $desc .= " Teleport"; $item->mapname = $eoserv_maps->Get($item->spec1)->name; break;
	case "Transform": $item->transform = true; $item->skin = race_str($item->spec1); $desc .= " Transformation"; break;
	case "Spell": $desc .= " Spell"; break;
	case "EXPReward": case "StatReward": case "SkillReward": $desc .= " Reward Scroll"; break;
	case "Key": $desc .= " Key"; break;
	case "Weapon":
		$equipment = true;
		switch ($item->subtype)
		{
			case 1: $desc .= " Bow"; break;
			default: $desc .= " Weapon"; break;
		}
		break;
	case "Shield":
		$equipment = true;
		switch ($item->subtype)
		{
			case 2: $desc .= " Arrows"; break;
			case 3: $desc .= " Wings"; break;
			default: $desc .= " Shield"; break;
		}
		break;
	case "Armor": $equipment = true; $desc .= " Armor"; break;
	case "Hat": $equipment = true; $desc .= " Hat"; break;
	case "Boots": $equipment = true; $desc .= " Boots"; break;
	case "Gloves": $equipment = true; $desc .= " Gloves"; break;
	case "Acessory": $equipment = true; $desc .= " Accessory"; break;
	case "Belt": $equipment = true; $desc .= " Belt"; break;
	case "Necklace": $equipment = true; $desc .= " Necklace"; break;
	case "Ring": $equipment = true; $desc .= " Ring"; break;
	case "Armlet": $equipment = true; $desc .= " Armlet"; break;
	case "Bracer": $equipment = true; $desc .= " Bracer"; break;
	case "Beer": $desc .= " Beverage"; break;
	case "EffectPotion": $desc .= " Effect Potion"; break;
	case "HairDye": $desc .= " Hair Dye"; break;
	case "OtherPotion": $desc .= " Potion"; break;
	case "CureCurse": $desc .= " Uncurse Potion"; break;
	default: $desc .= " Item";
}

$founditem = false;

$item->desc = $desc;

$itemshops = array();
$itemcrafts = array();

foreach ($eoserv_shops->Data() as $shop)
{
	$buy;
	$sell;
	$foundshop = false;
	$foundcraft = false;
	
	foreach ($shop->trades as $trade)
	{
		if ($trade->id == $item->id)
		{
			$founditem = true;
			$foundshop = true;
			$buy = $trade->buy;
			$sell = $trade->sell;
		}
	}
	foreach ($shop->crafts as $craft)
	{
		if ($craft->id == $item->id)
		{
			$founditem = true;
			$foundcraft = true;
			if ($craft->ing1 != 0 && $craft->amt1 > 0)	
				$ingredients[] = array(
					'itemid' => $craft->ing1,
					'item' => $eoserv_items->Get($craft->ing1)->name,
					'amount' => $craft->amt1,
				);
			if ($craft->ing2 != 0 && $craft->amt2 > 0)	
				$ingredients[] = array(
					'itemid' => $craft->ing2,
					'item' => $eoserv_items->Get($craft->ing2)->name,
					'amount' => $craft->amt2,
				);
			if ($craft->ing3 != 0 && $craft->amt3 > 0)	
				$ingredients[] = array(
					'itemid' => $craft->ing3,
					'item' => $eoserv_items->Get($craft->ing3)->name,
					'amount' => $craft->amt3,
				);
			if ($craft->ing4 != 0 && $craft->amt4 > 0)	
				$ingredients[] = array(
					'itemid' => $craft->ing4,
					'item' => $eoserv_items->Get($craft->ing4)->name,
					'amount' => $craft->amt4,
				);
		}
	}
	foreach ($eoserv_npcs->Data() as $npc)
	{
		if ($npc->type == 6 && $npc->shopid == $shop->id)
		{
			$image = 'npcimage/gif/' . (61 + $eoserv_npcs->Get($npc->id)->graphic * 40) . '.gif';
			if (!file_exists($image)) $image = null;
			if ($foundshop)
				$itemshops[] = array(
					'name' => $shop->name,
					'npcid' => $eoserv_npcs->GetByTypeID(6, $npc->shopid)->id,
					'shopid' => $shop->id,
					'npc' => $eoserv_npcs->GetByTypeID(6, $npc->shopid)->name,
					'image' => $image,
					'buy' => $sell,
					'sell' => $buy
				);
			if ($foundcraft)
				$itemcrafts[] = array(
					'name' => $shop->name,
					'shopid' => $shop->id,
					'npcid' => $eoserv_npcs->GetByTypeID(6, $npc->shopid)->id,
					'npc' => $eoserv_npcs->GetByTypeID(6, $npc->shopid)->name,
					'image' => $image,
					'ingredients' => $ingredients
				);
		}	
	}

}

$itemdrops = array();

foreach ($eoserv_drops->Data() as $npc)
{
	foreach ($npc->drops as $drop)
	{
		if ($drop->id == $item->id)
		{
			$founditem = true;
			$rarity = "1 in " . number_format(100 / ($drop->chance / 100));
			
			$amount = $drop->minimum;
			
			if ($drop->maximum > $amount)
				$amount .= " - " . $drop->maximum;
			$image = 'npcimage/gif/' . (61 + $eoserv_npcs->Get($npc->id)->graphic * 40) . '.gif';
			if (!file_exists($image)) $image = null;
			$itemdrops[] = array(
			'npcid' => $npc->id,
			'npc' => $eoserv_npcs->Get($npc->id)->name,
			'min' => $drop->minimum,
			'max'=> $drop->maximum,
			'amount' => $amount,
			'pct' => number_format(($drop->chance * (10000 / max(10000,$npc->dropsum))) / 100, 2) ."%",
			'rarity' => $rarity,
			'image' => $image
			);
		}
	}
}
$itemquests = array();

foreach ($eoserv_npcs->Data() as $npc)
{
	if ($npc->type == 15)
	{
		$image = 'npcimage/gif/' . (61 + $eoserv_npcs->Get($npc->id)->graphic * 40) . '.gif';
		if (!file_exists($image)) $image = null;
		$quest = $eoserv_quests->Get($npc->shopid);
		if ($quest)
		{
			foreach ($quest->itemrewards as $ckitem)
			{
				if ($ckitem->id == $item->id)
				{
					$founditem = true;
					$itemquests[] = array(
					'questid' => $quest->id,
					'questname' => $quest->name,
					'questamount' => $ckitem->amount,
					'npcid' => $npc->id,
					'npcname' => $npc->name,
					'image' => $image
					);
				}
			}
		}
	}
}



$itemmaps = array();
foreach ($eoserv_maps->Data() as $map)
{
	foreach ($map->mapitems as $ckitem)
	{
		if ($ckitem && $ckitem->id == $item->id)
		{
			$founditem = true;
			$minutes = str_pad($ckitem->spawntime % 60, 2, '0', STR_PAD_LEFT);
			$hours = ($ckitem->spawntime - $minutes)/60;
			$key = $eoserv_items->GetKey($ckitem->keyid);
			$keyid = $key ? $eoserv_items->GetKey($ckitem->keyid)->id : null;
			$keyname = $key ? $eoserv_items->GetKey($ckitem->keyid)->name : '';
			$keyimage = $key ? 'itemimage/gif/' . (100 + $eoserv_items->GetKey($ckitem->keyid)->graphic * 2) . '.gif' : '';
			if (!file_exists($keyimage)) $keyimage = null;
			$break = $key ?  '<br>' : '';
			$itemmaps[] = array(
				'mapid' => $map->id,
				'name' => $map->name,
				'amount' => $ckitem->amount,
				'spawntime' => $hours.":".$minutes,
				'keyid' => $keyid,
				'keyname' => $keyname,
				'keyimage' => $keyimage,
				'br' => $break
			);			
		}
	}
}

$tpl->equipment = $equipment;
$tpl->founditem = $founditem;
$tpl->maps = (count($itemmaps) == 0) ? null : $itemmaps;
$tpl->quests = (count($itemquests) == 0) ? null : $itemquests;
$tpl->item = (array)$item;
$tpl->shops = (count($itemshops) == 0) ? null : $itemshops;
$tpl->drops = (count($itemdrops) == 0) ? null : $itemdrops;
$tpl->crafts = (count($itemcrafts) == 0) ? null : $itemcrafts;

$tpl->Execute('item');
