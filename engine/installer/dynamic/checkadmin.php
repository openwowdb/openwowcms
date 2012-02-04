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

if ($_POST['admin_username'] == '' or $_POST['admin_password'] == '')
{
	echo 'You did not type in username or password or both.<br><br><input type="button" value="Save" onclick="javascript:checkadmin();return false"></form>';
	exit;
}

if (!class_exists("library"))
	include $_SERVER["DOCUMENT_ROOT"]. "/library/library.php";

if (!library::supported_databases($_POST['dbtype'])) die('Unknown database type');

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
	$enc_pass = strtoupper(sha1(strtoupper($_POST['admin_username'].':'.$_POST['admin_password'])));
	$con->query("SELECT * FROM ". $_SESSION['wwcmsv2install']['logon_db'] .".account WHERE username='".$_POST['admin_username']."' LIMIT 1") or die($db->getLastError());
	if ($con->numRows() == '1')//account is found
	{
		//check password
		$row = $con->getRow();
			
		if (strtoupper($row['sha_pass_hash']) == $enc_pass)
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
