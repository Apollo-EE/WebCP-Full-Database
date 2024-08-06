<?php

function DecodeEMF($mapname)
{
    $mapname = str_replace("ÿ", "", $mapname);
    $rname = strrev($mapname);
    $flip = (strlen($rname) % 2) == 1;
    $chars = str_split($rname);
    for ($i = 0; $i < strlen($rname); ++$i)
    {
        $c = ord($chars[$i]);

        if ($flip)
        {
            if ($c >= 0x22 && $c <= 0x4F)
                $c = 0x71 - $c;
            else if ($c >= 0x50 && $c <= 0x7E)
                $c = 0xCD - $c;
        }
        else
        {
            if ($c >= 0x22 && $c <= 0x7E)
                $c = 0x9F - $c;
        }
		if ($c != 0xFF)
			$chars[$i] = chr($c);
        $flip = !$flip;
    }
    $decode = implode($chars);
	$decode = substr($decode, 0, -8);
	$decoded = preg_replace("/[^a-zA-Z0-9# ]+/", "", $decode);
    return $decoded;
}

class mapnpc
{
	var $id;
	var $amount;
	var $speed;
	var $spawntime;
	var $xloc;
	var $yloc;
}

class mapkey
{
	var $id;
	var $xloc;
	var $yloc;
}

class mapitem
{
	var $id;
	var $amount;
	var $xloc;
	var $yloc;
	var $slot;
	var $keyid;
	var $spawntime;
}

class mapwarp
{
	var $xloc;
	var $yloc;
	var $levelreq;
	var $door;
	var $warpmap;
	var $warpx;
	var $warpy;
}

class Map_Data
{
	var $id;
	var $name;
	var $combat;
	var $hazard;
	var $width;
	var $height;
	var $minimap;
	var $scroll;
	var $rx;
	var $ry;

	public $mapnpcs = array();
	public $mapkeys = array();
	public $mapitems = array();
	public $mapwarps = array();
	public $maps = array();
}

class MapReader
{
	private $data;

