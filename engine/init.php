<?php
/************************************************************************
*														 	 engine/init.php
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

if (!defined('PATHROOT')) define('PATHROOT', './');

if (version_compare(PHP_VERSION, '5.0.0') < 0)
{
	die('This CMS is set to work only on PHP versions 5.0.0 and above, your currently running on version: ' . PHP_VERSION .' upgrade @ <a href="http://windows.php.net/download/">http://windows.php.net/download/</a>');
}

include PATHROOT."library/library.php";
set_error_handler("errorhandler::error", -1);
set_exception_handler('errorhandler::exception');
//register_shutdown_function('errorhandler::shutdown');

function get_microtime() {
	$mtime = microtime();
	$mtime = explode(" ", $mtime);
	$mtime = (double)($mtime[1]) + (double)($mtime[0]);
	return $mtime;
}


// start the page generation timer
define('TIMESTART', get_microtime());

// set the current unix timestamp
define('TIMENOW', time());

//required in PHP 5.3, set default timezone for date() function
if (version_compare(PHP_VERSION, '5.3.0') >= 0)
{
	if (function_exists("date_default_timezone_set") and function_exists("date_default_timezone_get"))
		@date_default_timezone_set(@date_default_timezone_get());
}

// Strip magic quotes from request data.
if (function_exists('get_magic_quotes_gpc') && get_magic_quotes_gpc())
{
	if(!function_exists('stripslashes_deep'))
	{
		function stripslashes_deep($value)
		{
			if(is_array($value))
			{
				return array_map('stripslashes_deep', $value);
			}
			else
			{
				return stripslashes($value);
			}
		}
	}

	$_POST    = array_map('stripslashes_deep', $_POST);
	$_GET     = array_map('stripslashes_deep', $_GET);
	$_COOKIE  = array_map('stripslashes_deep', $_COOKIE);
	$_REQUEST = array_map('stripslashes_deep', $_REQUEST);
}

if (file_exists(PATHROOT . 'config/config.php') && file_exists(PATHROOT . 'config/config_db.php'))
{
	include PATHROOT."config/config.php";
	include PATHROOT."config/config_db.php";

	// Include mysql engine and start connection if configs are valid
	if (defined('AXE_db') && defined('AXE'))
	{
		library::create_dblink($db, "mysql");
		$db->init($db_host, $db_user, $db_pass) or die('Unable to connect to MySQL server.<br>' . $db->getLastError());
	}
}

include PATHROOT. 'engine/version.php';

// Include install if config doesn't exists
if (!defined('AXE_db'))
{
	// restrict users from directly accessing the install directory
	define('INSTALL_AXE',1);
	include PATHROOT . 'engine/installer/install.php';
}

// Include language (not user specific)
include PATHROOT . 'engine/lang/' . strtolower($config['engine_lang']) . '/common.php';

// Include necessary libraries
include PATHROOT."engine/func/session.php";
include PATHROOT."engine/core/base.php";
include PATHROOT."engine/func/form.php";
include PATHROOT."engine/core/".strtolower($config['engine_core']).".php";
include PATHROOT."engine/func/required.php";

//start user session, determine if it's logged in and set variables
$user = new User;
$user->Session();
?>