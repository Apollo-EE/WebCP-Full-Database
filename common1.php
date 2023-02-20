<?php

function Number($b1, $b2 = 254, $b3 = 254, $b4 = 254)
{
	if ($b1 == 0 || $b1 == 254) $b1 = 1;
	if ($b2 == 0 || $b2 == 254) $b2 = 1;
	if ($b3 == 0 || $b3 == 254) $b3 = 1;
	if ($b4 == 0 || $b4 == 254) $b4 = 1;

	--$b1;
	--$b2;
	--$b3;
	--$b4;

	return ($b4*16194277 + $b3*64009 + $b2*253 + $b1);
}

function timeago_full($pre,$now=NULL,$suffix=true)
{
	if ($now === NULL)
	{
		$now = time();
	}

	$times = array(
		array(1,'second'),
		array(60,'minute'),
		array(60*60,'hour'),
		array(24*60*60,'day'),
		array(7*60*60*24,'week'),
		array(52*60*60*24*7,'year'),
	);

	$diff = $now - $pre;

	if ($suffix)
	{
		$ago = ($diff >= 0)?' ago':' from now';
	}
	else
	{
		$ago = '';
	}

	$diff = abs($diff);
	$text = '';

	for ($i=count($times)-1; $i>=0; --$i)
	{
		$x = floor($diff/$times[$i][0]);
		$diff -= $x*$times[$i][0];
		if ($x > 0)
		{
			$text .= "$x ".$times[$i][1].(($x == 1)?'':'s').', ';
		}
	}

	if ($text == '')
	{
		$text = '0 seconds, ';
	}

	return substr($text,0,-2).$ago;
}

function webcp_error_handler($errno, $errstr, $errfile, $errline)
{
	global $tpl;
	$errfile = basename($errfile);
	if ((error_reporting() & $errno) != $errno)
	{
		return;
	}
	if (isset($tpl) && !$tpl->MainExecuted())
	{
		$tpl->error = "$errstr ($errfile:$errline)";
		$tpl->Execute('error');
		exit;
	}
	else
	{
		exit("<br><b>Error:</b> $errstr ($errfile:$errline)<br>");
	}
}
set_error_handler("webcp_error_handler");

function webcp_exception_handler($e)
{
	$classname = 'Exception';
	$reflector = new ReflectionClass($e);
	$classname = $reflector->getName();
	webcp_error_handler(E_ERROR, "Uncaught $classname: ".$e->getMessage(), $e->getFile(), $e->getLine());
}
set_exception_handler("webcp_exception_handler");

function webcp_debug_info()
{
	global $db;
	global $starttime;
	$exectime = number_format((microtime(true) - $starttime)*1000, 1);
	echo "Total execution time: $exectime ms<br>";
	foreach ($db->Debug() as $query)
	{
		$exectime = number_format($query[1], 1);
		echo htmlentities($query[0])." -- ($exectime ms)<br>";
	}
}

function get_string_between($string, $start, $end){
    $string = ' ' . $string;
    $ini = strpos($string, $start);
    if ($ini == 0) return '';
    $ini += strlen($start);
    $len = strpos($string, $end, $ini) - $ini;
    return substr($string, $ini, $len);
}

require 'ipcrypt.php';

if (!function_exists('hash'))
{
	exit("Could not find the the hash PHP extension.");
}

if (array_search('sha256',hash_algos()) === false)
{
	exit("Could not find the the sha256 hash algorithm.");
}

if (!function_exists('mysql_connect') && !class_exists('PDO'))
{
	exit("Could not find the the mysql or PDO PHP extensions.");
}

define('ADMIN_HGM', 4);
define('ADMIN_GM', 3);
define('ADMIN_GUARDIAN', 2);
define('ADMIN_GUIDE', 1);
define('ADMIN_PLAYER', 0);

