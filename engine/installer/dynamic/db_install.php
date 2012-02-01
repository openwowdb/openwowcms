<?php

###################################################################
# This file is a part of OpenWoW CMS by www.openwow.com
#
#   Project Owner    : OpenWoW CMS (http://www.openwow.com)
#   Copyright        : (c) www.openwow.com, 2010
#   Credits          : Based on work done by AXE and Maverfax
#   License          : GPLv3
##################################################################

session_start();
error_reporting(~E_NOTICE);
$fail = false;
if (!isset($_SESSION['wwcmsv2install']))
{
	echo 'No access.';
	session_destroy();
	exit;
}

/**
* Removes all characters that are not alphabetical nor numerical
*
* @param string
* @return string
*/
function sanitize($string = '')
{
	return preg_replace('/[^a-zA-Z0-9]/', '', $string);
}

/**
* Determines wether a language is valid or not
*
* @param string
* @return boolean
*/
function is_valid_lang($language = '')
{
	if(!empty($language))
	{
		if(file_exists('./engine/lang/' . $language . '/installer.php'))
		{
			return TRUE;
		}
	}

	return FALSE;
}

function _htmlspecialchars($str)
{
	$str = preg_replace('/&(?!#[0-9]+;)/s', '&amp;', $str);
	$str = str_replace(array('<', '>', '"'), array('&lt;', '&gt;', '&quot;'), $str);
	return $str;
}

function doQuery($sql, $onResult = null, $onFailed = null, $output = true)
{
	if ($result = mysql_query($sql))
	{
		$onResult ?  $onResult() : '';
		if ($output)
			echo "<br>".$sql;
		return $result;
	}
	$onFailed ? $onFailed() : '';
}

define('PATHROOT', '../../../');

// By default, Web-WoW will run using English
$lang = 'English';

// Do we have a requested language through URL or POST?
$requested_lang = sanitize(
	isset($_SESSION['wwcmsv2install']['lang']) ? $_SESSION['wwcmsv2install']['lang'] : 
	(isset($_POST['lang']) ? $_POST['lang'] : $lang)
	);

if (is_valid_lang($requested_lang))
{
	$lang = $requested_lang;
}

// Load the language file
require (PATHROOT.'engine/lang/' . strtolower($lang) . '/installer.php');

// Process
$wwcms = $_SESSION['wwcmsv2install'];
$step = (isset($wwcms['sqlstep']) && strlen($wwcms['sqlstep']) == 1 ? preg_replace( "/[^0-9]/", "", $wwcms['sqlstep'] ) : '');
if ($step == '')
{
	$step = 1;
	$_SESSION['wwcmsv2install']['sqlstep'] = 1;
}

