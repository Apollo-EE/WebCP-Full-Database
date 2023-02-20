<?php
$pagetitle = 'Online Characters';

$NEEDPUB = true;
require 'common.php';

$tpl->limit = $topplayers;

if ($online)
{
	if (empty($onlinelist))
	{
		$tpl->message = "No characters are currently online.";
		$tpl->Execute(null);
		exit;
	}

	foreach ($onlinelist as &$character)
	{
		if ($character['name'])
		{
				$character['name'] = ucfirst($character['name']);
				$character['title'] = empty($character['title'])?'-':ucfirst($character['title']);
				$character['gm'] = $character['admin'] == 4 || $character['admin'] == 5 || $character['admin'] == 9 || $character['admin'] == 10;
				$character['class_str'] = class_str($character['class']);
				$char = webcp_db_fetchall("SELECT COALESCE(a.lastip, a.regip) AS ip FROM characters c LEFT JOIN accounts a ON a.username = c.account WHERE c.name = ? LIMIT 1", $character['name']);
					if (isset($char[0]))
						$char = $char[0];
					else
						$char = null;
				$character['ip'] = $char['ip'];

		}
	}
	unset($character);

	$tpl->characters = $onlinelist;

	$tpl->Execute('online');
}
else
{
	$tpl->message = "Server is offline";
	$tpl->Execute(null);
	exit;
}