define('RACE_WHITE', 0);
define('RACE_YELLOW', 1);
define('RACE_TAN', 2);
define('RACE_ORC', 3);
define('RACE_SKELETON', 4);
define('RACE_PANDA', 5);
define('RACE_FISH', 6);
define('RACE_FISH2', 7);
define('RACE_LIZARD', 8);
define('RACE_SQUIRREL', 9);
define('RACE_BIRD', 10);
define('RACE_DEVIL', 11);


require 'config.php';

if (!empty($DEBUG))
{
	$starttime = microtime(true);
	register_shutdown_function('webcp_debug_info');
}

require 'class/Template.class.php';
require 'class/Session.class.php';

try
{
	switch ($dbtype)
	{
		case 'sqlite':
			$dsn = "sqlite:" . $dbhost;
			$db = new PDO($dsn);
			break;
	
		case 'mysql':
			$dsn = "mysql:host=" . $dbhost;

			if (isset($dbport))
				$dsn .= ";port=" . $dbport;

			if (isset($dbname))
				$dsn .= ";dbname=" . $dbname;

			$db = new PDO($dsn, $dbuser, $dbpass);
			break;

		default:
			throw new Exception("Unknown DB type");
	}

	$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
}
catch (Exception $e)
{
	exit("Database connection failed. (".$e->getMessage().")");
}

function webcp_db_execute_typed($statement, $params = null)
{
	for ($i = 0; $i < count($params); ++$i)
	{
		$p = $params[$i];

		if (is_null($p))
			$statement->bindValue($i + 1, $p, PDO::PARAM_NULL);
		else if (is_int($p))
			$statement->bindValue($i + 1, $p, PDO::PARAM_INT);
		else
			$statement->bindValue($i + 1, $p, PDO::PARAM_STR);
	}

	return $statement->execute();
}

function webcp_db_execute_array($sql, $params = null)
{
	global $db;
	
	if (is_null($params))
		$params = array();

	$statement = $db->prepare($sql);
	
	if (webcp_db_execute_typed($statement, $params))
		return $statement->rowCount();
	else
		return false;
}

function webcp_db_fetchall_array($sql, $params = null)
{
	global $db;

	if (is_null($params))
		$params = array();

	$statement = $db->prepare($sql);
	
	if (webcp_db_execute_typed($statement, $params))
		return $statement->fetchAll(PDO::FETCH_ASSOC);
	else
		return array();
}

function webcp_db_execute($sql/*, $params...*/)
{
	$params = func_get_args();
	array_shift($params);

	return webcp_db_execute_array($sql, $params);
}

function webcp_db_fetchall($sql/*, $params...*/)
{
	$params = func_get_args();
	array_shift($params);

	return webcp_db_fetchall_array($sql, $params);
}

$tpl = new Template('tpl/'.$template, true);
$sess = new Session($cpid.'_EOSERVCP');

$tpl->pagetitle = $pagetitle;
$tpl->sitename = $sitename;
$tpl->homeurl = $homeurl;
$tpl->php = $phpext;
$tpl->onlinecharacters = 0;
$tpl->maxplayers = $maxplayers;
$tpl->serverhost = $serverhost;
$tpl->serverport = $serverport;


if (!is_dir($pubfiles))
{
	exit("Directory not found: $pubfiles");
}

if (!is_file($pubfiles.'/dat001.eif'))
{
	exit("File not found: $pubfiles/dat001.eif");
}

if (!is_file($pubfiles.'/dtn001.enf'))
{
	exit("File not found: $pubfiles/dtn001.enf");
}

if (!is_file($pubfiles.'/dat001.ecf'))
{
	exit("File not found: $pubfiles/dat001.ecf");
}

if (!is_file($pubfiles.'/dsl001.esf'))
{
	exit("File not found: $pubfiles/dsl001.esf");
}

