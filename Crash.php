<?php 

$pagetitle = 'Server reboot';   
require 'common.php';   

if (!$GUARDIAN) 
{     
	$tpl->message = 'You must be a Game Master to view this page.';     
	$tpl->Execute(null);     
	exit; 
}   

set_time_limit(0);

if (!@fsockopen('127.0.0.1', 8078, $errno, $errstr, 20.0)) 
{     
	passthru('taskkill /F /IM:eoserv-main.exe');     
	$tpl->message = "Server rebooted!<br>"; 
} 
else
{     
	$tpl->message = "Server isn't offline yet!<br>"; 
}   
$tpl->Execute('reboot'); 