if ($step <= 3)
{
	if ($con = mysql_connect($wwcms['db_host'], $wwcms['db_user'], $wwcms['db_pass']))
	{
		// Create Database
		if ($step == 1)
		{
			doQuery("CREATE DATABASE `" . $wwcms['web_db'] . "`", 
				function(){++$_SESSION['wwcmsv2install']['sqlstep'];},	// Skip step 2 if we created the database
				function(){global $installer_lang;echo "<br>".$installer_lang['found'] . " DB " . $_SESSION['wwcmsv2install']['web_db'];});
		}
		else
			mysql_select_db($wwcms['web_db']);
		
		$tables = array(
			"`wwc2_active_guests`"=>"CREATE TABLE `wwc2_active_guests` (`ip` varchar(15) NOT NULL,`timestamp` int(11) unsigned NOT NULL,PRIMARY KEY (`ip`)) ENGINE=MyISAM DEFAULT CHARSET=latin1;",
			"`wwc2_active_users`"=>"CREATE TABLE `wwc2_active_users` (`username` varchar(30) NOT NULL, `timestamp` int(11) unsigned NOT NULL, PRIMARY KEY (`username`)) ENGINE=MyISAM DEFAULT CHARSET=latin1;",
			"`wwc2_banned_users`"=>"CREATE TABLE `wwc2_banned_users` (`username` varchar(30) NOT NULL, `timestamp` int(11) unsigned NOT NULL, PRIMARY KEY (`username`)) ENGINE=MyISAM DEFAULT CHARSET=latin1;",
			"`wwc2_config`"=>"CREATE TABLE `wwc2_config` (`conf_name` varchar(255) COLLATE latin1_general_ci NOT NULL DEFAULT '', `conf_value` text COLLATE latin1_general_ci, `conf_descr` text COLLATE latin1_general_ci, `conf_stickied` int(1) NOT NULL DEFAULT '0', `conf_dropdown` text COLLATE latin1_general_ci, PRIMARY KEY (`conf_name`)) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;",
			"`wwc2_links`"=>"CREATE TABLE `wwc2_links` (`id` int(10) NOT NULL AUTO_INCREMENT, `linktitle` varchar(255) NOT NULL DEFAULT 'notitle', `linkurl` varchar(255) NOT NULL DEFAULT 'http://', `linkdescr` varchar(255) DEFAULT '', `linkgrup` varchar(100) NOT NULL DEFAULT '0', `linkorder` int(11) NOT NULL DEFAULT '0', `linkprems` int(10) NOT NULL DEFAULT '0',PRIMARY KEY (`id`)) ENGINE=MyISAM AUTO_INCREMENT=441 DEFAULT CHARSET=latin1;",
			"`wwc2_news`"=>"CREATE TABLE `wwc2_news` (`id` bigint(20) NOT NULL AUTO_INCREMENT, `title` varchar(255) COLLATE latin1_general_ci NOT NULL, `content` longtext COLLATE latin1_general_ci NOT NULL,`iconid` int(11) NOT NULL DEFAULT '0', `timepost` varchar(100) COLLATE latin1_general_ci NOT NULL, `stickied` int(1) NOT NULL DEFAULT '0' COMMENT '0 or 1',`hidden` int(1) NOT NULL DEFAULT '0' COMMENT '0 or 1', `author` varchar(50) COLLATE latin1_general_ci NOT NULL, PRIMARY KEY (`id`)) ENGINE=MyISAM AUTO_INCREMENT=88 DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;",
			"`wwc2_news_c`"=>"CREATE TABLE `wwc2_news_c` (`id` bigint(20) NOT NULL AUTO_INCREMENT, `poster` varchar(255) COLLATE latin1_general_ci NOT NULL, `content` text COLLATE latin1_general_ci NOT NULL,`newsid` int(11) NOT NULL, `timepost` varchar(100) COLLATE latin1_general_ci NOT NULL, `datepost` varchar(100) COLLATE latin1_general_ci NOT NULL,PRIMARY KEY (`id`)) ENGINE=MyISAM AUTO_INCREMENT=145 DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;",
			"`wwc2_template`"=>"CREATE TABLE `wwc2_template` (`templateid` int(10) unsigned NOT NULL auto_increment, `styleid` smallint(6) NOT NULL default '0', `title` varchar(100) NOT NULL default '',`template` mediumtext, `template_un` mediumtext, `templatetype` enum('template','css','other') NOT NULL default 'template', `dateline` int(10) unsigned NOT NULL default '0',`username` varchar(100) NOT NULL default '', `version` varchar(30) NOT NULL default '', PRIMARY KEY  (`templateid`), KEY `title` (`title`,`styleid`,`templatetype`)) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;",
			"`wwc2_users_more`"=>"CREATE TABLE `wwc2_users_more` (`id` bigint(20) NOT NULL AUTO_INCREMENT, `acc_login` varchar(55) COLLATE latin1_general_ci NOT NULL, `vp` bigint(55) NOT NULL DEFAULT '0',`userid` varchar(32) COLLATE latin1_general_ci DEFAULT NULL, `question` varchar(100) COLLATE latin1_general_ci DEFAULT NULL, `answer` varchar(100) COLLATE latin1_general_ci NOT NULL  DEFAULT '',`dp` bigint(55) NOT NULL DEFAULT '0', `gmlevel` varchar(11) COLLATE latin1_general_ci NOT NULL  DEFAULT '', `avatar` varchar(100) COLLATE latin1_general_ci NOT NULL  DEFAULT '',PRIMARY KEY (`id`,`acc_login`)) ENGINE=MyISAM AUTO_INCREMENT=112 DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;",
			"`wwc2_vote_data`"=>"CREATE TABLE `wwc2_vote_data` (`id` bigint(21) NOT NULL AUTO_INCREMENT, `userid` bigint(21) DEFAULT NULL, `siteid` bigint(21) NOT NULL,`timevoted` bigint(21) NOT NULL, `voteip` varchar(21) COLLATE latin1_general_ci DEFAULT NULL, PRIMARY KEY (`id`)) ENGINE=MyISAM AUTO_INCREMENT=170 DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;"
		);
		
		// Empty Database
		if ($step == 2)
		{
			function deleteTable($table){ 
				global $installer_lang;
				echo "<br>".$installer_lang['Delete']." ".$table;
			};
			echo "<br>";
			foreach ($tables as $table => $createSql)
			{
				if(doQuery("DROP TABLE IF EXISTS $table", null, null, false))
					deleteTable($table);
			}
		}
		
		// Create Tables
		if ($step == 3)
		{
			function createTable($table, $success = true){
				global $installer_lang;
				if ($success)
				{
					echo "<br>".$installer_lang['Create']." ".$table;
				}
				else
				{
					echo "<br>".$installer_lang['Failed to create tables']." ".$table;
				}
			}
			echo "<br>";
			foreach ($tables as $table => $createSql)
			{
				$success = doQuery($createSql, null, null, false);
				createTable($table, $success);
			}
		}
		
		mysql_close($con);
	}
	else
	{
		echo '&nbsp;&nbsp;<font color="red">'.$installer_lang['Connection Failed'].'</font> ('.mysql_error().")";
		return;
	}
}
else
{
	echo "<br> end of process";
	$_SESSION['wwcmsv2install']['sqlstep'] = 1;
	return;
}
// Next Step
++$_SESSION['wwcmsv2install']['sqlstep'];
?>
<script type="text/javascript">setTimeout('db_install()', '5000');</script>