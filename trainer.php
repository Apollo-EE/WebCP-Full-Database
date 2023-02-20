<?php

$pagetitle = 'Trainer Database';

$NEEDPUB = true;
require 'common.php';

if (!isset($_GET['trainer']))
{
	$tpl->message = 'No trainer ID specified.';
	$tpl->Execute(null);
	exit;
}

$trainers = array();
$skills = array();

$trainer = $eoserv_trainers->Get($_GET['trainer']);
if (!$trainer)
{
	$tpl->message = 'Trainer ID #'. $_GET['trainer']. ' Does Not Exist';
	$tpl->Execute(null);
	exit;
}
$trainer->clasreqname = $eoserv_classes->Get($trainer->clasreq)->name;

foreach ($trainer->skills as $skill)
{
	$refskill = $eoserv_spells->Get($skill->id);
	$image = 'spellimage/gif/' . (100 + $refskill->icon) . '.gif';
	if (!file_exists($image)) $image = null;
	$skills[] = array(
			'skillid' => $skill->id,
			'name' => $refskill->name,
			'levelreq' => $skill->levelreq,
			'clasid' => $skill->clasreq,
			'clasreq' => $eoserv_classes->Get($skill->clasreq)->name,
			'sk1id' => $skill->sk1,
			'sk1name' => $eoserv_spells->Get($skill->sk1)->name,
			'sk2id' => $skill->sk2,
			'sk2name' => $eoserv_spells->Get($skill->sk2)->name,
			'sk3id' => $skill->sk3,
			'sk3name' => $eoserv_spells->Get($skill->sk3)->name,
			'sk4id' => $skill->sk4,
			'sk4name' => $eoserv_spells->Get($skill->sk4)->name,
			'strreq' => $skill->strreq,
			'intlreq' => $skill->intlreq,
			'wisreq' => $skill->wisreq,
			'agireq' => $skill->agireq,
			'conreq' => $skill->conreq,
			'chareq' => $skill->chareq,
			'image' => $image
		);	
	
}

$npc_count = 0;

foreach ($eoserv_npcs->Data() as $npc)
{
	if ($npc->type == 14 && $npc->shopid == $trainer->id)
	{
		$image = 'npcimage/gif/' . (61 + $eoserv_npcs->Get($npc->id)->graphic * 40) . '.gif';
		if (!file_exists($image)) $image = null;
		$trainers[] = array(
			'npcid' => $npc->id,
			'name' => $npc->name,
			'image' => $image,
			'row' => $npc_count % 4
		);
		++$npc_count;
	}
}

$tpl->skills = (count($skills) == 0) ? null : $skills;

$tpl->trainers = (count($trainers) == 0) ? null : $trainers;
$tpl->skill = (array)$skill;
$tpl->trainer = (array)$trainer;
$tpl->Execute('trainer');
