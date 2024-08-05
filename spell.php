<?php

$pagetitle = 'Spell Database';

$NEEDPUB = true;
require 'common.php';

$_GET['spell'] = preg_replace("/[^0-9]/", "", $_GET['spell'] );

if (!isset($_GET['spell']))
{
	$tpl->message = 'No spell ID specified.';
	$tpl->Execute(null);
	exit;
}

$spell = $eoserv_spells->Get($_GET['spell']);
if (!$spell)
{
	$tpl->message = 'Skill ID #'. $_GET['spell']. ' Does Not Exist';
	$tpl->Execute(null);
	exit;
}

$spell->image = 'spellimage/gif/' . (100 + $spell->icon) . '.gif';
if (!file_exists($spell->image)) $spell->image = null;


$desc = '"' . $spell->shout . '"';

$spell->desc = $desc;

$spell->damage = $spell->type == 1;
$spell->heal = $spell->type == 0;


$skillmasters = array();

$found = false;
foreach ($eoserv_trainers->Data() as $trainer)
{
	$foundskill = false;
	foreach ($trainer->skills as $skill)
	{
		if ($skill->id == $spell->id)
		{
			$foundskill = true;
			$cost = $skill->cost;
			$levelreq = $skill->levelreq;
			$clasreq = $skill->clasreq;
			$sk1 = $skill->sk1;
			$sk2 = $skill->sk2;
			$sk3 = $skill->sk3;
			$sk4 = $skill->sk4;
			$strreq = $skill->strreq;
			$intlreq = $skill->intlreq;
			$wisreq = $skill->wisreq;
			$agireq = $skill->agireq;
			$conreq = $skill->conreq;
			$chareq = $skill->chareq;
			$found = true;
		}
	}
	if ($foundskill)
	{
		foreach ($eoserv_npcs->Data() as $npc)
		{
			if ($npc->type == 14 && $npc->shopid == $trainer->id)	
			{
						$image = 'npcimage/gif/' . (61 + $npc->graphic * 40) . '.gif';
						if (!file_exists($image)) $image = null;
						$skillmasters[] = array(
						'npcid' => $npc->id,
						'npc' => $npc->name,
						'cost' => $cost,
						'levelreq' => $levelreq,
						'clasreq' => $clasreq,
						'clasreqname' => $eoserv_classes->Get($clasreq)->name,
						'sk1' => $sk1,
						'sk1name' => $eoserv_spells->Get($sk1)->name,
						'sk2' => $sk2,
						'sk2name' => $eoserv_spells->Get($sk2)->name,
						'sk3' => $sk3,
						'sk3name' => $eoserv_spells->Get($sk3)->name,
						'sk4' => $sk4,
						'sk4name' => $eoserv_spells->Get($sk4)->name,
						'strreq' => $strreq,
						'intreq' => $intlreq,
						'wisreq' => $wisreq,
						'agireq' => $agireq,
						'conreq' => $conreq,
						'chareq' => $chareq,
						'image' => $image
					);
			}
		}
	}
}

$skillitems = array();

foreach ($eoserv_items->Data() as $item)
{
	if ($item->type == 7 && $item->spec1 == $spell->id)
	{
		$image = 'itemimage/gif/' . (100 + $item->graphic * 2) . '.gif';
		if (!file_exists($image)) $image = null;
		$found = true;
		$skillitems[] = array(
			'itemid' => $item->id,
			'name' => $item->name,
			'graphic' => $image
		);
	}
}

$tpl->found = $found;
$tpl->spell = (array)$spell;
$tpl->masters = (count($skillmasters) == 0) ? null : $skillmasters;
$tpl->items = (count($skillitems) == 0) ? null : $skillitems;

$tpl->Execute('spell');
