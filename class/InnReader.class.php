<?php

class quiz
{
	var $question;
	var $answer;
}

class Inn_Data
{
	var $id;
	var $name;
	var $cost;
	var $sleepmap;
	var $sleepx;
	var $sleepy;
	var $spawnmap;
	var $spawnx;
	var $spawny;
	var $hi_level;
	var $hi_spawnmap;
	var $hi_spawnx;
	var $hi_spawny;
	var $quizcount;
		
	public $quizzes = array();
}

class InnReader
{
	private $data;
	const DATA_SIZE = 18;

	function __construct($filename)
	{
		$this->data = array(0 => new Inn_Data);
		$filedata = file_get_contents($filename);
		
		$rid = substr($filedata, 3, 4);
		$len = substr($filedata, 7, 2);
		$len = Number(ord($len[0]), ord($len[1]));
		
		$fi = 10;
		for ($i = 1; $i <= $len; ++$i)
		{
			$newdata = new Inn_Data;
			
			$newdata->id = Number(ord(substr($filedata, $fi, 1)), ord(substr($filedata, $fi+1, 1))); $fi += 2;

			$namelen = Number(ord(substr($filedata, $fi, 1))); $fi += 1;
			$name = substr($filedata, $fi, $namelen); $fi += $namelen;	
			$newdata->name = $name;
			
			$newdata->cost = Number(ord(substr($filedata, $fi, 1)), ord(substr($filedata, $fi+1, 1)), ord(substr($filedata, $fi+2, 1))); $fi += 3;
			$newdata->sleepmap = Number(ord(substr($filedata, $fi, 1)), ord(substr($filedata, $fi+1, 1))); $fi += 2;
			$newdata->sleepx = Number(ord(substr($filedata, $fi, 1))); $fi += 1;
			$newdata->sleepy = Number(ord(substr($filedata, $fi, 1))); $fi += 1;
			$newdata->spawnmap = Number(ord(substr($filedata, $fi, 1)), ord(substr($filedata, $fi+1, 1))); $fi += 2;
			$newdata->spawnx = Number(ord(substr($filedata, $fi, 1))); $fi += 1;
			$newdata->spawny = Number(ord(substr($filedata, $fi, 1))); $fi += 1;			
			$newdata->hi_level = Number(ord(substr($filedata, $fi, 1)), ord(substr($filedata, $fi+1, 1))); $fi += 2;
			$newdata->hi_spawnmap = Number(ord(substr($filedata, $fi, 1)), ord(substr($filedata, $fi+1, 1))); $fi += 2;
			$newdata->hi_spawnx = Number(ord(substr($filedata, $fi, 1))); $fi += 1;
			$newdata->hi_spawny = Number(ord(substr($filedata, $fi, 1))); $fi += 1;
			$newdata->quizcount = Number(ord(substr($filedata, $fi, 1))); $fi += 1;
			
			for ($q = 1; $q <= $newdata->quizcount; ++$q) 
			{
				$newquiz = new quiz;
				
				$questionlen = Number(ord(substr($filedata, $fi, 1))); $fi += 1;
				$question = substr($filedata, $fi, $questionlen); $fi += $questionlen;	
				$newquiz->question = $question;
				$answerlen = Number(ord(substr($filedata, $fi, 1))); $fi += 1;
				$answer = substr($filedata, $fi, $answerlen); $fi += $answerlen;	
				$newquiz->answer = $answer;				
				
				array_push($newdata->quizzes, $newquiz);
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
	
	function GetName($name)
	{
		$found = false;
		foreach ($this->Data() as $data)
		{
			if ($data->name == $name)
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
