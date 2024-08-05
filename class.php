<?php

$pagetitle = 'Class Database';

$NEEDPUB = true;
require 'common.php';

$_GET['class'] = preg_replace("/[^0-9]/", "", $_GET['class'] );

if (!isset($_GET['class']))
{
	$tpl->message = 'No class ID specified.';
	$tpl->Execute(null);
	exit;
}

$class = $eoserv_classes->Get($_GET['class']);
if (!$class)
{
	$tpl->message = 'Class ID #'. $_GET['class']. ' Does Not Exist';
	$tpl->Execute(null);
	exit;
}

if ($class->base)
	$class->base_name = $eoserv_classes->Get($class->base)->name;

switch ($class->type)
{
	case 0: $class->type_name = "Melee"; break;
	case 1: $class->type_name = "Rogue"; break;
	case 2: $class->type_name = "Magical"; break;
	case 3: $class->type_name = "Archer"; break;
	case 4: $class->type_name = "Peasant"; break;
}

$foundclas = false;
$clasquests = array();

foreach ($eoserv_quests->Data() as $quest)
{
	foreach ($quest->clasrewards as $ckclas)
	{
		if ($ckclas->id == $class->id)
		{
			foreach ($eoserv_npcs->Data() as $npc)
			{
				if ($npc->type == 15 && $npc->shopid == $quest->id)
				{
					$foundclas = true;
					$clasquests[] = array(
					'questid' => $quest->id,
					'questname' => $quest->name,
					'npcid' => $npc->id,
					'npcname' => $npc->name,
					'image' => 'npcimage/gif/' . (61 + $eoserv_npcs->Get($npc->id)->graphic * 40) . '.gif'
					);
				}
			}
		}
	}
}
$tpl->foundclas = $foundclas;
$tpl->quests = (count($clasquests) == 0) ? null : $clasquests;	
$tpl->class = (array)$class;

$tpl->Execute('class');
