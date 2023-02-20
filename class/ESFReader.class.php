<?php

class ESF_Data
{
	var $id;
	var $name;
	var $shout;

	var $icon;
	var $graphic;
	
	var $tp;
	var $sp;
	
	var $cast_time;
	var $nature;
	var $active;
	var $type;
	var $element;
	var $target_restrict;
	var $target;
	var $target_time;
	var $reserved;
	var $maxlevel;
	var $mindam;
	var $maxdam;
	var $accuracy;
	var $evade;
	var $armor;
	var $return_damage;
	var $hp;
	var $tp_recover;
	var $sp_recover;
	var $str;
	var $intl;
	var $wis;
	var $agi;
	var $con;
	var $cha;
}

class ESFReader
{
	private $data;
	const DATA_SIZE = 51;

	function __construct($filename)
	{
		$this->data = array(0 => new ESF_Data);
		$filedata = file_get_contents($filename);
		
		$rid = substr($filedata, 3, 4);
		$len = substr($filedata, 7, 2);
		$len = Number(ord($len[0]), ord($len[1]));
		
		$fi = 10;
		for ($i = 0; $i < $len; ++$i)
		{
			$newdata = new ESF_Data;
			
			$newdata->id = $i+1;
			$namelen = Number(ord(substr($filedata, $fi, 1))); $fi += 1;
			$shoutlen = Number(ord(substr($filedata, $fi, 1))); $fi += 1;
			$name = substr($filedata, $fi, $namelen); $fi += $namelen;
			$shout = substr($filedata, $fi, $shoutlen); $fi += $shoutlen;
			$newdata->name = $name;
			$newdata->shout = $shout;
			
			$newdata->icon = Number(ord(substr($filedata, $fi, 1)), ord(substr($filedata, $fi+1, 1))); $fi += 2;
			$newdata->graphic = Number(ord(substr($filedata, $fi, 1)), ord(substr($filedata, $fi+1, 1))); $fi += 2;
			$newdata->tp = Number(ord(substr($filedata, $fi, 1)), ord(substr($filedata, $fi+1, 1))); $fi += 2;
			$newdata->sp = Number(ord(substr($filedata, $fi, 1)), ord(substr($filedata, $fi+1, 1))); $fi += 2;
			$newdata->cast_time = Number(ord(substr($filedata, $fi, 1))); $fi += 1;
			$newdata->nature = Number(ord(substr($filedata, $fi, 1))); $fi += 1;
			$newdata->active = Number(ord(substr($filedata, $fi, 1))); $fi += 1;
			$newdata->type = Number(ord(substr($filedata, $fi, 1))); $fi += 3;
			$element = array('None', 'Light', 'Dark', 'Earth', 'Wind', 'Water', 'Fire');
			$element_number = Number(ord(substr($filedata, $fi, 1))); $fi += 1;
			$newdata->element = $element[$element_number];
			$newdata->element_power = Number(ord(substr($filedata, $fi, 1)), ord(substr($filedata, $fi+1, 1))); $fi += 2;
			$newdata->target_restrict = Number(ord(substr($filedata, $fi, 1))); $fi += 1;
			$newdata->target = Number(ord(substr($filedata, $fi, 1))); $fi += 1;
			$newdata->target_time = Number(ord(substr($filedata, $fi, 1))); $fi += 1;
			$newdata->reserved = Number(ord(substr($filedata, $fi, 1))); $fi += 1;
			$newdata->maxlevel = Number(ord(substr($filedata, $fi, 1)), ord(substr($filedata, $fi+1, 1))); $fi += 2;
			$newdata->mindam = Number(ord(substr($filedata, $fi, 1)), ord(substr($filedata, $fi+1, 1))); $fi += 2;
			$newdata->maxdam = Number(ord(substr($filedata, $fi, 1)), ord(substr($filedata, $fi+1, 1))); $fi += 2;
			$newdata->accuracy = Number(ord(substr($filedata, $fi, 1)), ord(substr($filedata, $fi+1, 1))); $fi += 2;
			$newdata->evade = Number(ord(substr($filedata, $fi, 1)), ord(substr($filedata, $fi+1, 1))); $fi += 2;
			$newdata->armor = Number(ord(substr($filedata, $fi, 1)), ord(substr($filedata, $fi+1, 1))); $fi += 2;
			$newdata->return_damage = Number(ord(substr($filedata, $fi, 1))); $fi += 1;
			$newdata->hp = Number(ord(substr($filedata, $fi, 1)), ord(substr($filedata, $fi+1, 1))); $fi += 2;
			$newdata->tp_recover = Number(ord(substr($filedata, $fi, 1)), ord(substr($filedata, $fi+1, 1))); $fi += 2;
			$newdata->sp_recover = Number(ord(substr($filedata, $fi, 1))); $fi += 1;
			$newdata->str = Number(ord(substr($filedata, $fi, 1)), ord(substr($filedata, $fi+1, 1))); $fi += 2;
			$newdata->intl = Number(ord(substr($filedata, $fi, 1)), ord(substr($filedata, $fi+1, 1))); $fi += 2;
			$newdata->wis = Number(ord(substr($filedata, $fi, 1)), ord(substr($filedata, $fi+1, 1))); $fi += 2;
			$newdata->agi = Number(ord(substr($filedata, $fi, 1)), ord(substr($filedata, $fi+1, 1))); $fi += 2;
			$newdata->con = Number(ord(substr($filedata, $fi, 1)), ord(substr($filedata, $fi+1, 1))); $fi += 2;
			$newdata->cha = Number(ord(substr($filedata, $fi, 1)), ord(substr($filedata, $fi+1, 1))); $fi += 2;

			array_push($this->data, $newdata);
		}
		
		if ($this->data[count($this->data) - 1]->name == "eof")
			array_pop($this->data);
	}

	function Get($id)
	{
		if (isset($this->data[$id]))
		{
			return $this->data[$id];
		}
		else
		{
			return null;
		}
	}

	function Data()
	{
		return $this->data;
	}
}
