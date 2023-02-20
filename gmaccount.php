<?php

$pagetitle = 'Account';

require 'common.php';
require ".htsln/geoip.inc";
require ".htsln/geoipcity.inc";
require ".htsln/geoipregionvars.php";
$geoip_filename = '.htsln/GeoIP.dat';
$geoipcity_filename = '.htsln/GeoLiteCity.dat';
$geoip = geoip_open($geoip_filename, GEOIP_STANDARD);
$geocity = geoip_open($geoipcity_filename, GEOIP_STANDARD);
function getcc($ip)
{
	global $geoip;
	return geoip_country_code_by_addr($geoip, $ip);
}

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
$location1 = GeoIP_record_by_addr($geocity, $ip1);
$location2 = GeoIP_record_by_addr($geocity, $ip2);

$account['hdid_str'] = sprintf("%08x", (double)$account['hdid']);
$account['hdid_str'] = strtoupper(substr($account['hdid_str'],0,4).'-'.substr($account['hdid_str'],4,4));
$account['created_str'] = date('r', $account['created']);
$account['lastused_str'] = date('r', $account['lastused']);
$account['code1'] = geoip_country_code_by_addr($geoip, $ip1);
$account['code2'] = geoip_country_code_by_addr($geoip, $ip2);
$account['country1'] = geoip_country_name_by_addr($geoip, $ip1);
$account['country2'] = geoip_country_name_by_addr($geoip, $ip2);
$account['city1'] = '';
$account['region1'] = '';
$account['city2'] = '';
$account['region2'] = '';
if ($location1)
{
	if(($account['code1'] == 'US') or ($account['code1'] == 'CA'))
	{
		$account['region1'] = $location1->region.' ';
	}
	if ($account['region1'] == ' ')
	{
		$account['region1'] = '';
	}
	if ($location1->city)
	{
		$account['city1'] = $location1->city.', ';
	}
	if ($account['city1'] == ', ')
	{
		$account['city1'] = '';
	}
}

if ($location2)
{
	if(($account['code2'] == 'US') or ($account['code2'] == 'CA'))
	{
		$account['region2'] = $location2->region.' ';
	}
	if ($account['region2'] == ' ')
	{
		$account['region2'] = '';
	}
	if ($location2->city)
	{
		$account['city2'] = $location2->city.', ';
	}
	if ($account['city2'] == ', ')
	{
		$account['city2'] = '';
	}
}
if (($ip1 == '127.0.0.1') or ((long2ip(ip2long($ip1) & 0xFFFF0000)) == '192.168.0.0'))
{
	$account['code1'] = 'edge';
	$account['country1'] = 'Endless Edge';
	$account['region1'] = '';
	$account['city1'] = '';
}
if (($ip2 == '127.0.0.1') or ((long2ip(ip2long($ip2) & 0xFFFF0000)) == '192.168.0.0'))
{
	$account['code2'] = 'edge';
	$account['country2'] = 'Endless Edge';
	$account['region2'] = '';
	$account['city2'] = '';	
}

if (($account['code1'] == 'A1') or ($account['code1'] == 'A2'))
{
	$account['code1'] = 'unknown';
	$account['region1'] = '';
	$account['city1'] = '';	
}

if (!$account['code1']) 
{
	$account['code1'] = 'unknown'; 
	$account['region1'] = '';
	$account['city1'] = '';
}

if (($account['code2'] == 'A1') or ($account['code2'] == 'A2'))
{
	$account['code2'] = 'unknown';
	$account['region2'] = '';
	$account['city2'] = '';		
}

if (!$account['code2']) 
{
	$account['code2'] = 'unknown'; 
	$account['region2'] = '';
	$account['city2'] = '';		
}
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
