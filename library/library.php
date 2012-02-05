<?php
/**
 * class library
 *
 * Description for class library
 *
 * @author:
*/
class library  {
	static $supportdbs = array("mysql");

	/**
	 * _htmlspecialchars
	 *
	 * @param string $string
	 * @return string
	 *
	 */
	static function _htmlspecialchars($string)
	{
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
	static function supported_databases($dbtype = false)
	{
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
	static function create_dblink(&$dblink, $type)
	{
		if ($type == "") return;
		$className = "database_$type";
		if (!class_exists($className))
		{
			include "classes/database/$className.php";
		}
		$dblink = new $className;
	}
}
?>