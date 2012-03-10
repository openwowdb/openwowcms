<?php
/************************************************************************
*											library/classes/modules/modules.php
*                            -------------------
* 	 Copyright (C) 2011
*
* 	 This package is free software: you can redistribute it and/or modify
*    it under the terms of the GNU General Public License as published by
*    the Free Software Foundation, either version 3 of the License, or
*    (at your option) any later version.
*
* 	 Updated: $Date 2012/02/08 14:00 $
*
************************************************************************/
/**
 * class module_base
 *
 * Description for class module_base
 *
*/
class module_base {
	var $uniqueid;
	// array(config name => array(value, description))
	var $configFields = array();
	var $sqlQueries = array();
	var $proccess = false;
	var $showInUserpanel = true;
	var $sessionTimeout = 300;

	function process() {
		/* Reinitilaze 'form' proccess with latest session data */
		Form::_Form();
	}

	function plugin() {
		global $user;
		if ($this->uniqueid == '') $this->uniqueid = get_class($this);
		ajaxkey::createkey($user->userid, $this->sessionTimeout, $this->uniqueid);
	}

	function processAjaxRequest() {
		global $user;
		if (!isset($user)) return false;
		if ($this->uniqueid == '') $this->uniqueid = get_class($this);
		if (ajaxkey::verifykey($user->userid, $this->sessionTimeout, $this->uniqueid))
			return true;
		//Session has expired
		return false;
	}

	function isAjaxRequest() {
		if ($_SERVER['REQUEST_METHOD'] != 'POST') return false;
		if (!isset($_SERVER['HTTP_REFERER'])) return false;
		return true;
	}

	function DoInstall() {
		global $config, $user, $db;
		// Already processed
		if ($this->proccess) return false;
		if ($this->uniqueid == '') $this->uniqueid = get_class($this);
		// Already installed
		if (isset($config['installed_modules']))
		{
			$modules = explode("|", $config['installed_modules']);
			if (array_search($this->uniqueid, $modules) !== false)
				return false;
		}

		if (!$user->isAdmin())
		{
			echo "<div style='padding:4px; background:white;color:black;text-align:center; border:solid 1px black'>Admin needs to install this module first. If you are admin, please login with your admin account and revisit this module page.</div>";
			return true;
		}

		foreach ($this->configFields as $name => $valDesc)
		{
			// Already in config table
			if (in_array($name, $config)) continue;
			$db->query("INSERT INTO ".TBL_CONFIG." (conf_name,conf_value,conf_descr) VALUES ('".$name."','".$valDesc[0]."','".$valDesc[1]."')") or print("SQL REPORT: ".$db->getLastError()."<br>");
			array_merge($config, array($name => $valDesc[0]));
		}

		if (!isset($config['installed_modules']))
			$config['installed_modules'] = $this->uniqueid."|";
		else
			$config['installed_modules'] .= $this->uniqueid."|";
		array_push($this->sqlQueries, 'REPLACE INTO '.$config['engine_web_db'].'.wwc2_config(conf_name,conf_value,conf_descr) VALUES ("installed_modules", "'.$config['installed_modules'].'", "Names of installed modules, seperated by |");');

		if ($this->showInUserpanel == false)
		{
			$exclude_modulenames = explode('|',$config['module_userpanel']);
			if (array_search($this->uniqueid.".php", $exclude_modulenames) === false)
			{
				array_push($exclude_modulenames, 'shoutbox.php');
				$config['module_userpanel'] .= "|".$this->uniqueid.".php";
				array_push($this->sqlQueries, 'UPDATE '.$config['engine_web_db'].'.wwc2_config SET conf_value="'.$config['module_userpanel'].'" WHERE conf_name="module_userpanel"');
			}
		}

		$db->select_db($config['engine_web_db']);
		foreach ($this->sqlQueries as $sql)
		{
			if ($sql != '')
			{
				$db->query($sql) or print("SQL REPORT: ".$db->getLastError()."<br>");
			}
		}

		echo '<div style="padding:4px;  background:white;color:black;text-align:center; border:solid 1px black">';
		echo 'This module is now installed, please go to:<br>Administration Panel &gt; Configuration Variables<br />';
		echo 'and setup variables for this module.</div>';
		Html::cache_configfile();
		return true;
	}
}

?>