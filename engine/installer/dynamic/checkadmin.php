<?php
##################################################################
# This file is a part of OpenWoW CMS by www.openwow.co

#   Project Owner    : OpenWoW CMS (http://www.openwow.com
#   Copyright        : (c) www.openwow.com, 201
#   Credits          : Based on work done by AXE and Maverfa
#   License          : GPLv
#################################################################

session_start();
error_reporting(~E_NOTICE);
$fail=false;

if (!isset($_SESSION['wwcmsv2install'])) 
{
	echo 'No access.';session_destroy();exit;
}
function _htmlspecialchars($str)
{
	$str = preg_replace('/&(?!#[0-9]+;)/s', '&amp;', $str);
	$str = str_replace(array('<', '>', '"'), array('&lt;', '&gt;', '&quot;'), $str);
	return $str;
}



if ($_POST['admin_username']=='' or $_POST['admin_password']=='')
{
	echo 'You did not type in username or password or both.<br><br><input type="button" value="Save" onclick="javascript:checkadmin();return false"></form>';exit;
}
$con = @mysql_connect($_SESSION['wwcmsv2install']['db_host'], $_SESSION['wwcmsv2install']['db_user'], $_SESSION['wwcmsv2install']['db_pass']) or $fail=true;

if ($fail)
echo '&nbsp;&nbsp;<font color="red">'._htmlspecialchars($_GET['f']).'</font> ('.mysql_error().")";
else
{
	if (strtolower($_SESSION['wwcmsv2install']['core'])==strtolower('ArcEmu'))
	{
		$sql1 = mysql_query("SELECT * FROM ". $_SESSION['wwcmsv2install']['logon_db'] .".accounts WHERE login='".$_POST['admin_username']."' LIMIT 1")or die(mysql_error());
		if (mysql_num_rows($sql1)=='1')//account is found
		{
			//check password
			$sql2=mysql_fetch_assoc($sql1);
			if ($sql2['password']==$_POST['admin_password'])
			{
				//user if confirmed, add him to website db with admin privilages
				mysql_query("INSERT INTO ". $_SESSION['wwcmsv2install']['web_db'] .".wwc2_users_more (acc_login,vp,userid,dp,gmlevel) VALUES ('".$sql2['login']."','0','".$sql2['acct']."','0','az')")or die(mysql_error());
			}
			else
			{
				echo 'User password is wrong.<br><br><input type="button" value="Save" onclick="javascript:checkadmin();return false"></form>';exit;
			}
		}
		else //account is not found, create new one
		{
			mysql_query("INSERT INTO ". $_SESSION['wwcmsv2install']['logon_db'] .".accounts (login, password, gm) VALUES ('".$_POST['admin_username']."','".$_POST['admin_password']."','az')") or die(mysql_error());
			$sql3 = mysql_query("SELECT * FROM ". $_SESSION['wwcmsv2install']['logon_db'] .".accounts WHERE login='".$_POST['admin_username']."' LIMIT 1")or die(mysql_error());
			$sql4=mysql_fetch_assoc($sql3);
			mysql_query("INSERT INTO ". $_SESSION['wwcmsv2install']['web_db'] .".wwc2_users_more (acc_login,vp,userid,dp,gmlevel) VALUES ('".$sql4['login']."','0','".$sql4['acct']."','0','az')")or die(mysql_error());
			
		}
	}
	elseif(strtolower($_SESSION['wwcmsv2install']['core'])==strtolower('Trinity') or strtolower($_SESSION['wwcmsv2install']['core'])==strtolower('MaNGOS'))
	{
		$enc_pass=sha1(strtoupper($_POST['admin_username'].':'.$_POST['admin_password']));
		$sql1 = mysql_query("SELECT * FROM ". $_SESSION['wwcmsv2install']['logon_db'] .".account WHERE username='".$_POST['admin_username']."' LIMIT 1")or die(mysql_error());
		if (mysql_num_rows($sql1)=='1')//account is found
		{
			//check password
			$sql2=mysql_fetch_assoc($sql1);
			
			if (strtoupper($sql2['sha_pass_hash'])==strtoupper($enc_pass))
			{
				//user if confirmed, add him to website db with admin privilages
				mysql_query("INSERT INTO ". $_SESSION['wwcmsv2install']['web_db'] .".wwc2_users_more (acc_login,vp,userid,dp,gmlevel) VALUES ('".$sql2['username']."','0','".$sql2['id']."','0','4')")or die(mysql_error());
			}
			else
			{
				echo 'User password is wrong.<br><br><input type="button" value="Save" onclick="javascript:checkadmin();return false"></form>';exit;
			}
		}
		else //account is not found, create new one
		{
			mysql_query("INSERT INTO ". $_SESSION['wwcmsv2install']['logon_db'] .".account (username, sha_pass_hash) VALUES ('".$_POST['admin_username']."','".$enc_pass."')") or die(mysql_error());
			$sql3 = mysql_query("SELECT * FROM ". $_SESSION['wwcmsv2install']['logon_db'] .".account WHERE username='".$_POST['admin_username']."' LIMIT 1")or die(mysql_error());
			$sql4=mysql_fetch_assoc($sql3);
			mysql_query("INSERT INTO ". $_SESSION['wwcmsv2install']['web_db'] .".wwc2_users_more (acc_login,vp,userid,dp,gmlevel) VALUES ('".$sql4['username']."','0','".$sql4['id']."','0','az')")or die(mysql_error());
			
		}
	}
	else
	{
		echo "Unknown core, go back to core selection.</form>";exit;
	}
		
		
		
		
		
		
		
	echo '<font color="green">Success!</font><br><br><input name="next" type="submit" value="'.$_GET['l'].' (7/8)"></form>';
}

@mysql_close( $con );
?>
