<?php

class trade
{
	var $id;
	var $buy;
	var $sell;
	var $cart;
}

class craft
{
	var $id;
	var $ing1;
	var $amt1;
	var $ing2;
	var $amt2;
	var $ing3;
	var $amt3;
	var $ing4;
	var $amt4;
}

class Shop_Data
{
	var $id;
	var $name;
	var $minlevel;
	var $maxlevel;
	var $clasreq;
	var $numtrades;
	var $numcrafts;
	public $trades = array();
	public $crafts = array();
}

class ShopReader
{
	private $data;
	const DATA_SIZE = 8;
	const TRADE_SIZE = 9;
	const CRAFT_SIZE = 14;

	function __construct($filename)
	{
		$this->data = array(0 => new Shop_Data);
		$filedata = file_get_contents($filename);
		
		$rid = substr($filedata, 3, 4);
		$len = substr($filedata, 7, 2);
		$len = Number(ord($len[0]), ord($len[1]));
		
		$fi = 10;
		for ($i = 1; $i <= $len; ++$i)
		{
			$newdata = new Shop_Data;
			
			$newdata->id = Number(ord(substr($filedata, $fi, 1)), ord(substr($filedata, $fi+1, 1))); $fi += 2;

			$namelen = Number(ord(substr($filedata, $fi, 1))); $fi += 1;
			$name = substr($filedata, $fi, $namelen); $fi += $namelen;	
			$newdata->name = $name;
			
			$newdata->minlevel = Number(ord(substr($filedata, $fi, 1)), ord(substr($filedata, $fi+1, 1))); $fi += 2;
			$newdata->maxlevel = Number(ord(substr($filedata, $fi, 1)), ord(substr($filedata, $fi+1, 1))); $fi += 2;
			$newdata->clasreq = Number(ord(substr($filedata, $fi, 1))); $fi += 1;
			$newdata->numtrades = Number(ord(substr($filedata, $fi, 1)), ord(substr($filedata, $fi+1, 1))); $fi += 2;
			$newdata->numcrafts = Number(ord(substr($filedata, $fi, 1))); $fi += 1;
			
			for ($t = 1; $t <= $newdata->numtrades; ++$t) 
			{
				$newtrade = new trade;
				
				$newtrade->id = Number(ord(substr($filedata, $fi, 1)), ord(substr($filedata, $fi+1, 1))); $fi += 2;
				$newtrade->buy = Number(ord(substr($filedata, $fi, 1)), ord(substr($filedata, $fi+1, 1)), ord(substr($filedata, $fi+2, 1))); $fi += 3;
				$newtrade->sell = Number(ord(substr($filedata, $fi, 1)), ord(substr($filedata, $fi+1, 1)), ord(substr($filedata, $fi+2, 1))); $fi += 3;
				$newtrade->cart =Number(ord(substr($filedata, $fi, 1))); $fi += 1;
				
				array_push($newdata->trades, $newtrade);
			}
			for ($c = 1; $c <= $newdata->numcrafts; ++$c)
			{
				$newcraft = new craft;
				
				$newcraft->id = Number(ord(substr($filedata, $fi, 1)), ord(substr($filedata, $fi+1, 1))); $fi += 2;
				$newcraft->ing1 = Number(ord(substr($filedata, $fi, 1)), ord(substr($filedata, $fi+1, 1))); $fi += 2;
				$newcraft->amt1 = Number(ord(substr($filedata, $fi, 1))); $fi += 1;
				$newcraft->ing2 = Number(ord(substr($filedata, $fi, 1)), ord(substr($filedata, $fi+1, 1))); $fi += 2;
				$newcraft->amt2 = Number(ord(substr($filedata, $fi, 1))); $fi += 1;
				$newcraft->ing3 = Number(ord(substr($filedata, $fi, 1)), ord(substr($filedata, $fi+1, 1))); $fi += 2;
				$newcraft->amt3 = Number(ord(substr($filedata, $fi, 1))); $fi += 1;
				$newcraft->ing4 = Number(ord(substr($filedata, $fi, 1)), ord(substr($filedata, $fi+1, 1))); $fi += 2;
				$newcraft->amt4 = Number(ord(substr($filedata, $fi, 1))); $fi += 1;
				
				array_push($newdata->crafts, $newcraft);
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
