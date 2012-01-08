<?php
define('PATHROOT', '../../');
include (PATHROOT.'engine/init.php');
include_once(PATHROOT."engine/func/parser.php");
include_once(PATHROOT."engine/func/nicetime.php");
/* EXIT if no premission */
if (strtolower($user->userlevel)<>strtolower($config['premission_admin'])) exit;


if ($_GET['id'] && trim ($_GET['id'])<>'')
{
	$newsid = preg_replace( "/[^0-9]/", "", $_GET['id'] );
	if (isset($_GET['save']))
	{
		$db->query("UPDATE ".$config['engine_web_db'].".wwc2_news SET content='".$db->escape($_POST['message'])."' WHERE id='".$newsid."' LIMIT 1")or die($db->error('error_msg'));
		echo parse_message($_POST['message']);
	}
	elseif(isset($_GET['gettitle']))
	{
		$sql1 = $db->query("SELECT title FROM ".$config['engine_web_db'].".wwc2_news WHERE id='".$newsid."' LIMIT 1")or die($db->error('error_msg'));
		$sql2=$db->fetch_array($sql1);
		echo $sql2[0];
	}
	elseif(isset($_GET['getbody']))
	{
		$sql1 = $db->query("SELECT content FROM ".$config['engine_web_db'].".wwc2_news WHERE id='".$newsid."' LIMIT 1")or die($db->error('error_msg'));
		$sql2=$db->fetch_array($sql1);
		echo $sql2[0];
	}
	elseif(isset($_GET['getbodyparsed']))
	{
		$sql1 = $db->query("SELECT content FROM ".$config['engine_web_db'].".wwc2_news WHERE id='".$newsid."' LIMIT 1")or die($db->error('error_msg'));
		$sql2=$db->fetch_array($sql1);
		echo parse_message($sql2[0]);
	}
	else
	exit;
	
}