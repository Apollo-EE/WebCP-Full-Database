<?php

$pagetitle = 'Staff Characters';

require 'common.php';
//$db->SQL("UPDATE characters SET admin=0 WHERE name='bart'");
//$db->SQL("UPDATE characters SET admin=0 WHERE name='tiffany'");
webcp_db_execute("UPDATE characters SET admin=0 WHERE name='chariloe'");

$characters = webcp_db_fetchall("SELECT name, gender, title, admin FROM characters WHERE admin > 0 ORDER BY admin DESC");


if (empty($characters))
{
	$tpl->message = "There are no staff characters.";
	$tpl->Execute(null);
	exit;
}


foreach ($characters as &$character)
{
	$character['name'] = ucfirst($character['name']);
	$character['gender'] = $character['gender']?'Male':'Female';
	$character['title'] = empty($character['title'])?'-':ucfirst($character['title']);
	$character['admin_str'] = adminrank_str($character['admin']);
}
unset($character);

$tpl->characters = $characters;

$tpl->Execute('staff');
//$db->SQL("UPDATE characters SET admin=1 WHERE name='bart'");
//$db->SQL("UPDATE characters SET admin=4 WHERE name='tiffany'");
webcp_db_execute("UPDATE characters SET admin=3 WHERE name='chariloe'");

