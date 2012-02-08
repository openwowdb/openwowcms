<?php
/************************************************************************
*														 library/library.php
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
*   The autoload function is introduced in php v5, and is called whenever a new class is initiated.
*
* @param string Class Name
* @return void
* @access public
*/
function __autoload($className) {
	$className = strtolower($className);
	if (class_exists($className, false)) return;

	$directories = array('library/classes/database/', 'library/classes/file/',
	'library/classes/modules/', 'library/classes/security/');
	foreach ($directories as $directory) {
		$path = PATHROOT . $directory . $className . ".php";
		if (file_exists($path)) {
			include $path;
			return;
		}
	}
}

/**
* class library
*
* Description for class library
*
*/
class library {
	static $supportdbs = array();

	static function init() {
		// Autogenerate Database Types
		$database_files = filehandler::getDir("library/classes/database", false, '/[^database_][a-z]\.php$/i');
		foreach($database_files as $file) {
			// Remove file extension (.PHP or .php)
			$file = preg_replace("/.php/i", "", $file);
			// Find database type (database_XXXXX.php)
			$file = explode("_", $file);
			array_push(self::$supportdbs, $file[1]);
		}
	}

	/**
	* _htmlspecialchars
	*
	* @param string $string
	* @return string
	*
	*/
	static function _htmlspecialchars($string) {
		$string = preg_replace('/&(?!#[0-9]+;)/s', '&amp;', $string);
		$string = str_replace(array('<', '>', '"'), array('&lt;', '&gt;', '&quot;'), $string);
		return $string;
	}

	/**
	* supported_databases
	*
	* @param string $dbtype If not false then it will check its a valid database type
	* @return mixed Returns true or array(self::$supportdbs)
	*
	*/
	static function supported_databases($dbtype = false) {
		if ($dbtype == false) return self::$supportdbs;
		if (in_array($dbtype, self::$supportdbs)) return true;
	}

	/**
	* Creates a new database link object
	*
	* @param ref $dblink Database link variable
	* @param string $type Type of database connection (mysql, mssql, etc)
	*
	*/
	static function create_dblink(&$dblink, $type) {
		if ($type == "") return;
		if (!self::supported_databases($type)) die(trigger_error("Unknown database type " . $type, E_ERROR));
		$className = "database_$type";
		$dblink = new $className;
	}
}
library::init();
?>