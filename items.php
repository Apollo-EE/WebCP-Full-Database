<?php

$pagetitle = 'Item Database';

$NEEDPUB = true;
require 'common.php';

$sections = array(
	'Weapons' => array(
		'types' => array(10)
	),
	'Hats' => array(
		'types' => array(13)
	),
	'Armor (Male)' => array(
		'types' => array(12),
		'gender' => 1
	),
	'Armor (Female)' => array(
		'types' => array(12),
		'gender' => 0
	),
	'Shields/Back' => array(
		'types' => array(11)
	),
	'Boots' => array(
		'types' => array(14)
	),
	'Accessories' => array(
		'types' => array(15, 16, 17, 18, 19, 20, 21)
	),
	'Teleport Scrolls' => array(
		'types' => array(4)
	),
	'Useables' => array(
		'types' => array(3, 5, 6, 7, 8, 22, 23, 24, 25, 26)
	),
	'Other' => array(
		'types' => array(0, 1, 2, 9, 27, 28, 29, 30)
	),
);

if (isset($_GET['section']))
{
	$items = array();
	
	if (!isset($sections[$_GET['section']]))
	{
		$tpl->message = "Invalid section";
		$tpl->Execute(null);
		exit;
	}
	
	$section = $sections[$_GET['section']];
	
	foreach ($eoserv_items->Data() as $eifitem)
	{
		if ($eifitem->id == 0)
			continue;

		$insec = false;

		foreach ($section['types'] as $type)
		{
			if ($type == $eifitem->type)
			{
				if (isset($section['gender']) && $section['gender'] != $eifitem->spec2)
					continue;

				$insec = true;
				break;
			}
		}
		
		if (!$insec)
			continue;
		$image = 'itemimage/gif/' . (100 + $eifitem->graphic * 2) . '.gif';
		if (!file_exists($image)) $image = null;
		$item = array(
			'id' => $eifitem->id,
			'name' => $eifitem->name,
			'image' => $image
		);
		
		$data = '';
		
		switch ($_GET['section'])
		{
			case 'Weapons':
				$data = "<b>Damage:</b> {$eifitem->mindam} - {$eifitem->maxdam}<br>";
				$data .= "<b>Accuracy:</b> {$eifitem->accuracy}<br>";
				if ($eifitem->armor) $data = "<b>Defence:</b> {$eifitem->armor}<br>";
				break;
			
			case 'Hats':
			case 'Armor (Male)':
			case 'Armor (Female)':
			case 'Shields/Back':
			case 'Accessories':
				if ($eifitem->armor) $data = "<b>Defence:</b> {$eifitem->armor}<br>";
				if ($eifitem->mindam || $eifitem->maxdam) $data .= "<b>Damage:</b> {$eifitem->mindam} - {$eifitem->maxdam}<br>";
				if ($eifitem->accuracy) $data .= "<b>Accuracy:</b> {$eifitem->accuracy}<br>";
		}
		$eifitem->teleport = false;
		if ($eifitem->evade) $data .= "<b>Evade:</b> {$eifitem->evade}<br>";
		if ($eifitem->hp) $data .= "<b>HP:</b> +{$eifitem->hp}<br>";
		if ($eifitem->tp) $data .= "<b>TP:</b> +{$eifitem->tp}<br>";
		if ($eifitem->str) $data .= "<b>STR:</b> +{$eifitem->str}<br>";
		if ($eifitem->intl) $data .= "<b>INT:</b> +{$eifitem->intl}<br>";
		if ($eifitem->wis) $data .= "<b>WIS:</b> +{$eifitem->wis}<br>";
		if ($eifitem->agi) $data .= "<b>AGI:</b> +{$eifitem->agi}<br>";
		if ($eifitem->con) $data .= "<b>CON:</b> +{$eifitem->con}<br>";
		if ($eifitem->cha) $data .= "<b>CHA:</b> +{$eifitem->cha}<br>";
		
		if (EIFReader::TypeString($eifitem->type) == 'EffectPotion')
			$data .= "<b>Effect:</b> #{$eifitem->spec1}";
		
		if (EIFReader::TypeString($eifitem->type) == 'Transform')
		$data .= "<b>Skin:</b> " . race_str($eifitem->spec1);
	
		if (EIFReader::TypeString($eifitem->type) == 'SpellBook')
		$data .= "<b>Spell Learned:</b> " . $eoserv_spells->Get($eifitem->spec1)->name;

		if (EIFReader::TypeString($eifitem->type) == 'HairDye')
			$data .= "<b>Hair dye:</b> " . haircolor_str($eifitem->spec1);
		
		if (EIFReader::TypeString($eifitem->type) == 'EXPReward')
			$data .= "<b>EXP Reward:</b> ({$eifitem->spec1})";
		if (EIFReader::TypeString($eifitem->type) == 'Teleport')
		{
			$mapname=$eoserv_maps->Get($eifitem->spec1)->name;
			$eifitem->teleport = true;
			if ($eifitem->spec1 == 0)
				$data .= '<b>Teleport:</b> Home Spawn Location';
			else
			{
				$data .= "<b>Teleport: Map </b><a href='map.php?map=$eifitem->spec1'> #$eifitem->spec1 $mapname </a>";
			}
		}
		if (EIFReader::TypeString($eifitem->type) == 'Beer')
			$data .= "<b>Drunk:</b> {$eifitem->spec1} sec";
		
		if (EIFReader::TypeString($eifitem->type) == 'OtherPotion')
			$data .= "Cure Curse";
		
		if ($_GET['section'] == 'Other')
			$data = '';
		
		$item['data'] = $data;
		
		$items[] = $item;
	}
	
	usort($items, function($a, $b)
	{
		return $b['name'] < $a['name'];
	});

	$perpage = $imgperpage;
	$count = count($items);

	$page = isset($_GET['page']) ? $_GET['page'] : 1;
	$pages = ceil($count / $perpage);

	if ($page < 1 || $page > $pages)
	{
		$page = max(min($page, $pages), 1);
	}

	$start = ($page-1) * $perpage;
	
	$pagination = generate_pagination($pages, $page, '?section=' . $_GET['section']);
	
	$items = array_slice($items, $start, $perpage);

	$tpl->page = $page;
	$tpl->pages = $pages;
	$tpl->pagination = $pagination;
	$tpl->perpage = $perpage;
	$tpl->showing = count($items);
	$tpl->start = $start+1;
	$tpl->end = min($start+$perpage, $count);
	$tpl->count = $count;

	$tpl->section = $_GET['section'];
	$tpl->items = $items;
	$tpl->Execute('items');
}
else
{
	$tpl->sections = array_keys($sections);
	$tpl->Execute('items_index');
}
