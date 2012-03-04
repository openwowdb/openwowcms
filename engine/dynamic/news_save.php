<?php
/************************************************************************
*													engine/dynamic/news_save.php
*                            -------------------
* 	 Copyright (C) 2011
*
* 	 This package is free software: you can redistribute it and/or modify
*    it under the terms of the GNU General Public License as published by
*    the Free Software Foundation, either version 3 of the License, or
*    (at your option) any later version.
*
*  	 This package is based on the work of the web-wow.net and openwow.com
* 	 team during 2007-2010.
*
* 	 Updated: $Date 2012/02/08 14:00 $
*
************************************************************************/

define('PATHROOT', '../../');
include (PATHROOT.'engine/init.php');
include_once(PATHROOT."engine/func/parser.php");
include_once(PATHROOT."engine/func/nicetime.php");
/* EXIT if no premission */
if (!$user->isAdmin()) exit;


if (isset($_GET['id']) && trim ($_GET['id'])<>'')
{
	$newsid = preg_replace( "/[^0-9]/", "", $_GET['id'] );
	if (isset($_GET['save']))
	{
		$db->query("UPDATE ".$config['engine_web_db'].".wwc2_news SET content='".$db->escape($_POST['message'])."' WHERE id='".$newsid."' LIMIT 1")or die($db->getLastError());
		echo parse_message($_POST['message']);
	}
	elseif(isset($_GET['gettitle']))
	{
		$sql1 = $db->query("SELECT title FROM ".$config['engine_web_db'].".wwc2_news WHERE id='".$newsid."' LIMIT 1")or die($db->getLastError());
		$sql2=$db->getRow($sql1);
		echo $sql2[0];
	}
	elseif(isset($_GET['getbody']))
	{
		$sql1 = $db->query("SELECT content FROM ".$config['engine_web_db'].".wwc2_news WHERE id='".$newsid."' LIMIT 1")or die($db->getLastError());
		$sql2=$db->getRow($sql1);
		echo $sql2[0];
	}
	elseif(isset($_GET['getbodyparsed']))
	{
		$sql1 = $db->query("SELECT content FROM ".$config['engine_web_db'].".wwc2_news WHERE id='".$newsid."' LIMIT 1")or die($db->getLastError());
		$sql2=$db->getRow($sql1);
		echo parse_message($sql2[0]);
	}
	exit;
}
else if(isset($_GET['new']))
{
	if (isset($_POST['title']) && isset($_POST['message']))
		$db->query("INSERT INTO ".$config['engine_web_db'].".wwc2_news (title,content,timepost,author) VALUES ('".$db->escape(htmlspecialchars($_POST['title']))."','".$db->escape($_POST['message'])."','".@date("U")."','".$user->username."')")or die($db->getLastError());
	return;
}
