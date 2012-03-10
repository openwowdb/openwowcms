<?php
/************************************************************************
*									library/classes/database/database_mysql.php
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
* class database_mysql
*
* Description for class database_mysql
*
* @see library/classes/database/database.php
*
*/
class database_mysql extends database {

	function init($hostname, $username, $password, $database = null) {
		// Open connection to mysql server
		$this->link_id = @mysql_connect($hostname, $username, $password);
		if ($this->link_id)
		{
			// Select mysql server database
			if ($database != null)
				$this->select_db($database);
			return true;
		}
		// Failed to connect to mysql server
		else
		{
			return false;
		}
	}

	function select_db($database) {
		return @mysql_select_db($database);
	}

	function query($sql, $unbuffered = false) {
		if ($unbuffered)
			$this->query_result = mysql_unbuffered_query($sql, $this->link_id);
		else
			$this->query_result = mysql_query($sql, $this->link_id);

		if ($this->query_result)
		{
			self::$num_queries++;
			self::$string_queries .= '<br>'.$sql;
			return $this->query_result;
		}
		return false;
	}

	function getArray() {
		$array = array();
		if (!$this->query_result) return $array;
		while ($row = mysql_fetch_assoc($this->query_result))
		{
			array_push($array, $row);
		}
		return $array;
	}

	function getRow($query_result = null) {
		if ($query_result != null)
			return mysql_fetch_array($query_result, MYSQL_BOTH);
		return mysql_fetch_array($this->query_result, MYSQL_BOTH);
	}

	function numRows() {
		return mysql_num_rows($this->query_result);
	}

	function insertId() {
		return mysql_insert_id($this->link_id);
	}

	function escape($string) {
		if (is_array($string))
		{
			return array_map("self::escape", $string);
		}

		if (function_exists('mysql_real_escape_string'))
		{
			return mysql_real_escape_string($string, $this->link_id);
		}
		else
		{
			return mysql_escape_string($string);
		}
	}

	function close() {
		if ($this->link_id)
		{
			mysql_close($this->link_id);
			unset($this->link_id);
		}
	}

	function getLastError() {
		return mysql_error();
	}
}

function connect_realm($id) {
	//id -> from 0 to infinity
	global $config, $db;
	#check char db and assign connector or connect
	#DB1|REALM_PORT|DB1_HOST|DB1_USER|DB1_PASS;DB2|REALM_PORT;etc...
	$split0=explode(';',$config['engine_char_dbs']); //DB1|REALM_PORT|DB1_HOST|DB1_USER|DB1_PASS or DB2|REALM_PORT
	$split1=explode('|',$split0[$id]);//we have data in array
	if (isset($split1[2])) {
		$IsOn = @fsockopen($split1['2'],$split1['1'], $ERROR_NO, $ERROR_STR,(float)0.5);
		if($IsOn){
			//Online
			@fclose($IsOn);
			library::create_dblink($db_realmconnector, 'mysql');
			$db_realmconnector->init(trim($split1['2'].":".$split1['1']), trim($split1[3]), trim($split1[4]), $split1[0]);
		}
	} else {
		$db_realmconnector = $db;
		$db_realmconnector->select_db($split1['0']);
	}
	return $db_realmconnector;
}
?>