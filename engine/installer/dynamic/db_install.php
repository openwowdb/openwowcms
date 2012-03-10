<?php
/************************************************************************
*											engine/installer/dynamic/db_install.php
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

session_start();
error_reporting(~E_NOTICE);
$fail = false;
if (!isset($_SESSION['wwcmsv2install']))
{
	echo 'No access.';
	session_destroy();
	exit;
}

define('PATHROOT', '../../../');
// Include common functions
include PATHROOT."engine/func/required.php";
include PATHROOT."library/library.php";

// By default, Web-WoW will run using English
$lang = 'English';

// Do we have a requested language through URL or POST?
$requested_lang = Html::sanitize(
	isset($_SESSION['wwcmsv2install']['lang']) ? $_SESSION['wwcmsv2install']['lang'] :
	(isset($_POST['lang']) ? $_POST['lang'] : $lang)
	);

if (Html::is_valid_lang($requested_lang, "installer")) $lang = $requested_lang;

// Load the language file
include PATHROOT.'engine/lang/' . strtolower($lang) . '/installer.php';

// Process
$wwcms = $_SESSION['wwcmsv2install'];
$step = (isset($wwcms['sqlstep']) && strlen($wwcms['sqlstep']) == 1 ? preg_replace( "/[^0-9]/", "", $wwcms['sqlstep'] ) : '');
if ($step == '')
{
	$step = 1;
	$_SESSION['wwcmsv2install']['sqlstep'] = 1;
}
$errorCount = 0;

// --------------------------------------------------------------------

/**
* MySQL Import File
*
* @access	public
* @param	string
* @return	boolean
*/
function mysql_import_file(&$con, $filename) {
	// Read the file
	$lines = filehandler::file($filename, "engine/installer/sql");

	if(!$lines)
	{
		$errmsg = "Could not open file $filename";
		return false;
	}

	// Run each line as a query
	foreach($lines as $query)
	{
		$query = trim($query);

		// Empty Line
		if($query == '')
			continue;

		// Make sure query ends with a ;
		if (!preg_match('/;$/', $query)) continue;

		// Query Failed ?
		if (!$con->query($query))
			echo "<strong>Query</strong> " . htmlspecialchars($query) . " <b>FAILED</b><br>REPORT: " . $con->getLastError() . "<br><br>";
	}

	return true;
}