if (!empty($NEEDPUB))
{
	require 'class/EIFReader.class.php';

	if ($pubcache && file_exists('eif.cache') && filemtime('eif.cache') >= filemtime($pubfiles.'/dat001.eif'))
	{
		$eoserv_items = unserialize(file_get_contents('eif.cache'));
	}
	else
	{
		$eoserv_items = new EIFReader("$pubfiles/dat001.eif");
		if ($pubcache)
		{
			file_put_contents('eif.cache', serialize($eoserv_items));
		}
	}

	require 'class/ENFReader.class.php';

	if ($pubcache && file_exists('enf.cache') && filemtime('enf.cache') >= filemtime($pubfiles.'/dtn001.enf'))
	{
		$eoserv_npcs = unserialize(file_get_contents('enf.cache'));
	}
	else
	{
		$eoserv_npcs = new ENFReader("$pubfiles/dtn001.enf");
		if ($pubcache)
		{
			file_put_contents('enf.cache', serialize($eoserv_npcs));
		}
	}

	require 'class/ECFReader.class.php';

	if ($pubcache && file_exists('ecf.cache') && filemtime('ecf.cache') >= filemtime($pubfiles.'/dat001.ecf'))
	{
		$eoserv_classes = unserialize(file_get_contents('ecf.cache'));
	}
	else
	{
		$eoserv_classes = new ECFReader("$pubfiles/dat001.ecf");
		if ($pubcache)
		{
			file_put_contents('ecf.cache', serialize($eoserv_classes));
		}
	}

	require 'class/ESFReader.class.php';

	if ($pubcache && file_exists('esf.cache') && filemtime('esf.cache') >= filemtime($pubfiles.'/dsl001.esf'))
	{
		$eoserv_spells = unserialize(file_get_contents('esf.cache'));
	}
	else
	{
		$eoserv_spells = new ESFReader("$pubfiles/dsl001.esf");
		if ($pubcache)
		{
			file_put_contents('esf.cache', serialize($eoserv_spells));
		}
	}
	
	require 'class/ChatReader.class.php';

	if ($pubcache && file_exists('chat.cache') && filemtime('chat.cache') >= filemtime($pubfiles.'/serv_chats.epf'))
	{
		$eoserv_chats = unserialize(file_get_contents('chat.cache'));
	}
	else
	{
		$eoserv_chats = new ChatReader("$pubfiles/serv_chats.epf");
		if ($pubcache)
		{
			file_put_contents('chats.cache', serialize($eoserv_chats));
		}
	}
	
	require 'class/DropReader.class.php';

	if ($pubcache && file_exists('drop.cache') && filemtime('drop.cache') >= filemtime($pubfiles.'/serv_drops.epf'))
	{
		$eoserv_drops = unserialize(file_get_contents('drop.cache'));
	}
	else
	{
		$eoserv_drops = new DropReader("$pubfiles/serv_drops.epf");
		if ($pubcache)
		{
			file_put_contents('drop.cache', serialize($eoserv_drops));
		}
	}
	
	require 'class/ShopReader.class.php';

	if ($pubcache && file_exists('shop.cache') && filemtime('shop.cache') >= filemtime($pubfiles.'/serv_shops.epf'))
	{
		$eoserv_shops = unserialize(file_get_contents('shop.cache'));
	}
	else
	{
		$eoserv_shops = new ShopReader("$pubfiles/serv_shops.epf");
		if ($pubcache)
		{
			file_put_contents('shop.cache', serialize($eoserv_shops));
		}
	}
	
	require 'class/TrainerReader.class.php';

	if ($pubcache && file_exists('trainer.cache') && filemtime('trainer.cache') >= filemtime($pubfiles.'/serv_trainers.epf'))
	{
		$eoserv_trainers = unserialize(file_get_contents('trainer.cache'));
	}
	else
	{
		$eoserv_trainers = new TrainerReader("$pubfiles/serv_trainers.epf");
		if ($pubcache)
		{
			file_put_contents('trainer.cache', serialize($eoserv_trainers));
		}
	}
	
	require 'class/InnReader.class.php';

	if ($pubcache && file_exists('inn.cache') && filemtime('inn.cache') >= filemtime($pubfiles.'/serv_inns.epf'))
	{
		$eoserv_inns = unserialize(file_get_contents('inn.cache'));
	}
	else
	{
		$eoserv_inns = new InnReader("$pubfiles/serv_inns.epf");
		if ($pubcache)
		{
			file_put_contents('inn.cache', serialize($eoserv_inns));
		}
	}	
	
	$questsmade = 0;
	
	foreach ($eoserv_npcs->Data() as $npc)
	{
		if ($npc->type == 15 && $npc->shopid > 0)
		{
			$questid = $npc->shopid;
			$questfile = $questfiles . '/' . str_pad($questid, 5, '0', STR_PAD_LEFT) . $questext;

			if (file_exists($questfile))
			{	
				$currentmade = filemtime($questfile);
				if ($currentmade > $questsmade)
				{
					$questsmade = $currentmade;
				}
			}
		}
	}
	
	require 'class/QuestReader.class.php';
	
	if ($pubcache && file_exists('quest.cache') && filemtime('quest.cache') >= $questsmade)
	{
		$eoserv_quests = unserialize(file_get_contents('quest.cache'));
	}
	else
	{
		$eoserv_quests = new QuestReader($eoserv_npcs, $questfiles, $questext);
		if ($pubcache)
		{
			file_put_contents('quest.cache', serialize($eoserv_quests));
		}
	}

	$mapscreated = 0;
	$mapcheck = scandir($mapfiles);
	foreach ($mapcheck as $mapdirfile)
	{
		if (strlen($mapdirfile) >= 9 && substr($mapdirfile, -3) == "emf")
		{	
			$currentmade = filemtime($mapfiles."/".$mapdirfile);
			if ($currentmade > $mapscreated)
			{
				$mapscreated = $currentmade;
			}
		}		
	}
	require 'class/MapReader.class.php';
	
	if ($pubcache && file_exists('map.cache') && filemtime('map.cache') >= $mapscreated)
	{
		$eoserv_maps = unserialize(file_get_contents('map.cache'));
	}
	else
	{
		$eoserv_maps = new MapReader($eoserv_npcs, $eoserv_items, $mapfiles, $mapcheck);
		if ($pubcache)
		{
			file_put_contents('map.cache', serialize($eoserv_maps));
		}
	}	
}
if (((isset($checkcsrf) && $checkcsrf) || $_SERVER['REQUEST_METHOD'] == 'POST') && (!isset($_REQUEST['csrf']) || !isset($sess->csrf) || $_REQUEST['csrf'] != $sess->csrf))
{
	header('HTTP/1.1 400 Bad Request');
	exit("<h1>400 - Bad Request</h1>");
}

