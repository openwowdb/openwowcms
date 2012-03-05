<?php
/************************************************************************
*                                 index.php
*                            -------------------
* 	 Copyright (C) 2011
*
*    This package is free software: you can redistribute it and/or modify
*    it under the terms of the GNU General Public License as published by
*    the Free Software Foundation, either version 3 of the License, or
*    (at your option) any later version.
*
*    This package is distributed in the hope that it will be useful,
*    but WITHOUT ANY WARRANTY; without even the implied warranty of
*    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*    GNU General Public License for more details.
*
*    You should have received a copy of the GNU General Public License
*    along with this program. If not, see <http://www.gnu.org/licenses/>.
*
*
*  	 This package is based on the work of the web-wow.net and openwow.com
* 	 team during 2007-2010.
*
* 	 Updated: $Date 2012/02/08 14:00 $
*
************************************************************************/

/*
|---------------------------------------------------------------
| PHP ERROR REPORTING LEVEL
|---------------------------------------------------------------
|
| By default this program runs with error reporting set to ALL.
| For security reasons you are encouraged to change this when
| your site goes live. For more info visit:
| http://www.php.net/error_reporting
|
*/
	error_reporting(E_ALL);
	/* Will not show NOTICE errors when caching, do not change. */
	$error_reporting_cache='E_ALL';

	include './engine/init.php';
	Html::_construct();
?>