	function __construct($eoserv_npcs, $eoserv_items, $mapfiles, $mapcheck)
	{
		$this->data = array(0 => new Map_Data);
		
		foreach ($mapcheck as $mfile)
		{
			if (strlen($mfile) >= 9 && substr($mfile, -3) == "emf")
			{
				
				$filedata = file_get_contents($mapfiles."/".$mfile);
			
				$rid = substr($filedata, 3, 4);
				
				$fi = 7;

				$newdata = new Map_Data;
				
				$newdata->id = ltrim(substr($mfile, 0, -4), 0);
				$namelen = 24;
				$name = substr($filedata, $fi, $namelen); $fi += $namelen;

				$tempname =DecodeEMF($name);
				$newdata->name = (strlen($tempname) == 0) ? 'Untitled Map' : $tempname;
				$newdata->combat = Number(ord(substr($filedata, $fi, 1))); $fi += 1; 
				$newdata->hazard = Number(ord(substr($filedata, $fi, 1))); $fi += 1;
				$musicid = Number(ord(substr($filedata, $fi, 1))); $fi += 1;
				$musiccontrol = Number(ord(substr($filedata, $fi, 1))); $fi += 1;
				$ambient = Number(ord(substr($filedata, $fi, 1)), ord(substr($filedata, $fi+1, 1))); $fi += 2;
				$newdata->width = 1 + Number(ord(substr($filedata, $fi, 1))); $fi += 1;
				$newdata->height = 1 + Number(ord(substr($filedata, $fi, 1))); $fi += 1;
				$fill = Number(ord(substr($filedata, $fi, 1)), ord(substr($filedata, $fi+1, 1))); $fi += 2;
				$newdata->minimap = Number(ord(substr($filedata, $fi, 1))); $fi += 1;
				$newdata->scroll = Number(ord(substr($filedata, $fi, 1))); $fi += 1;
				$newdata->rx = Number(ord(substr($filedata, $fi, 1))); $fi += 1;
				$newdata->ry = Number(ord(substr($filedata, $fi, 1))); $fi += 1;
				$unknown = Number(ord(substr($filedata, $fi, 1))); $fi += 1;
				$npccount = Number(ord(substr($filedata, $fi, 1))); $fi += 1;
				for ($i = 1; $i <= $npccount; ++$i) 
				{
					$newnpc = new mapnpc;
					$newnpc->xloc = Number(ord(substr($filedata, $fi, 1))); $fi += 1; 
					$newnpc->yloc = Number(ord(substr($filedata, $fi, 1))); $fi += 1; 
					$newnpc->id = Number(ord(substr($filedata, $fi, 1)), ord(substr($filedata, $fi+1, 1))); $fi += 2;
					$newnpc->speed = Number(ord(substr($filedata, $fi, 1))); $fi += 1; 
					$newnpc->spawntime = Number(ord(substr($filedata, $fi, 1)), ord(substr($filedata, $fi+1, 1))); $fi += 2;
					$newnpc->amount = Number(ord(substr($filedata, $fi, 1))); $fi += 1;
					$found = false;
					if (isset($newdata->mapnpcs))
					{
						foreach ($newdata->mapnpcs as $npccheck)
						{
							if ($npccheck->id == $newnpc->id)
								$found = true;
						}
					}					
					if (!$found)
						array_push($newdata->mapnpcs, $newnpc);
				}
				$keycount = Number(ord(substr($filedata, $fi, 1))); $fi += 1; 
				for ($i = 1; $i <= $keycount; ++$i) 
				{
					$newkey = new mapkey;
					$newkey->xloc = Number(ord(substr($filedata, $fi, 1))); $fi += 1; 
					$newkey->yloc = Number(ord(substr($filedata, $fi, 1))); $fi += 1; 
					$newkey->id = Number(ord(substr($filedata, $fi, 1)), ord(substr($filedata, $fi+1, 1))); $fi += 2;
					
					array_push($newdata->mapkeys, $newkey);
				}
				$itemcount = Number(ord(substr($filedata, $fi, 1))); $fi += 1;
				for ($i = 1; $i <= $itemcount; ++$i) 
				{
					$newitem = new mapitem;
					$newitem->xloc = Number(ord(substr($filedata, $fi, 1))); $fi += 1; 
					$newitem->yloc = Number(ord(substr($filedata, $fi, 1))); $fi += 1; 
					$newitem->keyid = Number(ord(substr($filedata, $fi, 1)), ord(substr($filedata, $fi+1, 1))); $fi += 2;
					$newitem->slot = Number(ord(substr($filedata, $fi, 1))); $fi += 1; 
					$newitem->id = Number(ord(substr($filedata, $fi, 1)), ord(substr($filedata, $fi+1, 1))); $fi += 2;
					$newitem->spawntime = Number(ord(substr($filedata, $fi, 1)), ord(substr($filedata, $fi+1, 1))); $fi += 2;
					$newitem->amount = Number(ord(substr($filedata, $fi, 1)), ord(substr($filedata, $fi+1, 1)), ord(substr($filedata, $fi+2, 1))); $fi += 3;					
					
					array_push($newdata->mapitems, $newitem);
				}
				$tilespecrows = Number(ord(substr($filedata, $fi, 1))); $fi += 1;
				for ($i = 1; $i <= $tilespecrows; ++$i) 
				{
					$specy = Number(ord(substr($filedata, $fi, 1))); $fi += 1;
					$tilecount = Number(ord(substr($filedata, $fi, 1))); $fi += 1;
					for ($j = 1; $j <= $tilecount; ++$j) 
					{
						$specx = Number(ord(substr($filedata, $fi, 1))); $fi += 1;
						$tilespec = Number(ord(substr($filedata, $fi, 1))); $fi += 1;
					}
				}
				$warprows = Number(ord(substr($filedata, $fi, 1))); $fi += 1;

				for ($i = 1; $i <= $warprows; ++$i) 
				{
					$yloc = Number(ord(substr($filedata, $fi, 1))); $fi += 1;
					$warpcount = Number(ord(substr($filedata, $fi, 1))); $fi += 1;
					
					for ($j = 1; $j <= $warpcount; ++$j) 
					{
						$newwarp = new mapwarp;
						$newwarp->yloc = $yloc;
						$newwarp->xloc = Number(ord(substr($filedata, $fi, 1))); $fi += 1;
						$newwarp->warpmap = Number(ord(substr($filedata, $fi, 1)), ord(substr($filedata, $fi+1, 1))); $fi += 2;
						$newwarp->warpx = Number(ord(substr($filedata, $fi, 1))); $fi += 1;
						$newwarp->warpy = Number(ord(substr($filedata, $fi, 1))); $fi += 1;
						$newwarp->levelreq = Number(ord(substr($filedata, $fi, 1))); $fi += 1;
						$newwarp->door = Number(ord(substr($filedata, $fi, 1)), ord(substr($filedata, $fi+1, 1))); $fi += 2;

						array_push($newdata->mapwarps, $newwarp);

					}
				}

				$graphicrows = Number(ord(substr($filedata, $fi, 1))); $fi += 1;
				for ($i = 1; $i <= $graphicrows; ++$i) 
				{
					$graphicy = Number(ord(substr($filedata, $fi, 1))); $fi += 1;
					$graphiccount = Number(ord(substr($filedata, $fi, 1))); $fi += 1;
					for ($j = 1; $j <= $graphiccount; ++$j) 
					{
						$graphicx = Number(ord(substr($filedata, $fi, 1))); $fi += 1;
						$graphic = Number(ord(substr($filedata, $fi, 1)), ord(substr($filedata, $fi+1, 1))); $fi += 2;
					}
				}
				$signcount = Number(ord(substr($filedata, $fi, 1))); $fi += 1;
				for ($i = 1; $i <= $signcount; ++$i)
				{
					$signx = Number(ord(substr($filedata, $fi, 1))); $fi += 1;
					$signy = Number(ord(substr($filedata, $fi, 1))); $fi += 1;
					$signlength =Number(ord(substr($filedata, $fi, 1)), ord(substr($filedata, $fi+1, 1))); $fi += 2;
					$textdata = substr($filedata, $fi, $signlength - 1); $fi += ($signlength - 1);	
					$titlelength = Number(ord(substr($filedata, $fi, 1))); $fi += 1;	
					$text = DecodeEMF($textdata);
					$title = substr($text, 0, $titlelength);
					$message = substr($text, $titlelength);
				}
				
				array_push($this->data, $newdata);					
			}
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
