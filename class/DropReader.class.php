<?php

class drop
{
	var $id;
	var $minimum;
	var $maximum;
	var $chance;
}

class Drop_Data
{
	var $id;
	var $numitems;
	public $drops = array();
}

class DropReader
{
	private $data;
	const DATA_SIZE = 4;
	const ENTRY_SIZE = 10;

	function __construct($filename)
	{
		$this->data = array(0 => new Drop_Data);
		$filedata = file_get_contents($filename);
		
		$rid = substr($filedata, 3, 4);
		$len = substr($filedata, 7, 2);
		$len = Number(ord($len[0]), ord($len[1]));
		
		$fi = 10;
		for ($i = 1; $i <= $len; ++$i)
		{
			$newdata = new Drop_Data;
			
			$newdata->id = Number(ord(substr($filedata, $fi, 1)), ord(substr($filedata, $fi+1, 1))); $fi += 2;
			$newdata->numitems = Number(ord(substr($filedata, $fi, 1)), ord(substr($filedata, $fi+1, 1))); $fi += 2;
			$dropsum = 0;
			for ($c = 1; $c <= $newdata->numitems; ++$c) 
			{
				$newdrop = new drop;
				
				$newdrop->id = Number(ord(substr($filedata, $fi, 1)), ord(substr($filedata, $fi+1, 1))); $fi += 2;
				$newdrop->minimum = Number(ord(substr($filedata, $fi, 1)), ord(substr($filedata, $fi+1, 1)), ord(substr($filedata, $fi+2, 1))); $fi += 3;
				$newdrop->maximum = Number(ord(substr($filedata, $fi, 1)), ord(substr($filedata, $fi+1, 1)), ord(substr($filedata, $fi+2, 1))); $fi += 3;
				$newdrop->chance = Number(ord(substr($filedata, $fi, 1)), ord(substr($filedata, $fi+1, 1))); $fi += 2;
				if ($newdrop->minimum)
					$dropsum += $newdrop->chance;
				array_push($newdata->drops, $newdrop);
			}
			$newdata->dropsum = $dropsum;
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
