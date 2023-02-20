<?php

class chat
{
	var $id;
	var $message;
}

class Chat_Data
{
	var $id;
	var $rate;
	var $chance;
	var $chatcount;
		
	public $chats = array();
}

class ChatReader
{
	private $data;
	const DATA_SIZE = 5;

	function __construct($filename)
	{
		$this->data = array(0 => new Chat_Data);
		$filedata = file_get_contents($filename);
		
		$rid = substr($filedata, 3, 4);
		$len = substr($filedata, 7, 2);
		$len = Number(ord($len[0]), ord($len[1]));
		
		$fi = 10;
		for ($i = 1; $i <= $len; ++$i)
		{
			$newdata = new Chat_Data;
			
			$newdata->id = Number(ord(substr($filedata, $fi, 1)), ord(substr($filedata, $fi+1, 1))); $fi += 2;
			$newdata->rate = Number(ord(substr($filedata, $fi, 1))); $fi += 1;
			$newdata->chance = Number(ord(substr($filedata, $fi, 1))); $fi += 1;
			$newdata->chatcount = Number(ord(substr($filedata, $fi, 1))); $fi += 1;
			
			for ($m = 1; $m <= $newdata->chatcount; ++$m) 
			{
				$newchat = new chat;
				
				$newchat->id = $m;
				$messagelen = Number(ord(substr($filedata, $fi, 1))); $fi += 1;
				$message = substr($filedata, $fi, $messagelen); $fi += $messagelen;	
				$newchat->message = $message;				
				
				array_push($newdata->chats, $newchat);
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