// Check all database connections!
if (!isset($wwcms['db_test']) || $wwcms['db_test'] == false) {
	echo "<fieldset><legend>Initial Step</legend>";
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
					library::create_dblink($con, $wwcms['db_type']);
					if ($con->init($wwcms['char_host'][$char_counter], $wwcms['char_dbuser'][$char_counter], $wwcms['char_dbpass'][$char_counter]))
					{
						$con->close();
						$validHost = true;
					}
					else
					{
						$errorCount++;
						echo '<span id="db_error'.$errorCount.'"><br>&nbsp;&nbsp;<font color="red">'.$sess_chardb.' - '.$installer_lang['Connection Failed'].'</font> ('.$con->getLastError().")";
						echo "<br>&nbsp;&nbsp;&nbsp;<span class='innerlinks'><a href='javascript:void()' onclick='db_ignore($errorCount);return false'>".$installer_lang['Ignore Message']."</a></span></span>";
						$char_counter++;
						continue;
					}
				}

				/**
				* Now construct realm databases string in format:
				* "CHAR_DB1|REALM_SQL_PORT|DB1_HOST|DB1_USER|DB1_PASS;CHAR_DB2|REALM_SQL_PORT;CHAR_DB3|REALM_SQL_PORT" etc...
				* without quotes
				*/
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
		$_SESSION['wwcmsv2install']['raorsoap_port'] = $raorsoap_port;
		$_SESSION['wwcmsv2install']['char_db_string'] = $char_db;
	}
	if ($errorCount == 0)
	{
		echo "<br><font color=green>Character database check - passed</font>";
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

if ($step <= 5) {
	library::create_dblink($con, $wwcms['db_type']);
	if ($con->init($wwcms['db_host'], $wwcms['db_user'], $wwcms['db_pass']))
	{
		if ($step == 1)
		{
			echo "<fieldset><legend>Step 1 / 5</legend>";
			$result = $con->query("CREATE DATABASE `" . $wwcms['web_db'] . "`");
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
			$con->select_db($wwcms['web_db']);

		$tables = array(
			"`wwc2_active_guests`"=>"CREATE TABLE `wwc2_active_guests` (`ip` varchar(15) NOT NULL,`timestamp` int(11) unsigned NOT NULL,PRIMARY KEY (`ip`)) ENGINE=MyISAM DEFAULT CHARSET=latin1;",
			"`wwc2_active_users`"=>"CREATE TABLE `wwc2_active_users` (`username` varchar(30) NOT NULL, `timestamp` int(11) unsigned NOT NULL, PRIMARY KEY (`username`)) ENGINE=MyISAM DEFAULT CHARSET=latin1;",
			"`wwc2_banned_users`"=>"CREATE TABLE `wwc2_banned_users` (`username` varchar(30) NOT NULL, `timestamp` int(11) unsigned NOT NULL, PRIMARY KEY (`username`)) ENGINE=MyISAM DEFAULT CHARSET=latin1;",
			"`wwc2_config`"=>"CREATE TABLE `wwc2_config` (`conf_name` varchar(255) COLLATE latin1_general_ci NOT NULL DEFAULT '', `conf_value` text COLLATE latin1_general_ci, `conf_descr` text COLLATE latin1_general_ci, `conf_stickied` int(1) NOT NULL DEFAULT '0', `conf_dropdown` text COLLATE latin1_general_ci, PRIMARY KEY (`conf_name`)) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;",
			"`wwc2_links`"=>"CREATE TABLE `wwc2_links` (`id` int(10) NOT NULL AUTO_INCREMENT, `linktitle` varchar(255) NOT NULL DEFAULT 'notitle', `linkurl` varchar(255) NOT NULL DEFAULT 'http://', `linkdescr` varchar(255) DEFAULT '', `linkgrup` varchar(100) NOT NULL DEFAULT '0', `linkorder` int(11) NOT NULL DEFAULT '0', `linkprems` int(10) NOT NULL DEFAULT '0',PRIMARY KEY (`id`)) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;",
			"`wwc2_news`"=>"CREATE TABLE `wwc2_news` (`id` bigint(20) NOT NULL AUTO_INCREMENT, `title` varchar(255) COLLATE latin1_general_ci NOT NULL, `content` longtext COLLATE latin1_general_ci NOT NULL,`iconid` int(11) NOT NULL DEFAULT '0', `timepost` varchar(100) COLLATE latin1_general_ci NOT NULL, `stickied` int(1) NOT NULL DEFAULT '0' COMMENT '0 or 1',`hidden` int(1) NOT NULL DEFAULT '0' COMMENT '0 or 1', `author` varchar(50) COLLATE latin1_general_ci NOT NULL, PRIMARY KEY (`id`)) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;",
			"`wwc2_news_c`"=>"CREATE TABLE `wwc2_news_c` (`id` bigint(20) NOT NULL AUTO_INCREMENT, `poster` varchar(255) COLLATE latin1_general_ci NOT NULL, `content` text COLLATE latin1_general_ci NOT NULL,`newsid` int(11) NOT NULL, `timepost` varchar(100) COLLATE latin1_general_ci NOT NULL, `datepost` varchar(100) COLLATE latin1_general_ci NOT NULL,PRIMARY KEY (`id`)) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;",
			"`wwc2_template`"=>"CREATE TABLE `wwc2_template` (`templateid` int(10) unsigned NOT NULL auto_increment, `styleid` smallint(6) NOT NULL default '0', `title` varchar(100) NOT NULL default '',`template` mediumtext, `template_un` mediumtext, `templatetype` enum('template','css','other') NOT NULL default 'template', `dateline` int(10) unsigned NOT NULL default '0',`username` varchar(100) NOT NULL default '', `version` varchar(30) NOT NULL default '', PRIMARY KEY (`templateid`), KEY `title` (`title`,`styleid`,`templatetype`)) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;",
			"`wwc2_users_more`"=>"CREATE TABLE `wwc2_users_more` (`id` bigint(20) NOT NULL AUTO_INCREMENT, `acc_login` varchar(55) COLLATE latin1_general_ci NOT NULL, `vp` bigint(55) NOT NULL DEFAULT '0',`userid` varchar(32) COLLATE latin1_general_ci DEFAULT NULL, `question` varchar(100) COLLATE latin1_general_ci DEFAULT NULL, `answer` varchar(100) COLLATE latin1_general_ci NOT NULL DEFAULT '',`dp` bigint(55) NOT NULL DEFAULT '0', `gmlevel` varchar(11) COLLATE latin1_general_ci NOT NULL DEFAULT '', `avatar` varchar(100) COLLATE latin1_general_ci NOT NULL DEFAULT '',PRIMARY KEY (`id`,`acc_login`)) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;",
			"`wwc2_vote_data`"=>"CREATE TABLE `wwc2_vote_data` (`id` bigint(21) NOT NULL AUTO_INCREMENT, `userid` bigint(21) DEFAULT NULL, `siteid` bigint(21) NOT NULL,`timevoted` bigint(21) NOT NULL, `voteip` varchar(21) COLLATE latin1_general_ci DEFAULT NULL, PRIMARY KEY (`id`)) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;"
			);

		// Empty Database
		if ($step == 2)
		{
			echo "<fieldset><legend>Step 2 / 5</legend>";
			foreach ($tables as $table => $createSql)
			{
				if($con->query("DROP TABLE IF EXISTS $table"))
					echo "<br>".$installer_lang['Delete']." ".$table;
			}
			echo "</fieldset>";
		}

		// Create Tables
		if ($step == 3)
		{
			echo "<fieldset><legend>Step 3 / 5</legend>";
			foreach ($tables as $table => $createSql)
			{
				if ($con->query($createSql))
				{
					echo "<br>".$installer_lang['Create']." ".$table;
				}
				else
				{
					echo "<br>".$installer_lang['Failed to create tables']." ".$table." (".$con->getLastError().")";
				}
			}
			echo "</fieldset>";
		}

		if ($step == 4)
		{
			echo "<fieldset><legend>Step 4 / 5</legend>";
			$raorsoap_port = (isset($wwcms['raorsoap_port']) ? $wwcms['raorsoap_port'] : '');
			$core = $wwcms['core'];
			if ($raorsoap_port == '')
				$raorsoap_port = ($core == 'Trinity' ? 3443 : 7878);

			/**
			* TRINITY RA PORT, MANGOS SOAP PORT, TRINITY SOAP PORT:
			*/
			$trinity_soap_port = ($core == "Trinity" ? "7878" : $raorsoap_port);
			$trinity_ra_port = ($core == "Trinity" ? $raorsoap_port : ($core == "Trinitysoap" ? "3443|" : "3443"));
			$trinity_ra_userpass =  ($core == "Trinity" ? strtoupper($wwcms['char_rasoap_user'])."|".$wwcms['char_rasoap_pass'] : null);
			$trinity_soap_userpass = ($core == "Trinitysoap" ? strtoupper($wwcms['char_rasoap_user'])."|".$wwcms['char_rasoap_pass'] : null);
			$mangos_soap_port = ($core == "Trinity" ? "7878" : $raorsoap_port);
			$mangos_soap_userpass = ($core == "Trinity" ? null : strtoupper($wwcms['char_rasoap_user'])."|".$wwcms['char_rasoap_pass']);

			/**
			* Realm names name|name|name
			*/
			if (isset($wwcms['char_names2']))
				$char_names = implode("|", $wwcms['char_names2']);
			else
				$char_names = "|";

			$insertQueries = array(
				"`wwc2_config`" =>
					"('engine_lang','$lang','','1','')".
					",('engine_core','".$wwcms['core']."','','1','')".
					",('engine_logon_db','".$wwcms['logon_db']."','','1','')".
					",('engine_web_db','".$wwcms['web_db']."','','1','')".
					",('engine_styleid','1','Change style ID to change style.','1','')".
					",('trinity_soap_port','".$trinity_soap_port."','TrintyCore: SOAP Port (for sending ingame mail)<br><small>realm1_SOAP_port|realm2_SOAP_port</small>','1','')".
					",('mangos_soap_port','".$mangos_soap_port."','MaNGOS: Soap Port (for sending ingame mail)<br><small>realm1_SOAP_port|realm2_SOAP_port</small>','1','')".
					",('trinity_ra_port','$trinity_ra_port','TrintyCore: Remote Access Port (for sending ingame mail)<br><small>realm1_RA_port|realm2_RA_port</small>','1','')".
					($mangos_soap_userpass == null ? "" : ",('mangos_soap_userpass','$mangos_soap_userpass','MaNGOS: SOAP Username and Password (for sending ingame mail)<br><small>SOAP_username|SOAP_password</small>','1','')").
					($trinity_soap_userpass == null ? "" : ",('trinity_soap_userpass','$trinity_soap_userpass','TrintyCore: SOAP Username and Password (for sending ingame mail)<br><small>SOAP_username|SOAP_password</small>','1','')").
					($trinity_ra_userpass == null ? "" : ",('trinity_ra_userpass','$trinity_ra_userpass','TrintyCore: RA Username and Password (for sending ingame mail)<br><small>RA_username|RA_password</small>','1','')").
					",('engine_char_dbs','".$wwcms['char_db_string']."','<br><small>CHAR_DB1|REALM_SQL_PORT|DB1_HOST|DB1_USER|DB1_PASS;CHAR_DB2|REALM_SQL_PORT</small>','1','')".
					",('engine_realmnames','".htmlspecialchars($char_names)."','<br><small>RealmName1|realmname2|RealmName3</small>','1','')".
					",('engine_acp_folder','admincp\/','foldername\/','1','')".
					",('license','FREE','','1','')".
					",('premission_admin','" . ($core == "ArcEmu" ? "az" : "4") . "','','1','')".
					",('premission_gm','" . ($core == "ArcEmu" ? "a" : "3") . "','','1','')".
					",('title','My WoW Server','','1','')".
					",('engine_logusers','true','true/false, disable if your website is slow','1','true|false')".
					",('vote_enable','1','1 = enabled;  0 = disabled','1','0|1')".
					",('module_userpanel','loginout.php|register.php|credits.php|userpanel.php|MODULE_TEMPLATE.php','Userpanel: Do not show modules in this list<br><small>module1.php|module2.php</small>','1','')".
					",('footer_detail','0','Footer Credits: <small>0 = simplified; 1 = full detail; 2 = full for admins only</small>','1','0|1|2')",
				"`wwc2_news`" => "(null, 'Welcome','Thank you for using OpenWoW CMS v2.

If your administrator double click here to edit news.

Go to [b]administration panel[/b] to manage CMS.',0, '".@date("U")."',0, 0,'WebWoWCMSv2')"
				);

			foreach ($insertQueries as $table => $insert)
			{
				if ($con->query("INSERT INTO $table VALUES $insert"))
					echo "<br>".$installer_lang['Inserting data to']." ".$table;
				else
					echo "<br><strong>Query</strong> " . htmlspecialchars($insert) . " <b>FAILED</b><br>REPORT: " . $con->getLastError() . "<br>";
			}
			echo "</fieldset>";
		}

		if ($step == 5)
		{
			echo "<fieldset><legend>Step 5 / 5</legend>";
			echo "<br>".$installer_lang['Inserting data to']." `wwc2_template`";
			mysql_import_file($con, 'wwc2_template.sql');
			echo "<br>".$installer_lang['Inserting data to']." `wwc2_links`<br>";
			mysql_import_file($con, 'wwc2_links.sql');
			echo "</fieldset>";
			echo "<br><font color=green>".$installer_lang['Tables are created successfully']."</font>";
			echo '<br><br><input name="next" type="submit" value="'.$installer_lang['Next Step'].' (6/8)"></form>';
			echo '<script type="text/javascript">$("#db_install").remove();</script>';
			$_SESSION['wwcmsv2install']['sqlstep'] = 0;
			$_SESSION['wwcmsv2install']['db_test'] = false;
		}
		$con->close();
	}
	else
	{
		echo '&nbsp;&nbsp;<font color="red">'.$installer_lang['Connection Failed'].'</font> ('.$con->getLastError().")";
		return;
	}
}
else {
	exit; // Should never Happen
}
// Next Step
++$_SESSION['wwcmsv2install']['sqlstep'];
return;
?>