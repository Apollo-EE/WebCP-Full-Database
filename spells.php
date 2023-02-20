<?php

$pagetitle = 'Spell Database';

$NEEDPUB = true;
require 'common.php';

$sections = array(
	'Heal Spells' => array(
		'types' => array(0)
	),
	'Damage Spells' => array(
		'types' => array(1)
	),
	'Other Spells' => array(
		'types' => array(2)
	),
);

if (isset($_GET['section']))
{
	$spells = array();
	
	if (!isset($sections[$_GET['section']]))
	{
		$tpl->message = "Invalid section";
		$tpl->Execute(null);
		exit;
	}
	
	$section = $sections[$_GET['section']];
	
	foreach ($eoserv_spells->Data() as $esfitem)
	{
		if ($esfitem->id == 0)
			continue;

		$insec = false;

		foreach ($section['types'] as $type)
		{
			if ($type == $esfitem->type)
			{
				$insec = true;
				break;
			}
		}
		
		if (!$insec)
			continue;

		$image = 'spellimage/gif/' . (100 + $esfitem->icon) . '.gif';
		if (!file_exists($image)) $image = null;
		$spell = array(
			'id' => $esfitem->id,
			'name' => $esfitem->name,
			'image' => $image

		);
		
		$data = '';
		
		switch ($_GET['section'])
		{
			case 'Damage Spells':
				$data .= "<b>TP:</b> {$esfitem->tp}<br>";
				$data .= "<b>SP:</b> {$esfitem->sp}<br>";
				$data .= "<b>Damage:</b> {$esfitem->mindam} - {$esfitem->maxdam}";
				break;

			case 'Heal Spells':
				$data .= "<b>TP:</b> {$esfitem->tp}<br>";
				$data .= "<b>SP:</b> {$esfitem->sp}<br>";
				$data .= "<b>HP:</b> +{$esfitem->hp}";

				break;
		}
		
		$spell['data'] = $data;
		
		$spells[] = $spell;
	}
	
	usort($spells, function($a, $b)
	{
		return $b['name'] < $a['name'];
	});

	$perpage = $imgperpage;
	$count = count($spells);

	$page = isset($_GET['page']) ? $_GET['page'] : 1;
	$pages = ceil($count / $perpage);

	if ($page < 1 || $page > $pages)
	{
		$page = max(min($page, $pages), 1);
	}

	$start = ($page-1) * $perpage;
	
	$pagination = generate_pagination($pages, $page, '?section=' . $_GET['section']);
	
	$spells = array_slice($spells, $start, $perpage);

	$tpl->page = $page;
	$tpl->pages = $pages;
	$tpl->pagination = $pagination;
	$tpl->perpage = $perpage;
	$tpl->showing = count($spells);
	$tpl->start = $start+1;
	$tpl->end = min($start+$perpage, $count);
	$tpl->count = $count;

	$tpl->section = $_GET['section'];
	$tpl->spells = $spells;
	$tpl->Execute('spells');
}
else
{
	$tpl->sections = array_keys($sections);
	$tpl->Execute('spells_index');
}
