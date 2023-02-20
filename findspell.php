<?php
ini_set('memory_limit', '1024M');
$pagetitle = 'Spell Search (beta)';

if (isset($_GET['spellid']))
{
	$checkcsrf = true;
}
$NEEDPUB = true;
require 'common.php';

if (!$GM)
{
	$tpl->message = 'You must be a Game Master to view this page.';
	$tpl->Execute(null);
	exit;
}

if (isset($_GET['spellid']))
{
	$_GET['spellid'] = (int)$_GET['spellid'];

	$allcharacters = webcp_db_fetchall("SELECT name, account, spells FROM characters");
	
	foreach ($allcharacters as &$character)
	{
		$character['spells'] = unserialize_spells($character['spells']);
	}
	unset($character);
	
	$characters = array();

	foreach ($allcharacters as $character)
	{
		$spells = $character['spells'];
		
		foreach ($spells as $spell)
		{
			if ($spell['id'] == $_GET['spellid'])
			{
				$character['found_spell'] = '<b>' . $spell['name'] . '</b>';
				$character['found'] = 1;
				break;
			}
		}
	
		
		$characters[] = $character;
	}
	
	$tpl->characters = $characters;
	
	$tpl->Execute('findspell_results');
}
else
{
	
	
	$i = 1;
	$spell = $eoserv_spells->Get($i);
	
	while ($spell->id)
	{
		$spells[] = array(
			'name' => $spell->name,
			'id' => $spell->id
		);

		$spell = $eoserv_spells->Get(++$i);
	}
	
	$tpl->spells = $spells;
	$tpl->Execute('findspell');
}




