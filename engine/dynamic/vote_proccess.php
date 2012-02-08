<?php
/************************************************************************
*												engine/dynamic/vote_process.php
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

// Invalid Access
if (!isset($_GET['id'])) die();

$vote_every_hrs = 12; //vote every 12 hrs

define('PATHROOT', '../../');
include PATHROOT.'engine/init.php';

$id = preg_replace("/[^0-9]/", "", $_GET['id']);

// Unknown $id
if ($id == "") die();

// Unknwon $id
if (!isset($config['vote_link_'.$id])) die();

// No $user
if (!isset($user)) die();

// Delete expired data
$db->query("DELETE FROM ".$config['engine_web_db'].".wwc2_vote_data WHERE timevoted <= '".@date("U")."'");

/**
* Script needs to do following:
* 	check if user hasn't voted within last 12 hours for specific ID
* 	at end print nothing (link image will dissapear when user click on it)
* 	or print "X", delete old data so there is no stacking.
*/
$sql1 = $db->query("SELECT * FROM ".$config['engine_web_db'].".wwc2_vote_data WHERE siteid = '".$id."' AND timevoted>='".@date("U")."' AND (userid='".$user->userinfo['guid']."' OR voteip='".$_SERVER['REMOTE_ADDR']."') LIMIT 1")or die($db->getLastError());
if ($db->numRows() == 0)
{
	//first check if vote_link_$id exists in config
	$db->query("INSERT INTO ".$config['engine_web_db'].".wwc2_vote_data (userid,siteid,timevoted,voteip) VALUES ('".$user->userinfo['guid']."','".$id."','".(@date("U")+($vote_every_hrs*60*60))."','".$_SERVER['REMOTE_ADDR']."')")or die($db->getLastError());
	$db->updateUserField($user->username, 'vp', ($user->userinfo['vp']+1));
}
echo "<script type='text/javascript'>window.location = './index.php';</script>";
?>