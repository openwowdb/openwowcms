<?php
// Prevent duplicate loading
if (class_exists("database_mysql")) return;
// Include required class database
if (!class_exists("database")) include __DIR__. "/database.php";

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
			++$this->num_queries;
			$this->string_queries .= '<br>'.$sql;
			return $this->query_result;
		}
		return false;
	}

	function getArray() {
		$array = array();
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
?>