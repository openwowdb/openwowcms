<?php
###################################################################
# This file is a part of OpenWoW CMS by www.openwow.com
#                                init.php
#                            -------------------
#
#   Project Owner    : OpenWoW CMS (http://www.openwow.com)
#   Copyright        : (c) www.openwow.com, 2010
#   Credits          : Based on work done by AXE and Maverfax
#   License          : GPLv3
##################################################################


if (!defined('PATHROOT'))
{
	define('PATHROOT', './');
}

// Strip magic quotes from request data.
if (function_exists('get_magic_quotes_gpc') && get_magic_quotes_gpc())
{
	if( ! function_exists('stripslashes_deep'))
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
	require_once (PATHROOT."config/config.php");
	require_once (PATHROOT."config/config_db.php");
	
	// Include mysql engine and start connection if configs are valid
	if (defined('AXE_db') && defined('AXE'))
	{
		require_once (PATHROOT . 'engine/db/mysql.php');
	}
}

require_once (PATHROOT. 'engine/version.php');


// start the page generation timer
//$pagestarttime = microtime();
define('TIMESTART', microtime());

// set the current unix timestamp
define('TIMENOW', time());

//required in PHP 5.3, set default timezone for date() function
if (version_compare(PHP_VERSION, '5.3.0') >= 0)
{
	if(function_exists("date_default_timezone_set") and function_exists("date_default_timezone_get"))
	{
		@date_default_timezone_set(@date_default_timezone_get()); 
	}
}

// Include install if config doesn't exists
if (!defined('AXE_db'))
{
	// restrict users from directly accessing the install directory
	define('INSTALL_AXE',1);

	require_once(PATHROOT . 'engine/installer/install.php');
}

// Include language (not user specific)
require_once (PATHROOT . 'engine/lang/' . strtolower($config['engine_lang']) . '/common.php');


// Include necessary libraries
require_once (PATHROOT."engine/func/session.php");
require_once (PATHROOT."engine/core/".strtolower($config['engine_core']).".php");
require_once (PATHROOT."engine/func/required.php");

//start user session, determine if it's logged in and set variables
$user = new User;
$user->Session();
