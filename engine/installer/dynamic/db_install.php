<?php

###################################################################
# This file is a part of OpenWoW CMS by www.openwow.com
#
# Project Owner : OpenWoW CMS (http://www.openwow.com)
# Copyright : (c) www.openwow.com, 2010
# Credits : Based on work done by AXE and Maverfax
# License : GPLv3
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

function doQuery($sql)
{
	$result = mysql_query($sql);
	return $result;
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
$errorCount = 0;

// Check all database connections!
if (!isset($wwcms['db_test']) || $wwcms['db_test'] == false)
{
	echo "<fieldset><legend>Testing Database Settings</legend>";
	$char_counter = 0; $char_db = ''; $raorsoap_port = '';
	if (isset($wwcms['char_db']))
	{
		foreach ($wwcms['char_db'] as $key => $sess_chardb)
		{
			if($sess_chardb<>'')
			{
				$validHost = false;
				// Host, User, Pass - Only if the host is set
				if (isset($wwcms['char_host']) && trim($wwcms['char_host'][$char_counter])!='' )
				{
					if ($con = @mysql_connect($wwcms['char_host'][$char_counter], $wwcms['char_dbuser'][$char_counter], $wwcms['char_dbpass'][$char_counter]))
					{
						mysql_close($con);
						$validHost = true;
					}
					else
					{
						$errorCount++;
						echo '<span id="db_error'.$errorCount.'"><br>&nbsp;&nbsp;<font color="red">'.$sess_chardb.' - '.$installer_lang['Connection Failed'].'</font> ('.mysql_error().")";
						echo "<br>&nbsp;&nbsp;&nbsp;<span class='innerlinks'><a href='javascript:void()' onclick='db_ignore($errorCount);return false'>".$installer_lang['Ignore Message']."</a></span></span>";
						$char_counter++;
						continue;
					}
				}
				$char_db .= $sess_chardb.'|'.$wwcms['char_port'][$char_counter];

				if ($validHost)
					$char_db .= '|'.$wwcms['char_host'][$char_counter].'|'.$wwcms['char_dbuser'][$char_counter].'|'.$wwcms['char_dbpass'][$char_counter];
				$char_db .= ";";

				// RA PORT or SOAP PORT
				if ($wwcms['core']=='Trinity' or $wwcms['core']=='MaNGOS' or $wwcms['core']=='Trinitysoap')
					$raorsoap_port.= $wwcms['char_rasoap'][$char_counter].'|';

				// Realm Names
				$_SESSION['wwcmsv2install']['char_names2'][$char_counter] = $wwcms['char_names'][$char_counter];
			}
			$char_counter++;
		}
		$_SESSION['wwcmsv2install']['char_db_string'] = $char_db;
	}
	echo "</fieldset>";
	if ($errorCount > 0)
	{
		echo '<div id="errorcounts">';
		echo '<br><font color="red"><var id="db_error_count">'.$errorCount.'</var> ' . $installer_lang['Errors Found'].'</font>';
		echo '<br><font color="#666666"><var id="db_error_ignore">0</var> ' . $installer_lang['Errors Ignored'].'</font>';
		echo '</div>';
	}
	$_SESSION['wwcmsv2install']['db_test'] = true;
	return;
}

if ($step <= 4)
{
	if ($con = mysql_connect($wwcms['db_host'], $wwcms['db_user'], $wwcms['db_pass']))
	{
		if ($step == 1)
		{
			echo "<fieldset><legend>Creating Database</legend>";
			$result = doQuery("CREATE DATABASE `" . $wwcms['web_db'] . "`");
			if ($result)
			{
				echo "<br>".$installer_lang['Create'] . " DB " . $_SESSION['wwcmsv2install']['web_db'];
				++$_SESSION['wwcmsv2install']['sqlstep']; // Skip step 2 if we created the database
			}
			else
				echo "<br>".$installer_lang['found'] . " DB " . $_SESSION['wwcmsv2install']['web_db'];
			echo "</fieldset>";
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
			"`wwc2_template`"=>"CREATE TABLE `wwc2_template` (`templateid` int(10) unsigned NOT NULL auto_increment, `styleid` smallint(6) NOT NULL default '0', `title` varchar(100) NOT NULL default '',`template` mediumtext, `template_un` mediumtext, `templatetype` enum('template','css','other') NOT NULL default 'template', `dateline` int(10) unsigned NOT NULL default '0',`username` varchar(100) NOT NULL default '', `version` varchar(30) NOT NULL default '', PRIMARY KEY (`templateid`), KEY `title` (`title`,`styleid`,`templatetype`)) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;",
			"`wwc2_users_more`"=>"CREATE TABLE `wwc2_users_more` (`id` bigint(20) NOT NULL AUTO_INCREMENT, `acc_login` varchar(55) COLLATE latin1_general_ci NOT NULL, `vp` bigint(55) NOT NULL DEFAULT '0',`userid` varchar(32) COLLATE latin1_general_ci DEFAULT NULL, `question` varchar(100) COLLATE latin1_general_ci DEFAULT NULL, `answer` varchar(100) COLLATE latin1_general_ci NOT NULL DEFAULT '',`dp` bigint(55) NOT NULL DEFAULT '0', `gmlevel` varchar(11) COLLATE latin1_general_ci NOT NULL DEFAULT '', `avatar` varchar(100) COLLATE latin1_general_ci NOT NULL DEFAULT '',PRIMARY KEY (`id`,`acc_login`)) ENGINE=MyISAM AUTO_INCREMENT=112 DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;",
			"`wwc2_vote_data`"=>"CREATE TABLE `wwc2_vote_data` (`id` bigint(21) NOT NULL AUTO_INCREMENT, `userid` bigint(21) DEFAULT NULL, `siteid` bigint(21) NOT NULL,`timevoted` bigint(21) NOT NULL, `voteip` varchar(21) COLLATE latin1_general_ci DEFAULT NULL, PRIMARY KEY (`id`)) ENGINE=MyISAM AUTO_INCREMENT=170 DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;"
			);

		// Empty Database
		if ($step == 2)
		{
			echo "<fieldset><legend>Clearing Database</legend>";
			foreach ($tables as $table => $createSql)
			{
				if(doQuery("DROP TABLE IF EXISTS $table"))
					echo "<br>".$installer_lang['Delete']." ".$table;
			}
			echo "</fieldset>";
		}

		// Create Tables
		if ($step == 3)
		{
			echo "<fieldset><legend>Creating Tables</legend>";
			foreach ($tables as $table => $createSql)
			{
				$success = doQuery($createSql);
				if ($success)
				{
					echo "<br>".$installer_lang['Create']." ".$table;
				}
				else
				{
					echo "<br>".$installer_lang['Failed to create tables']." ".$table;
				}
			}
			echo "</fieldset>";
		}

		if ($step == 4)
		{
			$insertQueries = array(
				"`wwc2_config`" => "
('engine_lang','$lang','','1',''),
('engine_core','".$wwcms['core']."','','1',''),
('engine_logon_db','".$wwcms['logon_db']."','','1',''),
('engine_styleid','1','Change style ID to change style.','1','')"
				);
			foreach ($insertQueries as $table => $insert)
			{
				$success = doQuery("INSERT INTO $table VALUES $insert");
				if ($success)
					echo "<br>".$installer_lang['Inserting data to']." ".$table;
				else
					echo "<br>".sprintf($installer_lang['Inserting data failed'], $table);
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
	echo '<br><br><input name="next" type="submit" value="'.$installer_lang['Next Step'].' (6/8)"></form>';
	echo '<script type="text/javascript">$("#db_install").remove();</script>';
	$_SESSION['wwcmsv2install']['sqlstep'] = 1;
	$_SESSION['wwcmsv2install']['db_test'] = false;
	return;
}
// Next Step
++$_SESSION['wwcmsv2install']['sqlstep'];
return;
?>
<script type="text/javascript">setTimeout('db_install()', '5000');</script>