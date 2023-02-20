<?php

$pagetitle = 'Shop Database';

$NEEDPUB = true;
require 'common.php';

$shops = array();

foreach ($eoserv_shops->Data() as $shop)
{
	if ($shop->id == 0)
		continue;

	$insec = false;

	$shop = array(
		'id' => $shop->id,
		'name' => $shop->name,
		'trades' => $shop->numtrades,
		'crafts' => $shop->numcrafts
	);
	
	
	$shops[] = $shop;
}

$tpl->shops = $shops;
$tpl->Execute('shops');
