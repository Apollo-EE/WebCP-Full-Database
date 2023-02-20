<?php

$pagetitle = 'Top Players';

require 'common.php';

$tpl->limit = $topplayers;

$characters = webcp_db_fetchall("SELECT COALESCE(a.lastip, a.regip) AS ip, c.name AS name, c.account AS account, c.title AS title, c.level AS level, c.exp AS exp, c.gender AS gender FROM characters c LEFT JOIN accounts a ON a.username = c.account WHERE c.admin = 0 ORDER BY c.exp DESC LIMIT ?", $topplayers);
foreach ($characters as &$character)
{
	$character['name'] = ucfirst($character['name']);
	$character['gender'] = $character['gender']?'Male':'Female';
	$character['title'] = empty($character['title'])?'-':ucfirst($character['title']);
	$character['exp'] = number_format($character['exp']);

}
unset($character);

$tpl->characters = $characters;

$tpl->Execute('players');
