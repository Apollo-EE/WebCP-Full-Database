<?php

$pagetitle = 'Character';

$NEEDPUB = true;
require 'common.php';

if (!$logged)
{
	$tpl->message = 'You must be logged in to view this page.';
	$tpl->Execute(null);
	exit;
}

if (empty($_GET['name']))
{
	$tpl->message = 'No character name specified.';
	$tpl->Execute(null);
	exit;
}

if ($GM)
{
	$character = webcp_db_fetchall("SELECT * FROM characters WHERE name = ? LIMIT 1", strtolower($_GET['name']));
}
else
{
	$character = webcp_db_fetchall("SELECT * FROM characters WHERE name = ? AND account = ? LIMIT 1", strtolower($_GET['name']), $sess->username);
}

if (empty($character))
{
	$tpl->message = 'Character does not exist' . ($GM ? '.' : ' or is not yours.');
	$tpl->Execute(null);
	exit;
}




$rank = 1;
$character = $character[0];

$character['name'] = ucfirst($character['name']);

$character['account'] = $character['account'];
$character['gender'] = $character['gender']?'Male':'Female';
$character['title'] = empty($character['title'])?'-':ucfirst($character['title']);
$character['mapname'] = $eoserv_maps->Get($character['map'])->name;
$character['home'] = empty($character['home'])? $eoserv_inns->Get(1)->name :ucfirst($character['home']);
$home =  $eoserv_inns->GetName($character['home']);
$character['homeid'] = $home->id;
$character['spawnmap'] = ($character['level'] >= $home->hi_level && $home->hi_level != 0) ? $home->hi_spawnmap : $home->spawnmap;
$character['spawnname'] = ($character['level'] >= $home->hi_level && $home->hi_level != 0) ? $eoserv_maps->Get($home->hi_spawnmap)->name : $eoserv_maps->Get($home->spawnmap)->name;
$character['spawnx'] = ($character['level'] >= $home->hi_level && $home->hi_level != 0) ? $home->hi_spawnx : $home->spawnx;
$character['spawny'] = ($character['level'] >= $home->hi_level && $home->hi_level != 0) ? $home->hi_spawny : $home->spawny;
$character['usage_str'] = floor($character['usage']/60).' hour(s)';
$character['karma_str'] = karma_str($character['karma']);
$character['inventory'] = unserialize_inventory($character['inventory']);
$character['bank'] = unserialize_inventory($character['bank']);
$character['paperdoll'] = unserialize_paperdoll($character['paperdoll']);
$character['spells'] = unserialize_spells($character['spells']);
$character['kills'] = unserialize_kills($character['kills']);
$character['quests'] = unserialize_quests($character['quest']);
if (!empty($character['guild']))
{
	$guildinfo = webcp_db_fetchall("SELECT * FROM guilds WHERE tag = ?", $character['guild']); 
	if (!empty($guildinfo[0]))
	{
		$character['guild_name'] = ucfirst($guildinfo[0]['name']);
		$character['guild_rank_str'] = guildrank_str(unserialize_guildranks($guildinfo[0]['ranks']), $character['guild_rank']);
	}
}
$character['class_str'] = class_str($character['class']);
$character['haircolor_str'] = haircolor_str($character['haircolor']);
$character['race_str'] = race_str($character['race']);
$character['partner'] = empty($character['partner'])?'':ucfirst($character['partner']);
$expval = $character['exp'];
$character['exp'] = number_format($character['exp']);

$character['dayexpavg'] = ($character['daykills'] == 0)? '0': round($character['dayexp'] / $character['daykills']);


$counts = webcp_db_fetchall("SELECT COUNT(1) as count FROM characters WHERE exp > ? AND name != ? AND admin = 0", $expval, $character['name']);
$counts = max(1, $counts[0]['count'] + 1);
if ($character['admin']){
$counts = "-";
}
$character['rank'] = $counts;
$character['admin_str'] = adminrank_str($character['admin']);

$pagetitle .= ': '.htmlentities($character['name']);
$tpl->pagetitle = $pagetitle;

$tpl->character = $character;

$tpl->Execute('character');
