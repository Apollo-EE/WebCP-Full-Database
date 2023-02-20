<?php

class skill
{
	var $id;
	var $levelreq;
	var $clasreq;
	var $cost;
	var $sk1;
	var $sk2;
	var $sk3;
	var $sk4;
	var $strreq;
	var $intlreq;
	var $wisreq;
	var $agireq;
	var $conreq;
	var $chareq;
}

class Trainer_Data
{
	var $id;
	var $name;
	var $minlevel;
	var $maxlevel;
	var $clasreq;
	var $numskills;
	public $skills = array();
}

class TrainerReader
{
	private $data;
	const DATA_SIZE = 7;
	const SKILL_SIZE = 28;

	function __construct($filename)
	{
		$this->data = array(0 => new Trainer_Data);
		$filedata = file_get_contents($filename);
		
		$rid = substr($filedata, 3, 4);
		$len = substr($filedata, 7, 2);
		$len = Number(ord($len[0]), ord($len[1]));
		
		$fi = 10;
		for ($i = 1; $i <= $len; ++$i)
		{
			$newdata = new Trainer_Data;
			
			$newdata->id = Number(ord(substr($filedata, $fi, 1)), ord(substr($filedata, $fi+1, 1))); $fi += 2;

			$namelen = Number(ord(substr($filedata, $fi, 1))); $fi += 1;
			$name = substr($filedata, $fi, $namelen); $fi += $namelen;	
			$newdata->name = $name;

			$newdata->minlevel = Number(ord(substr($filedata, $fi, 1)), ord(substr($filedata, $fi+1, 1))); $fi += 2;
			$newdata->maxlevel = Number(ord(substr($filedata, $fi, 1)), ord(substr($filedata, $fi+1, 1))); $fi += 2;
			$newdata->clasreq = Number(ord(substr($filedata, $fi, 1))); $fi += 1;
			$newdata->numskills = Number(ord(substr($filedata, $fi, 1)), ord(substr($filedata, $fi+1, 1))); $fi += 2;
			
			for ($s = 1; $s <= $newdata->numskills; ++$s) 
			{
				$newskill = new skill;
				
				$newskill->id = Number(ord(substr($filedata, $fi, 1)), ord(substr($filedata, $fi+1, 1))); $fi += 2;
				$newskill->levelreq = Number(ord(substr($filedata, $fi, 1)), ord(substr($filedata, $fi+1, 1))); $fi += 2;
				$newskill->clasreq = Number(ord(substr($filedata, $fi, 1))); $fi += 1;
				$newskill->cost = Number(ord(substr($filedata, $fi, 1)), ord(substr($filedata, $fi+1, 1)), ord(substr($filedata, $fi+2, 1))); $fi += 3;
				$newskill->sk1 = Number(ord(substr($filedata, $fi, 1)), ord(substr($filedata, $fi+1, 1))); $fi += 2;
				$newskill->sk2 = Number(ord(substr($filedata, $fi, 1)), ord(substr($filedata, $fi+1, 1))); $fi += 2;
				$newskill->sk3 = Number(ord(substr($filedata, $fi, 1)), ord(substr($filedata, $fi+1, 1))); $fi += 2;
				$newskill->sk4 = Number(ord(substr($filedata, $fi, 1)), ord(substr($filedata, $fi+1, 1))); $fi += 2;
				$newskill->strreq = Number(ord(substr($filedata, $fi, 1)), ord(substr($filedata, $fi+1, 1))); $fi += 2;
				$newskill->intlreq = Number(ord(substr($filedata, $fi, 1)), ord(substr($filedata, $fi+1, 1))); $fi += 2;
				$newskill->wisreq = Number(ord(substr($filedata, $fi, 1)), ord(substr($filedata, $fi+1, 1))); $fi += 2;
				$newskill->agireq = Number(ord(substr($filedata, $fi, 1)), ord(substr($filedata, $fi+1, 1))); $fi += 2;
				$newskill->conreq = Number(ord(substr($filedata, $fi, 1)), ord(substr($filedata, $fi+1, 1))); $fi += 2;
				$newskill->chareq = Number(ord(substr($filedata, $fi, 1)), ord(substr($filedata, $fi+1, 1))); $fi += 2;
				
				array_push($newdata->skills, $newskill);
			}

			array_push($this->data, $newdata);
		}
		
	}

	function Get($id)
	{
		$found = false;
		foreach ($this->Data() as $data)
		{
			if ($data->id == $id)
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