if ($dynamiccsrf || !isset($sess->csrf))
{
	$tpl->csrf = $sess->csrf = $csrf = mt_rand();
}
else
{
	$tpl->csrf = $csrf = $sess->csrf;
}

if (!file_exists('online.cache') || filemtime('online.cache')+$onlinecache < time())
{
	$serverconn = @fsockopen($serverhost, $serverport, $errno, $errstr, 2.0);
	$tpl->online = $online = (bool)$serverconn;
	$onlinelist = array();
	if ($online)
	{
		$request_online = chr(5).chr(254).chr(1).chr(22).chr(254).chr(255);
		fwrite($serverconn, $request_online);
		$raw = fread($serverconn, 1024*256); // Read up to 256KB of data
		fclose($serverconn);
		$raw = substr($raw, 5); // length, ID, replycode
		$chars = Number(ord($raw[0]), ord($raw[1])); $raw = substr($raw, 2); // Number of characters
		$raw = substr($raw, 1); // separator
		for ($i = 0; $i < $chars; ++$i)
		{
			$newchar = array(
				'name' => '',
				'title' => '',
				'level' => '',
				'admin' => '',
				'class' => '',
				'guild' => '',
			);

			$pos = strpos($raw, chr(255));
			$newchar['name'] = substr($raw, 0, $pos);
			$raw = substr($raw, $pos+1);

			$pos = strpos($raw, chr(255));
			$newchar['title'] = substr($raw, 0, $pos);
			$raw = substr($raw, $pos+1);
			
			$newchar['level'] = Number(ord(substr($raw, 0, 3)));
			$raw = substr($raw, 1); // ?

			$newchar['admin'] = Number(ord(substr($raw, 0, 1)));
			$newchar['admin'] = ($newchar['admin'] == 4 || $newchar['admin'] == 5 || $newchar['admin'] == 9 || $newchar['admin'] == 10);
			$raw = substr($raw, 1);

			$newchar['class'] = Number(ord(substr($raw, 0, 1)));
			$raw = substr($raw, 1);

			$newchar['guild'] = trim(substr($raw, 0, 3));
			$raw = substr($raw, 3);

			$raw = substr($raw, 1); // separator

			$onlinelist[] = $newchar;
		}
		ksort($onlinelist);
		file_put_contents('online.cache', serialize($onlinelist));
	}
	else
	{
		file_put_contents('online.cache', 'OFFLINE');
	}
}
else
{
	$onlinedata = file_get_contents('online.cache');
	if ($onlinedata == 'OFFLINE')
	{
		$tpl->online = $online = false;
	}
	else
	{
		$tpl->online = $online = true;
		$onlinelist = unserialize($onlinedata);
	}
}

