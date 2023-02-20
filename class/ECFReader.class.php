<?php

class ECF_Data
{
	var $id;
	var $name;
	
	var $base;
	var $type;
	var $str;
	var $intl;
	var $wis;
	var $agi;
	var $con;
	var $cha;
}

class ECFReader
{
	private $data;
	const DATA_SIZE = 14;

	function __construct($filename)
	{
		$this->data = array(0 => new ECF_Data);
		$filedata = file_get_contents($filename);
		
		$rid = substr($filedata, 3, 4);
		$len = substr($filedata, 7, 2);
		$len = Number(ord($len[0]), ord($len[1]));
		
		$fi = 10;
		for ($i = 0; $i < $len; ++$i)
		{
			$newdata = new ECF_Data;
			
			$newdata->id = $i+1;
			$namelen = Number(ord(substr($filedata, $fi, 1))); $fi += 1;
			$name = substr($filedata, $fi, $namelen); $fi += $namelen;
			$newdata->name = $name;
			
			$newdata->base = Number(ord(substr($filedata, $fi, 1))); $fi += 1;
			$newdata->type = Number(ord(substr($filedata, $fi, 1))); $fi += 1;
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
