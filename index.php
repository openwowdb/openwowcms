<?php
/***************************************************************************
 *                                index.php
 *                            -------------------
 *   Project                 : Web-WoW
 *   Begin                   : Friday, August 6, 2010
 *   Copyright               : (C) 2010 AXE       ( zg_20102@hotmail.com ),
 *   Small contribution from : Maverfax  		  ( maverfax@gmail.com )
 *
 *      Do not redistribute this file without permission from AXE.
 *
 ***************************************************************************/

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