$tpl->onlinecharacters = isset($onlinelist)?count($onlinelist):0;

if ($online)
{
	$statusstr = '<span class="online">Online</span>';
}
else
{
	$statusstr = '<span class="offline">Offline</span>';
}

$tpl->statusstr = $statusstr;

if (isset($_REQUEST['action']))
{
	switch ($_REQUEST['action'])
	{
		case 'logout':
			unset($sess->username);

		case 'login':
			if (isset($_POST['username'], $_POST['password']))
			{
				$password = hash('sha256',$salt.strtolower($_POST['username']).substr($_POST['password'],0,12));
				$checklogin = webcp_db_fetchall("SELECT username FROM accounts WHERE username = ? AND password = ?", strtolower($_POST['username']), $password);
				if (empty($checklogin))
				{
					$tpl->message = "Login failed.";
					break;
				}
				else
				{
					$sess->username = $checklogin[0]['username'];
					$tpl->message = "Logged in.";
				}
			}
			break;
	}
}

$tpl->logged = $logged = isset($sess->username);
$tpl->username = $sess->username;
$userdata = webcp_db_fetchall("SELECT * FROM accounts WHERE username = ?", $sess->username);

if ($logged && empty($userdata))
{
	$tpl->message = "Your account has been deleted, logging out...";
	$tpl->logged = $logged = false;
}

$tpl->GUIDE = $GUIDE = false;
$tpl->GUARDIAN = $GUARDIAN = false;
$tpl->GM = $GM = false;
$tpl->HGM = $HGM = false;
$tpl->ART = $ART = false;

$chardata_guilds = array();
if (isset($userdata[0]))
{
	$userdata = $userdata[0];
	$chardata = webcp_db_fetchall("SELECT * FROM characters WHERE account = ?", $sess->username);
	foreach ($chardata as $cd)
	{
		if ($cd['admin'] >= ADMIN_GUIDE)
		{
			$tpl->GUIDE = $GUIDE = true;
		}

		if ($cd['admin'] >= ADMIN_GUARDIAN)
		{
			$tpl->GUARDIAN = $GUARDIAN = true;
		}

		if ($cd['admin'] >= ADMIN_GM)
		{
			$tpl->GM = $GM = true;
		}

		if ($cd['admin'] >= ADMIN_HGM)
		{
			$tpl->HGM = $HGM = true;
		}
		
		if ($cd['guild'])
		{
			if (!isset($chardata_guilds[$cd['guild']]))
			{
				$chardata_guilds[$cd['guild']] = array(
					'leader' => false
				);
			}
			if ($cd['guild_rank'] <= 1)
			{
				$chardata_guilds[$cd['guild']]['leader'] = true;
			}
		}
	}
}
else
{
	$chardata = array();
}

