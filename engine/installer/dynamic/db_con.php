<?php
###################################################################
# This file is a part of OpenWoW CMS by www.openwow.com
#
#   Project Owner    : OpenWoW CMS (http://www.openwow.com)
#   Copyright        : (c) www.openwow.com, 2010
#   Credits          : Based on work done by AXE and Maverfax
#   License          : GPLv3
##################################################################

session_start();
error_reporting(~E_NOTICE);
if (!isset($_SESSION['wwcmsv2install'])) 
{
	echo 'No access.';
	session_destroy();
	exit;
}

if (!class_exists("library"))
	include $_SERVER["DOCUMENT_ROOT"]. "/library/library.php";

if (library::supported_databases($_POST['dbtype']))
{
	library::create_dblink($websiteDB, $_POST['dbtype']);
	$websiteDB->init($_POST['host'], $_POST['user'], $_POST['pass']) or die('<font color="red">'.library::_htmlspecialchars($_GET['f']).'</font> ' . $websiteDB->getLastError());
	$websiteDB->close();
	echo '<font color="green">'.library::_htmlspecialchars($_GET['s']).'</font><br><br><input name="next" type="submit" value="'.$_GET['l'].' (4/8)"></form>';
}
?>