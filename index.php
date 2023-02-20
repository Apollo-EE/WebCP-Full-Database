<?php

$pagetitle = 'News Headlines';

require 'common.php';

$count = webcp_db_fetchall('SELECT COUNT(1) as count FROM news');
$count = $count[0]['count'];

if ($count == 0)
{
	$tpl->message = "There are currently no news headlines.";
 	$tpl->Execute(null);
 	exit;
}

$page = isset($_GET['page']) ? $_GET['page'] : 1;
$pages = ceil($count / $perpage);

if ($page < 1 || $page > $pages)
{
	$page = max(min($page, $pages), 1);
}

$start = ($page-1) * $perpage;

$news = webcp_db_fetchall("SELECT title, body, time FROM news ORDER BY time DESC LIMIT ?,?", $start, $perpage);
foreach ($news as &$new)
{
	$new['postdate'] = date('F j, Y', $new['time']);
}

if (empty($news))
{
	$tpl->messages = "There are currently no news headlines.";
	$tpl->Execute(null);
	exit;
}
$pagination = generate_pagination($pages, $page);
$tpl->page = $page;
$tpl->pages = $pages;
$tpl->pagination = $pagination;
$tpl->perpage = $perpage;
$tpl->showing = count($news);
$tpl->start = $start+1;
$tpl->end = min($start+$perpage, $count);
$tpl->count = $count;

$tpl->news = $news;

$tpl->Execute('index');