$tpl->numchars = $numchars = count($chardata);
$tpl->userdata = $sess->userdata = $userdata;
$tpl->chardata_guilds = $chardata_guilds;

function trans_form($buffer)
{
	global $csrf;
	$buffer = str_replace('</form>','<input type="hidden" name="csrf" value="'.$csrf.'">'."\n".'</form>', $buffer);
	return $buffer;
}

ob_start('trans_form',0);

function generate_pagination($pages, $page, $prefix = '')
{
	if (strpos($prefix, '?') === false)
	{
		$prefix .= '?';
	}
	else
	{
		$prefix .= '&';
	}
	$ret = "<div class=\"pagination\">";
	if ($page == 1)
	{
		$ret .= "&lt;&lt; ";
	}
	else
	{
		$ret .= "<a href=\"{$prefix}page=".($page-1)."\">&lt;&lt;</a> ";
	}
	$elip = false;
	for ($i = 1; $i <= $pages; ++$i)
	{
		if ($pages < 15 || abs($i - $page) < 3 || abs($i - $pages) < 2 || abs($i - 1) < 2)
		{
			if ($i == $page)
			{
				$ret .= "<span class=\"current\">$i</span> ";
			}
			else
			{
				$ret .= "<a href=\"{$prefix}page=$i\">$i</a> ";
			}
			$elip = true;
		}
		else
		{
			if ($elip)
			{
				$ret .= "... ";
				$elip = false;
			}
		}
	}
	
	if ($page == $pages)
	{
		$ret .= "&gt;&gt;";
	}
	else
	{
		$ret .= "<a href=\"{$prefix}page=".($page+1)."\">&gt;&gt;</a>";
	}
	
	$ret .= "</div>";

	return $ret;
}

function unserialize_inventory($str)
{
global $eoserv_items;
	$items = explode(';', $str);
	array_pop($items);

	foreach ($items as &$item)
	{
		$xitem = explode(',', $item);
		$getitem = $eoserv_items->Get($xitem[0]);
		
		if ($getitem)
		$item = array(
			'id' => (int)$xitem[0],
			'name' => $getitem->name,
			'amount' => isset($xitem[1]) ? $xitem[1] : '99999ERROR',
			'valid' => true
		);
	}
	unset($item);
	
	return $items;
}

function unserialize_paperdoll($str)
{
global $eoserv_items;
	$items = explode(',', $str);
	array_pop($items);
	
	if (count($items) != 15)
	{
		$items = array_fill(0, 15, 0);
	}

	foreach ($items as &$item)
	{
		$item = array(
			'id' => (int)$item,
			'slot' => EIFReader::TypeString($eoserv_items->Get($item)->type),
			'name' => $eoserv_items->Get($item)->name
		);
	}
	unset($item);

	return $items;
}

function unserialize_guildranks($str)
{
global $eoserv_items;
	$ranks = explode(',', $str);
	array_pop($ranks);
	
	if (count($ranks) != 9)
	{
		$ranks = array_fill(0, 9, 0);
	}

	return $ranks;
}

function unserialize_spells($str)
{
global $eoserv_spells;
	
	$spells = explode(';', $str);
	array_pop($spells);

	foreach ($spells as &$spell)
	{
		$xspell = explode(',', $spell);
		$spell = array(
			'id' => (int)$xspell[0],
			'name' => $eoserv_spells->Get($xspell[0])->name,
			'level' => isset($xspell[1]) ? $xspell[1] : '99999ERROR'
		);
	}
	unset($spell);
	
	return $spells;
	//return array();
}

function unserialize_quests($str)
{
global $eoserv_quests;
	
	$quests = explode(';', $str);
	array_pop($quests);

	foreach ($quests as &$quest)
	{
		$xquest = explode(',', $quest);
		$quest = array(
			'id' => (int)$xquest[0],
			//'name' => $eoserv_spells->Get($xquest[0])->name,
			'state' => isset($xquest[1]) ? $xquest[1] : '99999ERROR'
		);
	}
	unset($quest);
	
	return $quests;
	//return array();
}

