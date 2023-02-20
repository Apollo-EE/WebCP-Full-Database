<?php

class EIF_Data
{
	var $id;
	var $name;
	var $graphic;
	var $type;
	var $subtype;

	var $special;
	var $hp;
	var $tp;
	var $mindam;
	var $maxdam;
	var $accuracy;
	var $evade;
	var $armor;

	var $str;
	var $intl;
	var $wis;
	var $agi;
	var $con;
	var $cha;

	var $light;
	var $dark;
	var $earth;
	var $air;
	var $fire;
	var $water;

	var $spec1;
	var $spec2;
	var $spec3;

	var $levelreq;
	var $classreq;

	var $strreq;
	var $intreq;
	var $wisreq;
	var $agireq;
	var $conreq;
	var $chareq;

	var $weight;
	var $size;
}

class EIFReader
{
	private $data;
	const DATA_SIZE = 58;

	static function TypeString($type)
	{
		$types = array(
			'General',
			'Static',
			'Currency',
			'Heal',
			'Teleport',
			'Transform',
			'EXPReward',
			'SpellBook',
			'ShowImage',
			'Key',
			'Weapon',
			'Shield',
			'Armor',
			'Hat',
			'Boots',
			'Gloves',
			'Accessory',
			'Belt',
			'Necklace',
			'Ring',
			'Armlet',
			'Bracer',
			'Beer',
			'EffectPotion',
			'HairDye',
			'CureCurse',
			'Buff',
			'Debuff',
			'Unknown1',
			'Unknown2',
			'Unknown3'
		);
		
		return isset($types[$type])?$types[$type]:'';
	}

	function __construct($filename)
	{
		$this->data = array(0 => new EIF_Data);
		$filedata = file_get_contents($filename);
		
		$rid = substr($filedata, 3, 4);
		$len = substr($filedata, 7, 2);
		$len = Number(ord($len[0]), ord($len[1]));
		
		$fi = 10;
		for ($i = 0; $i < $len; ++$i)
		{
			$newdata = new EIF_Data;
			
			$newdata->id = $i+1;
			$namelen = Number(ord(substr($filedata, $fi, 1))); $fi += 1;
			$name = substr($filedata, $fi, $namelen); $fi += $namelen;
			$newdata->name = $name;
			
			$newdata->graphic = Number(ord(substr($filedata, $fi, 1)), ord(substr($filedata, $fi+1, 1))); $fi += 2;
			$newdata->type = Number(ord(substr($filedata, $fi, 1))); $fi += 1;
			$newdata->subtype = Number(ord(substr($filedata, $fi, 1))); $fi += 1;
			$newdata->special = Number(ord(substr($filedata, $fi, 1))); $fi += 1;
			$newdata->hp = Number(ord(substr($filedata, $fi, 1)), ord(substr($filedata, $fi+1, 1))); $fi += 2;
			$newdata->tp = Number(ord(substr($filedata, $fi, 1)), ord(substr($filedata, $fi+1, 1))); $fi += 2;
			$newdata->mindam = Number(ord(substr($filedata, $fi, 1)), ord(substr($filedata, $fi+1, 1))); $fi += 2;
			$newdata->maxdam = Number(ord(substr($filedata, $fi, 1)), ord(substr($filedata, $fi+1, 1))); $fi += 2;
			$newdata->accuracy = Number(ord(substr($filedata, $fi, 1)), ord(substr($filedata, $fi+1, 1))); $fi += 2;
			$newdata->evade = Number(ord(substr($filedata, $fi, 1)), ord(substr($filedata, $fi+1, 1))); $fi += 2;
			$newdata->armor = Number(ord(substr($filedata, $fi, 1)), ord(substr($filedata, $fi+1, 1))); $fi += 2;
			$newdata->rdam = Number(ord(substr($filedata, $fi, 1))); $fi += 1;
			$newdata->str = Number(ord(substr($filedata, $fi, 1))); $fi += 1;
			$newdata->intl = Number(ord(substr($filedata, $fi, 1))); $fi += 1;
			$newdata->wis = Number(ord(substr($filedata, $fi, 1))); $fi += 1;
			$newdata->agi = Number(ord(substr($filedata, $fi, 1))); $fi += 1;
			$newdata->con = Number(ord(substr($filedata, $fi, 1))); $fi += 1;
			$newdata->cha = Number(ord(substr($filedata, $fi, 1))); $fi += 1;
			$newdata->light = Number(ord(substr($filedata, $fi, 1))); $fi += 1;
			$newdata->dark = Number(ord(substr($filedata, $fi, 1))); $fi += 1;
			$newdata->earth = Number(ord(substr($filedata, $fi, 1))); $fi += 1;
			$newdata->air = Number(ord(substr($filedata, $fi, 1))); $fi += 1;
			$newdata->water = Number(ord(substr($filedata, $fi, 1))); $fi += 1;
			$newdata->fire = Number(ord(substr($filedata, $fi, 1))); $fi += 1;
			$newdata->spec1 = Number(ord(substr($filedata, $fi, 1)), ord(substr($filedata, $fi+1, 1)), ord(substr($filedata, $fi+2, 1))); $fi += 3;
			$newdata->spec2 = Number(ord(substr($filedata, $fi, 1))); $fi += 1;
			$newdata->spec3 = Number(ord(substr($filedata, $fi, 1))); $fi += 1;
			$newdata->levelreq = Number(ord(substr($filedata, $fi, 1)), ord(substr($filedata, $fi+1, 1))); $fi += 2;
			$newdata->classreq = Number(ord(substr($filedata, $fi, 1)), ord(substr($filedata, $fi+1, 1))); $fi += 2;
			$newdata->strreq = Number(ord(substr($filedata, $fi, 1)), ord(substr($filedata, $fi+1, 1))); $fi += 2;
			$newdata->intreq = Number(ord(substr($filedata, $fi, 1)), ord(substr($filedata, $fi+1, 1))); $fi += 2;
			$newdata->wisreq = Number(ord(substr($filedata, $fi, 1)), ord(substr($filedata, $fi+1, 1))); $fi += 2;
			$newdata->agireq = Number(ord(substr($filedata, $fi, 1)), ord(substr($filedata, $fi+1, 1))); $fi += 2;
			$newdata->conreq = Number(ord(substr($filedata, $fi, 1)), ord(substr($filedata, $fi+1, 1))); $fi += 2;
			$newdata->chareq = Number(ord(substr($filedata, $fi, 1)), ord(substr($filedata, $fi+1, 1))); $fi += 2;
			$element = array('None', 'Light', 'Dark', 'Earth', 'Air', 'Water', 'Fire');
			$element_number = Number(ord(substr($filedata, $fi, 1))); $fi += 1;
			$newdata->element = $element[$element_number];
			$newdata->element_power = Number(ord(substr($filedata, $fi, 1))); $fi += 1;
			$newdata->weight = Number(ord(substr($filedata, $fi, 1))); $fi += 1;
			$newdata->att_range = Number(ord(substr($filedata, $fi, 1))); $fi += 1;
			$newdata->size = Number(ord(substr($filedata, $fi, 1))); $fi += 1;

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
	
	function GetKey($id)
	{
		$found = false;
		foreach ($this->Data() as $data)
		{
			if ($data->type == 9 && $data->spec1 == $id)
			{
				return $data;
				$found = true;
			}
		}
		if (!$found)
			return null;
	}

	function Data()
	{
		return $this->data;
	}
}
