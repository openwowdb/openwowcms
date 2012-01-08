<?php
/**
* Configuration:
*/
$vote_every_hrs=12; //vote every 12 hrs
/**
* End Configuration
*/

define('PATHROOT', '../../');
include (PATHROOT.'engine/init.php');


$id = preg_replace( "/[^0-9]/", "", $_GET['id'] );
/**
* Script needs to do following:
* 	check if user hasn't voted within last 12 hours for specific ID
* 	at end print nothing (link image will dissapear when user click on it)
* 	or print "X", delete old data so there is no stacking.
*/

$sql1 = $db->query("SELECT * FROM ".$config['engine_web_db'].".wwc2_vote_data WHERE siteid = '".$id."' AND timevoted>='".date("U")."' AND (userid='".$user->userinfo['guid']."' OR voteip='".$_SERVER['REMOTE_ADDR']."') LIMIT 1")or die($db->error('error_msg'));//select data that is still under vote ban
if ($db->num_rows($sql1)=='1')
{
	echo "<center><span style='position:absolute' class='colorbad'>X</span></center><img src='./engine/_style_res/".$config['engine_styleid']."/images/voteimg/".$id.".gif' alt='[".$lang['Vote']."]'>";

}
else //there is no data or its expired, vote and insert vote ban then add +1 vp to user
{
	$db->query("INSERT INTO ".$config['engine_web_db'].".wwc2_vote_data (userid,siteid,timevoted,voteip) VALUES ('".$user->userinfo['guid']."','".$id."','".(date("U")+($vote_every_hrs*60*60))."','".$_SERVER['REMOTE_ADDR']."')")or die($db->error('error_msg'));
	$db->updateUserField($user->username, 'vp', ($user->userinfo['vp']+1));
	
}
//delete old expired data
$db->query("DELETE FROM ".$config['engine_web_db'].".wwc2_vote_data WHERE timevoted<='".date("U")."'")or die($db->error('error_msg'));