<?php

class itemreward
{
	var $id;
	var $amount;

}

class spellreward
{
	var $id;
}

class expreward
{
	var $amount;
}

class clasreward
{
	var $id;
}

class karmareward
{
	var $amount;
}

class karmataken
{
	var $amount;
}

class questnpc
{
	var $id;
}

class Quest_Data
{
	var $id;
	var $name;
	var $questends;
	var $questdaily;

	public $questnpcs = array();
	public $itemrewards = array();
	public $spellrewards = array();
	public $exprewards = array();
	public $clasrewards = array();
	public $karmarewards = array();
	public $karmalosses = array();
}

class QuestReader
{
	private $data;

	function __construct($eoserv_npcs, $questfiles, $questext)
	{
		$this->data = array(0 => new Quest_Data);
		
		foreach ($eoserv_npcs->Data() as $npc)
		{
			$questdata = '';
			$questid = '';
			if ($npc->type == 15 && $npc->shopid > 0)
			{
				$newdata = new Quest_Data;
				$questid = $npc->shopid;
				$questfile = $questfiles . '/' . str_pad($questid, 5, '0', STR_PAD_LEFT) . $questext;

				if (file_exists($questfile))
				{	
					$questdata = file_get_contents($questfile);

					$questdataslim = preg_replace('~"[^"]*"(*SKIP)(*F)|\s+~',"", $questdata);

					$count = preg_match_all('/questname\"(.*?)\"/i', $questdataslim, $name);

					$newdata->id = $questid;
					$newdata->name = $name[1][0];
					$startnpc = new questnpc;
						array_push($newdata->questnpcs, $startnpc);
					$countnpcs = preg_match_all('/addnpctext\((.*?)\,/i', $questdataslim, $npc);
					for ($i = 0; $i < $countnpcs; ++$i)
					{
						foreach ($eoserv_npcs->Data() as $cknpc)
						{
							if ($cknpc-> type == 15 && $cknpc->shopid == $npc[1][$i])
							{
								$newnpc = new questnpc;
								$newnpc->id = $cknpc->id;
								if (!in_array($newnpc, $newdata->questnpcs))
								{	
									array_push($newdata->questnpcs, $newnpc);
								}	
							}
						}
					}


					$countitem = preg_match_all('/giveitem\((.*?)\)/i', $questdataslim, $item);
					for ($i = 0; $i < $countitem; ++$i)
					{
						$newitem = new itemreward;
						$itemdata = explode(",", $item[1][$i]);
						$newitem->id = $itemdata[0];
						if (!isset($itemdata[1]))
							$itemdata[1] = 1;
						$newitem->amount = $itemdata[1];
						array_push($newdata->itemrewards, $newitem);		
					}
					
					$countspell = preg_match_all('/givespell\((.*?)\)/i', $questdataslim, $spell);
					for ($i = 0; $i < $countspell; ++$i)
					{
						$newspell = new spellreward;
						$newspell->id = $spell[1][$i];
						if (!in_array($newspell, $newdata->spellrewards))
							array_push($newdata->spellrewards, $newspell);					
					}
					
					$countexp = preg_match_all('/giveexp\((.*?)\)/i', $questdataslim, $exp);

					for ($i = 0; $i < $countexp; ++$i)
					{
						$newexp = new expreward;
						$newexp->amount = $exp[1][$i];
						if (!in_array($newexp, $newdata->exprewards))
							array_push($newdata->exprewards, $newexp);
					}
					
					$countclas = preg_match_all('/setclass\((.*?)\)/i', $questdataslim, $clas);

					for ($j = 0; $j < $countclas; ++$j)
					{
						$newclas = new clasreward;
						$newclas->id = $clas[1][$j];
						array_push($newdata->clasrewards, $newclas);
					}
					
					$countkarmag = preg_match_all('/givekarma\((.*?)\)/i', $questdataslim, $karmag);

					for ($j = 0; $j < $countkarmag; ++$j)
					{
						$newkarmag = new karmareward;
						$newkarmag->amount = $karmag[1][$j];
						array_push($newdata->karmarewards, $newkarmag);
					}
					
					$countkarmal = preg_match_all('/removekarma\((.*?)\)/i', $questdataslim, $karmal);

					for ($j = 0; $j < $countkarmag; ++$j)
					{
						$newkarmal = new karmataken;
						$newkarmal->amount = $karmal[1][$j];
						array_push($newdata->karmalosses, $newkarmal);
					}
					
					$countdaily = preg_match_all('/donedaily\((.*?)\)/i', $questdataslim, $daily);
					if ($countdaily)				
						$newdata->questdaily = $daily[1][0];					
					else
						$newdata->questdaily = 0;
					
					$countend = preg_match_all('/end\((.*?)\)/i', $questdataslim, $end);

					$newdata->questends = $countend;
						
					if (!in_array($newdata, $this->data))
						array_push($this->data, $newdata);
				}
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
