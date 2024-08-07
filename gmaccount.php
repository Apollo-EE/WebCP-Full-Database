<?php

$pagetitle = 'Account';

require 'common.php';

$_GET['name'] = substr($_GET['name'], 0, 16);

if (!$logged)
{
	$tpl->message = 'You must be logged in to view this page.';
	$tpl->Execute(null);
	exit;
}

if (!$GM)
{
	$tpl->message = 'You must be a Game Master to view this page.';
	$tpl->Execute(null);
	exit;
}

if (empty($_GET['name']))
{
	$tpl->message = 'No character name specified.';
	$tpl->Execute(null);
	exit;
}

$account = webcp_db_fetchall("SELECT * FROM accounts WHERE username = ?", strtolower($_GET['name']));
if (empty($account[0]))
{
	$tpl->message = 'Account does not exist.';
	$tpl->Execute(null);
	exit;
}
$account = $account[0];

$ip1 = $account['regip'];
$ip2 = $account['lastip'];
$account['computer_str'] = $account['computer'];
$account['hdid_str'] = sprintf("%08x", (double)$account['hdid']);
$account['hdid_str'] = strtoupper(substr($account['hdid_str'],0,4).'-'.substr($account['hdid_str'],4,4));
$account['created_str'] = date('r', $account['created']);
$account['lastused_str'] = date('r', $account['lastused']);
$account['city1'] = '';
$account['region1'] = '';
$account['city2'] = '';
$account['region2'] = '';

$lastlogin = time() - $account['lastused'];
function timesince($lastlogin)
{

	$timearray = array (
		31536000 => 'year',
		2592000 => 'month',
		604800 => 'week',
		86400 => 'day',
		3600 => 'hour',
		60 => 'minute',
		1 => 'second'
	);
	
	foreach ($timearray as $unit => $text) {
		if ($lastlogin < $unit) continue;
		$numberOfUnits = floor($lastlogin / $unit);
		return $numberOfUnits.' '.$text.(($numberOfUnits>1)?'s':'').' ago';
	}
}

$account['last_login'] = timesince($lastlogin);


$tpl->account = $account;

$characters = webcp_db_fetchall("SELECT * FROM characters WHERE account = ? ORDER BY exp DESC", strtolower($_GET['name']));

foreach ($characters as &$character)
{
	$character['name'] = ucfirst($character['name']);
	$character['gender'] = $character['gender']?'Male':'Female';
	$character['title'] = empty($character['title'])?'-':ucfirst($character['title']);
	$character['exp'] = number_format($character['exp']);
	$character['gm'] = $character['admin'] > 0;
	$character['admin_str'] = adminrank_str($character['admin']);
}
unset($character);

$tpl->characters = $characters;

$pagetitle .= ': '.htmlentities($_GET['name']);
$tpl->pagetitle = $pagetitle;

$tpl->Execute('account');
