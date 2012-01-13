<?php
/***************************************************************************
*                               index.php
*                            -------------------
*   Project Owner    : OpenWoW CMS (www.openwow.com)
*   Copyright        : (c) www.openwow.com, 2010
*   Credits          : Based on work done by AXE and Maverfax
*   License          : GPLv3
*****************************************************************************/


/*
|---------------------------------------------------------------
| PHP ERROR REPORTING LEVEL
|---------------------------------------------------------------
|
| By default Web-WoW runs with error reporting set to ALL.
| For security reasons you are encouraged to change this when
| your site goes live. For more info visit:  
| http://www.php.net/error_reporting
|
*/
	error_reporting(E_NOTICE);
	/* Will not show NOTICE errors when caching, do not change. */
	$error_reporting_cache='~E_NOTICE';
/*
|---------------------------------------------------------------
| INITIALIZE WEB-WOW
|---------------------------------------------------------------
|
| Here we go!
|
*/
	require_once './engine/init.php';
	$Html->_construct();
