<?php
/************************************************************************
*											engine/installer/dynamic/db_con.php
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

session_start();
error_reporting(~E_NOTICE);
if (!isset($_SESSION['wwcmsv2install']))
{
	echo 'No access.';
	session_destroy();
	exit;
}

define("PATHROOT", "../../../");
include PATHROOT."library/library.php";

library::create_dblink($con, $_POST['dbtype']);
$con->init($_POST['host'], $_POST['user'], $_POST['pass']) or die('<font color="red">'.library::_htmlspecialchars($_GET['f']).'</font> ' . $con->getLastError());
$con->close();
echo '<font color="green">'.library::_htmlspecialchars($_GET['s']).'</font><br><br><input name="next" type="submit" value="'.$_GET['l'].' (4/8)"></form>';
?>