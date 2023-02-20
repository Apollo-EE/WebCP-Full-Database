<?php

$pagetitle = 'NPC Database';

$NEEDPUB = true;
require 'common.php';

$sections = array(
	'Monsters' => array(
		'types' => array(1, 2)
	),
	'Shopkeepers' => array(
		'types' => array(6)
	),
	'Innkeepers' => array(
		'types' => array(7)
	),
	'Bankers' => array(
		'types' => array(9)
	),
	'Barbers' => array(
		'types' => array(10)
	),
	'Guild Masters' => array(
		'types' => array(11)
	),
	'Priests' => array(
		'types' => array(12)
	),
	'Lawyers' => array(
		'types' => array(13)
	),
	'Skill Masters' => array(
		'types' => array(14)
	),
	'Quest NPCs' => array(
		'types' => array(15)
	),
	'Other NPCs' => array(
		'types' => array(0, 3, 4, 5, 8)
	),
);

if (isset($_GET['section']))
{
	$npcs = array();
	
	if (!isset($sections[$_GET['section']]))
	{
		$tpl->message = "Invalid section";
		$tpl->Execute(null);
		exit;
	}
	
	$section = $sections[$_GET['section']];
	
	foreach ($eoserv_npcs->Data() as $enfitem)
	{
		if ($enfitem->id == 0 || $enfitem->name == "New Npc")
			continue;

		$insec = false;

		foreach ($section['types'] as $type)
		{
			if ($type == $enfitem->type)
			{
				$insec = true;
				break;
			}
		}
		
		if (!$insec)
			continue;
		$image = 'npcimage/gif/' . (61 + $enfitem->graphic * 40) . '.gif';
		if (!file_exists($image)) $image = null;
		$npc = array(
			'id' => $enfitem->id,
			'name' => $enfitem->name,
			'image' => $image
		);
		
		$data = '';
		
		switch ($_GET['section'])
		{
			case 'Monsters':
				$data = "<b>Level: {$enfitem->level}</b><br>";
				$data .= "<b>HP: {$enfitem->hp}</b><br>";
				$data .= "<b>TP: {$enfitem->tp}</b><br>";
				$data .= "<b>EXP: {$enfitem->exp}</b><br>";
				//$data .= "<b>Damage:</b> {$enfitem->mindam} - {$enfitem->maxdam}<br>";
				//$data .= "<b>Accuracy:</b> {$enfitem->accuracy}<br>";
				//$data .= "<b>Evade:</b> {$enfitem->evade}<br>";
				//$data .= "<b>Armor:</b> {$enfitem->armor}<br>";
				break;
		}
		
		$npc['data'] = $data;
		
		$npcs[] = $npc;
	}
	
	usort($npcs, function($a, $b)
	{
		return $b['name'] < $a['name'];
	});

	$perpage = $imgperpage;
	$count = count($npcs);

	$page = isset($_GET['page']) ? $_GET['page'] : 1;
	$pages = ceil($count / $perpage);

	if ($page < 1 || $page > $pages)
	{
		$page = max(min($page, $pages), 1);
	}

	$start = ($page-1) * $perpage;
	
	$pagination = generate_pagination($pages, $page, '?section=' . $_GET['section']);
	
	$npcs = array_slice($npcs, $start, $perpage);

	$tpl->monsters = ($_GET['section'] == 'Monsters') ? true : false;
	$tpl->page = $page;
	$tpl->pages = $pages;
	$tpl->pagination = $pagination;
	$tpl->perpage = $perpage;
	$tpl->showing = count($npcs);
	$tpl->start = $start+1;
	$tpl->end = min($start+$perpage, $count);
	$tpl->count = $count;

	$tpl->section = $_GET['section'];
	$tpl->npcs = $npcs;
	$tpl->Execute('npcs');
}
else
{
	$tpl->sections = array_keys($sections);
	$tpl->Execute('npcs_index');
}
