<?php

/**
 * class module_base
 *
 * Description for class module_base
 *
 * @author:
*/
class module_base  {
	var $uniqueid;
	// array(config name => array(value, description))
	var $configFields = array();
	var $sqlQueries = array();
	var $proccess = false;

	function process() {
		/* Reinitilaze 'form' proccess with latest session data */
		Form::_Form();
	}

	function processAjaxRequest() {}

	function isAjaxRequest() { if($_SERVER['REQUEST_METHOD']=='POST') return true; }

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

		if (strtolower($user->userlevel) != strtolower($config['premission_admin']))
		{
			echo "<div style='padding:4px; background:white;color:black;text-align:center; border:solid 1px black'>Admin needs to install this module first. If you are admin, please login with your admin account and revisit this module page.</div>";
			return false;
		}

		foreach ($this->configFields as $name => $valDesc)
		{
			// Already in config table
			if (in_array($name, $config)) continue;
			$db->query("INSERT INTO ".TBL_CONFIG." (conf_name,conf_value,conf_descr) VALUES ('".$name."','".$valDesc[0]."','".$valDesc[1]."')") or print("SQL REPORT: ".$db->getLastError()."<br>");
			array_merge($config, array($name => $valDesc[0]));
		}

		if (!isset($config['installed_modules']))
		{
			$config['installed_modules'] = $this->uniqueid."|";
			array_push($this->sqlQueries, 'INSERT INTO '.$config['engine_web_db'].'.wwc2_config(conf_name,conf_value,conf_descr) VALUES ("installed_modules", "'.$config['installed_modules'].'", "Names of installed modules, seperated by |");');
		}
		else
		{
			$config['installed_modules'] .= $this->uniqueid."|";
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
		echo 'and setup variables for this module.<br />After you recache page this message will go away.</div>';
	}
}

?>