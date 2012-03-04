<?php
/************************************************************************
*										engine/installer/dynamic/checkadmin.php
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

if ($_POST['admin_username'] == '' or $_POST['admin_password'] == '')
{
	echo 'You did not type in username or password or both.<br><br><input type="button" value="Save" onclick="javascript:checkadmin();return false"></form>';
	exit;
}

define('PATHROOT', '../../../');
include PATHROOT . "library/library.php";

library::create_dblink($con, $_SESSION['wwcmsv2install']['db_type']);
$con->init($_SESSION['wwcmsv2install']['db_host'], $_SESSION['wwcmsv2install']['db_user'], $_SESSION['wwcmsv2install']['db_pass']) or die('&nbsp;&nbsp;<font color="red">'.library::_htmlspecialchars($_GET['f']).'</font> ('.$con->getLastError().")");

$core = strtolower($_SESSION['wwcmsv2install']['core']);
if ($core == 'arcemu')
{
	$con->query("SELECT * FROM ". $_SESSION['wwcmsv2install']['logon_db'] .".accounts WHERE login='".$_POST['admin_username']."' LIMIT 1") or die($con->getLastError());
	if ($con->numRows() == '1')//account is found
	{
		//check password
		$row = $con->getRow();
		if ($row['password'] == $_POST['admin_password'])
		{
			//user if confirmed, add him to website db with admin privilages
			$con->query("INSERT INTO ". $_SESSION['wwcmsv2install']['web_db'] .".wwc2_users_more (acc_login,vp,userid,dp,gmlevel) VALUES ('".$row['login']."','0','".$row['acct']."','0','az')") or die($con->getLastError());
		}
		else
		{
			echo 'User password is wrong.<br><br><input type="button" value="Save" onclick="javascript:checkadmin();return false"></form>';
			exit;
		}
	}
	else //account is not found, create new one
	{
		$con->query("INSERT INTO ". $_SESSION['wwcmsv2install']['logon_db'] .".accounts (login, password, gm) VALUES ('".$_POST['admin_username']."','".$_POST['admin_password']."','az')") or die($con->getLastError());
		$con->query("SELECT * FROM ". $_SESSION['wwcmsv2install']['logon_db'] .".accounts WHERE login='".$_POST['admin_username']."' LIMIT 1") or die($con->getLastError());
		$row = $con->getRow();
		$con->query("INSERT INTO ". $_SESSION['wwcmsv2install']['web_db'] .".wwc2_users_more (acc_login,vp,userid,dp,gmlevel) VALUES ('".$row['login']."','0','".$row['acct']."','0','az')") or die($con->getLastError());
	}
}
elseif($core == 'trinity' or $core == 'mangos')
{
	$enc_pass = sha1(strtoupper($_POST['admin_username'].':'.$_POST['admin_password']));
	$con->query("SELECT * FROM ". $_SESSION['wwcmsv2install']['logon_db'] .".account WHERE username='".$_POST['admin_username']."' LIMIT 1") or die($con->getLastError());
	if ($con->numRows() == '1')//account is found
	{
		//check password
		$row = $con->getRow();

		if (strtoupper($row['sha_pass_hash']) == strtoupper($enc_pass))
		{
			//user if confirmed, add him to website db with admin privilages
			$con->query("INSERT INTO ". $_SESSION['wwcmsv2install']['web_db'] .".wwc2_users_more (acc_login,vp,userid,dp,gmlevel) VALUES ('".$row['username']."','0','".$row['id']."','0','4')") or die($con->getLastError());
		}
		else
		{
			echo 'User password is wrong.<br><br><input type="button" value="Save" onclick="javascript:checkadmin();return false"></form>';
			exit;
		}
	}
	else //account is not found, create new one
	{
		$con->query("INSERT INTO ". $_SESSION['wwcmsv2install']['logon_db'] .".account (username, sha_pass_hash) VALUES ('".$_POST['admin_username']."','".$enc_pass."')") or die($con->getLastError());
		$con->query("SELECT * FROM ". $_SESSION['wwcmsv2install']['logon_db'] .".account WHERE username='".$_POST['admin_username']."' LIMIT 1") or die($con->getLastError());
		$row = $con->getRow();
		$con->query("INSERT INTO ". $_SESSION['wwcmsv2install']['web_db'] .".wwc2_users_more (acc_login,vp,userid,dp,gmlevel) VALUES ('".$row['username']."','0','".$row['id']."','0','4')") or die($con->getLastError());
	}
}
else
{
	echo "Unknown core, go back to core selection.</form>";exit;
}

echo '<font color="green">Success!</font><br><br><input name="next" type="submit" value="'.$_GET['l'].' (7/8)"></form>';

$con->close();
?>
