<?php

$pagetitle = 'Shop Database';

$NEEDPUB = true;
require 'common.php';

$_GET['shop'] = preg_replace("/[^0-9]/", "", $_GET['shop'] );

if (!isset($_GET['shop']))
{
	$tpl->message = 'No shop ID specified.';
	$tpl->Execute(null);
	exit;
}

$vendors = array();
$trades = array();
$crafts = array();

$shop = $eoserv_shops->Get($_GET['shop']);
if (!$shop)
{
	$tpl->message = 'Shop ID #'. $_GET['shop']. ' Does Not Exist';
	$tpl->Execute(null);
	exit;
}
$shop->clasreqname = $eoserv_classes->Get($shop->clasreq)->name;

foreach ($shop->trades as $trade)
{
	$image = 'itemimage/gif/' . (100 + $eoserv_items->Get($trade->id)->graphic * 2) . '.gif';
	if (!file_exists($image)) $image = null;
	$trades[] = array(
			'itemid' => $trade->id,
			'item' => $eoserv_items->Get($trade->id)->name,
			'buy' => number_format(($trade->sell)),
			'sell' => number_format($trade->buy),
			'image' => $image
		);	
	
}

foreach ($shop->crafts as $craft)
{
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
	$image = 'itemimage/gif/' . (100 + $eoserv_items->Get($craft->id)->graphic * 2) . '.gif';
	if (!file_exists($image)) $image = null;
	$crafts[] = array(
		'itemid' => $craft->id,
		'item' => $eoserv_items->Get($craft->id)->name,
		'image' => $image,
		'ingredients' => $ingredients
	);
	unset($ingredients);
}

$npc_count = 0;

foreach ($eoserv_npcs->Data() as $npc)
{
	if ($npc->type == 6 && $npc->shopid == $shop->id)
	{
		$image = 'npcimage/gif/' . (61 + $eoserv_npcs->Get($npc->id)->graphic * 40) . '.gif';
		if (!file_exists($image)) $image = null;
		$vendors[] = array(
			'npcid' => $npc->id,
			'name' => $npc->name,
			'image' => $image,
			'row' => $npc_count % 4
		);
		++$npc_count;
	}
}

$tpl->crafts = (count($crafts) == 0) ? null : $crafts;
$tpl->trades = (count($trades) == 0) ? null : $trades;
$tpl->vendors = (count($vendors) == 0) ? null : $vendors;
$tpl->shop = (array)$shop;

$tpl->Execute('shop');
