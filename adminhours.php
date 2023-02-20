<?php

$pagetitle = 'Admin Usage';

require 'common.php';

if (!$HGM)
{
	$tpl->message = 'You must be a High Game Master to view this page.';
	$tpl->Execute(null);
	exit;
}
if (!$logged)
{
	$tpl->message = 'You must be logged in to view this page.';
	$tpl->Execute(null);
	exit;
}

$characters = webcp_db_fetchall("SELECT * FROM characters WHERE admin > 0 ORDER BY admin DESC");


if (empty($characters))
{
	$tpl->message = "There are no staff characters.";
	$tpl->Execute(null);
	exit;
}

foreach ($characters as &$character)
{

	$character['name'] = ucfirst($character['name']);
	$character['admin_str'] = adminrank_str($character['admin']);
	$character['usage_str'] = floor($character['usage']/60).' hour(s)';
}
unset($character);

$tpl->characters = $characters;

$tpl->Execute('adminhours');