function unserialize_kills($str)
{
global $eoserv_npcs;
	$npcs = explode(';', $str);
	array_pop($npcs);

	foreach ($npcs as &$npc)
	{
		$xnpc = explode(',', $npc);
		$npc = array(
			'id' => (int)$xnpc[0],
			'name' => $eoserv_npcs->Get($xnpc[0])->name,
			'amount' => isset($xnpc[1]) ? $xnpc[1] : '99999ERROR'
		);
	}
	unset($npc);
	
	return $npcs;
}

function read_ini($inifile)
{
	$lines = file($inifile, FILE_SKIP_EMPTY_LINES | FILE_IGNORE_NEW_LINES);
	$config = array();
	
	foreach ($lines as $line)
	{
		$line = trim($line);

		if (strlen($line) == 0 || $line[0] == '#')
			continue;

		$parts = explode('=', $line, 2);
		
		$config[rtrim($parts[0])] = ltrim($parts[1]);
	}
	
	return $config;
}

function load_shops($inifile)
{
	$ini = read_ini($inifile);
	$shops = array();
	
	foreach ($ini as $k => $v)
	{
		$k = explode('.', $k);
		if (!isset($shops[$k[0]])) $shops[$k[0]] = array();
		$shops[$k[0]][$k[1]] = array();
		
		$v = explode(',', $v);
		
		for ($i = 0; $i < count($v); )
		{
			switch ($k[1])
			{
				case 'name':
					$shops[$k[0]][$k[1]] = trim(implode(',', $v));
					$i += count($v);
					break;

				case 'trade':
					$item = trim($v[$i++]);
					$sell = trim($v[$i++]);
					$buy = trim($v[$i++]);
					$shops[$k[0]][$k[1]][$item] = array('sell' => $sell, 'buy' => $buy);
					break;
				
				case 'craft':
					$item = trim($v[$i++]);

					$shops[$k[0]][$k[1]][$item] = array(
						trim($v[$i++]) => trim($v[$i++]),
						trim($v[$i++]) => trim($v[$i++]),
						trim($v[$i++]) => trim($v[$i++]),
						trim($v[$i++]) => trim($v[$i++])
					);
					
					unset($shops[$k[0]][$k[1]][$item][0]);

					break;
				
				default:
					break 2;
			}
		}
	}
	
	return $shops;
}

function load_quests($inifile)
{
	$ini = read_ini($inifile);
	$quests = array();
	
	foreach ($ini as $k => $v)
	{
		$k = explode('.', $k);
		if (!isset($quests[$k[0]])) $quests[$k[0]] = array();
		$quests[$k[0]][$k[1]] = array();
		
		$v = explode(',', $v);
		
		for ($i = 0; $i < count($v); )
		{
			switch ($k[1])
			{
				case 'name':
					$quests[$k[0]][$k[1]] = trim(implode(',', $v));
					$i += count($v);
					break;

				case 'reward':
					$item = trim($v[$i++]);
					$amount = trim($v[$i++]);
					$quests[$k[0]][$k[1]][$item] = array('amount' >= $amount);
					break;
				
				case 'exp':
					$quests[$k[0]][$k[1]] = trim(implode(',', $v));
					$i += count($v);

					break;
				
				default:
					break 2;
			}
		}
	}
	
	return $quests;
}

function load_drops($inifile)
{
	$ini = read_ini($inifile);
	$drops = array();
	
	foreach ($ini as $k => $v)
	{
		$shops[$k] = array();
		
		$v = explode(',', $v);
		
		if (count($v) <= 1)
			continue;
		
		for ($i = 0; $i < count($v); )
		{
			$drops[$k][$i / 4] = array(
				'item' => trim($v[$i++]),
				'min' => trim($v[$i++]),
				'max' => trim($v[$i++]),
				'pct' => trim($v[$i++])
			);
		}
	}
	
	return $drops;
}

