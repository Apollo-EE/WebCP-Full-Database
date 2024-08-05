<?php
ini_set('memory_limit', '1024M');
$pagetitle = 'Item Search';

if (isset($_GET['itemid']))
{
	$checkcsrf = true;
}

$_GET['itemid'] = preg_replace("/[^0-9]/", "", $_GET['itemid'] );

$NEEDPUB = true;
require 'common.php';

if (!$GM)
{
	$tpl->message = 'You must be a Game Master to view this page.';
	$tpl->Execute(null);
	exit;
}

if (isset($_GET['itemid']) && isset($_GET['imin']) && isset($_GET['imax']))
{
	$_GET['itemid'] = (int)$_GET['itemid'];

	$allcharacters = webcp_db_fetchall("SELECT name, account, goldbank, inventory, bank, paperdoll FROM characters");
	
	foreach ($allcharacters as &$character)
	{
		$character['inventory'] = unserialize_inventory($character['inventory']);
		$character['bank'] = unserialize_inventory($character['bank']);
		$character['paperdoll'] = unserialize_paperdoll($character['paperdoll']);
	}
	unset($character);
	
	$characters = array();

	foreach ($allcharacters as $character)
	{
		$items = $character['inventory'];
		$bank = $character['bank'];
		$paperdoll = $character['paperdoll'];
		
		foreach ($items as $item)
		{
			if ($item['id'] == $_GET['itemid'] && $item['amount'] >= $_GET['imin']
			 && ($item['amount'] <= $_GET['imax'] || $_GET['imax'] == 0))
			{
				$character['found_inventory'] = '<b>' . $item['amount'] . 'x</b>';
				$character['found'] = 1;
				break;
			}
		}
		
		if ($_GET['itemid'] == 1)
		{
			if ($character['goldbank'] >= $_GET['imin']
			 && ($character['goldbank'] || $_GET['imax'] == 0))
			{
				$character['found_bank'] = '<b>' . $character['goldbank'] . 'x</b>';
				$character['found'] = 1;
			}
		}
		else
		{
			foreach ($bank as $item)
			{
				if ($item['id'] == $_GET['itemid'] && $item['amount'] >= $_GET['imin']
				 && ($item['amount'] <= $_GET['imax'] || $_GET['imax'] == 0))
				{
					$character['found_bank'] = '<b>' . $item['amount'] . 'x</b>';
					$character['found'] = 1;
					break;
				}
			}
		}
		
		foreach ($paperdoll as $item)
		{
			if ($item['id'] == $_GET['itemid'] && 1 >= $_GET['imin']
			 && (1 <= $_GET['imax'] || $_GET['imax'] == 0))
			{
				$character['found_paperdoll'] = '<b>' . 1 . 'x</b>';
				$character['found'] = 1;
				break;
			}
		}
		
		$characters[] = $character;
	}
	
	$tpl->characters = $characters;
	
	$tpl->Execute('finditem_results');
}
else
{
	
	
	$i = 1;
	$item = $eoserv_items->Get($i);
	
	while ($item->id)
	{
		$items[] = array(
			'name' => $item->name,
			'id' => $item->id
		);

		$item = $eoserv_items->Get(++$i);
	}
	
	$tpl->items = $items;
	$tpl->Execute('finditem');
}




