<?php
/************************************************************************
*											 engine/dynamic/news_comments.php
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
/**
* Configuration:
*/
$comments_per_page=5;
/**
* End Configuration
*/

define('PATHROOT', '../../');
include (PATHROOT.'engine/init.php');
include_once(PATHROOT."engine/func/parser.php");
include_once(PATHROOT."engine/func/nicetime.php");

function load_comments($newsid, &$start, $endCount)
{
	global $db, $config, $user;
	$comments_count_sql=$db->query("SELECT count(*) FROM ".$config['engine_web_db'].".wwc2_news_c WHERE newsid='".$newsid."'") or die($db->getLastError());
	$comments_count = $db->getRow($comments_count_sql);
	if ($comments_count[0] == 0) return 0;

	$comments_sql=$db->query("SELECT * FROM ".$config['engine_web_db'].".wwc2_news_c WHERE newsid='".$newsid."' ORDER BY id DESC LIMIT ".$start." , ".$endCount) or die($db->getLastError());
	if ($db->numRows($comments_sql) == 0) return 0; // no comments!
	//if yes comments
	while ($comments = $db->getRow($comments_sql))
	{
		//get poster ID
		$userinfo = $user->getUserInfo($comments['poster']);
		if (!filehandler::isExists($userinfo['avatar'].'.gif', 'engine/res/avatars'))
			$avatarurl='./engine/res/avatars/default.gif';
		else
			$avatarurl='./engine/res/avatars/'.$userinfo['avatar'].'.gif';
		echo '<div id="singlecomment'.$comments['id'].'">';
		if($user->logged_in && (strtoupper($comments['poster']) == strtoupper($user->username) or $user->isAdmin() or $user->isGM()))
			echo '<span style="float:right"><a href="javascript:void(0);" onclick="remove_comment('.$newsid.','.$comments['id'].');$(\'#singlecomment'.$comments['id'].'\').remove();">[x]</a></span>';

		echo '<table width="100%" border="0" cellspacing="3px">
		<tr>
		<td width="64px"><div class="avatar"><img src="'.$avatarurl.'" /></div></td>
		<td><div class="comments_poster"><a href="index.php?page=profile&id='.$userinfo['guid'].'">'.$comments['poster'].'</a> ('.nicetime($comments['timepost']).')</div><div class="comments_body">'.do_bbcode($comments['content']).'</div></td>
		</tr>
		</table></div>';
	}

	$start = $start + $endCount;
	// Returning remaining comments
	if ($comments_count[0] < $start)
		return 0;
	return $comments_count[0] - $start;
}

if ($_GET['newsid'])
{
	$newsid = preg_replace("/[^0-9]/", "", $_GET['newsid']);
	$start = isset($_GET['start']) ? preg_replace("/[^0-9]/", "", $_GET['start']) : 0;
	if ($start == '') $start=0;

	if (isset($_POST['comment']))
	{
		if($user->logged_in && trim($_POST['comment'])<>'')
		{
			$db->query("INSERT INTO ".$config['engine_web_db'].".wwc2_news_c (poster,content,newsid,timepost,datepost) VALUES ('".$db->escape($user->username)."','".$db->escape($_POST['comment'])."','".$newsid."','".@date("U")."','')") or die($db->getLastError());
			$insertid = $db->insertId();
			$comments_sql=$db->query("SELECT * FROM ".$config['engine_web_db'].".wwc2_news_c WHERE newsid='".$newsid."' AND id='".$insertid."' ORDER by id DESC LIMIT 1") or die($db->getLastError());
			$comments = $db->getRow($comments_sql);
			//get poster ID
			$userinfo = $user->getUserInfo($comments['poster']);
			if (!filehandler::isExists($userinfo['avatar'].'.gif', 'engine/res/avatars'))
				$avatarurl='./engine/res/avatars/default.gif';
			else
				$avatarurl='./engine/res/avatars/'.$userinfo['avatar'].'.gif';
			echo '<div id="singlecomment'.$comments['id'].'">';
			if($user->logged_in && (strtoupper($comments['poster']) == strtoupper($user->username) or $user->isAdmin() or $user->isGM()))
				echo '<span style="float:right"><a href="javascript:void(0);" onclick="remove_comment('.$newsid.','.$comments['id'].');$(\'#singlecomment'.$comments['id'].'\').remove();">[x]</a></span>';

			echo '<table width="100%" border="0" cellspacing="3px">
			<tr>
			<td width="64px"><div class="avatar"><img src="'.$avatarurl.'" /></div></td>
			<td><div class="comments_poster"><a href="index.php?page=profile&id='.$userinfo['guid'].'">'.$comments['poster'].'</a> ('.nicetime($comments['timepost']).')</div><div class="comments_body">'.do_bbcode($comments['content']).'</div></td>
			</tr>
			</table></div>';
		}
		return;
	}
	if(isset($_GET['delete']))
	{
		if (!$user->logged_in) exit;
		//we have to get comment with his id and check poster.... oh god help us all...
		$comments_sql = $db->query("SELECT * FROM ".$config['engine_web_db'].".wwc2_news_c WHERE id='".$newsid."' LIMIT 1") or die($db->getLastError());
		$comments = $db->getRow($comments_sql);
		if(strtoupper($comments['poster']) == strtoupper($user->username) or $user->isAdmin() or $user->isGM()) {
			$db->query("DELETE FROM ".$config['engine_web_db'].".wwc2_news_c WHERE id='".$newsid."' LIMIT 1") or die($db->getLastError());
		}
		return;
	}
	if (load_comments($newsid, $start, $comments_per_page) == 0)
	{
		echo "<script type='text/javascript'>\$('#comments_more_".$newsid."').remove();</script>";
		return;
	}
}
echo '</div>';
if (!isset($_GET['nobox']) or !isset($_GET['nomore']))
	echo '<div id="comments_more_'.$newsid.'">
	<div class="more_comments">
	<a href="javascript:void(0);" id="more_comments'.$newsid.'" onclick="get_comments('.$newsid.','.$start.');">'.$lang['More comments'].'</a>
	</div>
	</div>';
?>