function load_dialog($inifile)
{
	$ini = read_ini($inifile);
	$dialog = array();
	
	foreach ($ini as $k => $v)
	{
		$k = explode('.', $k);
		if (!isset($dialog[$k[0]])) $dialog[$k[0]] = array();

		$dialog[$k[0]][$k[1]] = $v;
	}
	
	return $dialog;
}

function load_skills($inifile)
{
	$ini = read_ini($inifile);
	$skills = array();
	
	foreach ($ini as $k => $v)
	{
		$k = explode('.', $k);
		if (!isset($skills[$k[0]])) $skills[$k[0]] = array();
		$skills[$k[0]][$k[1]] = array();
		
		$v = explode(',', $v);
		
		for ($i = 0; $i < count($v); )
		{
			switch ($k[1])
			{
				case 'name':
					$skills[$k[0]][$k[1]] = trim(implode(',', $v));
					$i += count($v);
					break;

				case 'learn':
					$spell = trim($v[$i++]);
					$cost = trim($v[$i++]);
					$levelreq = trim($v[$i++]);
					$classreq = trim($v[$i++]);
					$spellreq = array(trim($v[$i++]), trim($v[$i++]), trim($v[$i++]), trim($v[$i++]));
					$strreq = trim($v[$i++]);
					$intreq = trim($v[$i++]);
					$wisreq = trim($v[$i++]);
					$agireq = trim($v[$i++]);
					$conreq = trim($v[$i++]);
					$chareq = trim($v[$i++]);
					
					foreach ($spellreq as $spell_idx => $spellid)
					{
						if ($spellid == 0)
							unset($spellreq[$spell_idx]);
					}

					$skills[$k[0]][$k[1]][$spell] = array(
						'cost' => $cost,
						'levelreq' => $levelreq,
						'classreq' => $classreq,
						'spellreq' => $spellreq,
						'strreq' => $strreq,
						'intreq' => $intreq,
						'wisreq' => $wisreq,
						'agireq' => $agireq,
						'conreq' => $conreq,
						'chareq' => $chareq
					);

					break;

				default:
					break 2;
			}
		}
	}
	
	return $skills;
}

function karma_str($karma)
{
	// NOTE: These values are unconfirmed guesses
	$table = array(
		0    => 'Demonic',
		250  => 'Doomed',
		500  => 'Cursed',
		750  => 'Evil',
		1000 => 'Neutral',
		1250 => 'Good',
		1500 => 'Blessed',
		1750 => 'Saint',
		2000 => 'Pure'
	);
	
	$last = $table[0];
	
	foreach ($table as $k => $v)
	{
		if ($karma < $k)
		{
			return $last;
		}
		$last = $v;
	}
	
	return $last;
}

function haircolor_str($color)
{
	$table = array(
		'Brown',
		'Green',
		'Pink',
		'Red',
		'Yellow',
		'Blue',
		'Purple',
		'Luna',
		'White',
		'Black'
	);
	
	return isset($table[$color])?$table[$color]:'Unknown';
}

function race_str($race)
{
	$table = array(
		'Human (White)',
		'Human (Yellow)',
		'Human (Tan)',
		'Orc',
		'Skeleton',
		'Panda',
		'Fish',
		'Fish (alternate)',
		'Lizard',
		'Squirrel',
		'Bird',
		'Devil'
	);
	
	return isset($table[$race])?$table[$race]:'Unknown';
}

function adminrank_str($admin)
{
	$table = array(
		'Player',
		'Light Guide',
		'Guardian',
		'Game Master',
		'High Game Master'
	);
	
	return isset($table[$admin])?$table[$admin]:'Unknown';
}

function class_str($class)
{
global $eoserv_classes;
	if ($class == 0)
	{
		return '-';
	}
	
	return $eoserv_classes->Get($class)->name;
}

function guildrank_str($ranks, $rank)
{
	if ($rank == 0) $rank = 1;
	return isset($ranks[$rank-1])?$ranks[$rank-1]:'Unknown';
}
