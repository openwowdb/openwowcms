<?php
interface BaseUser {
	function CoreSQL($id, $param1=false,$param2=false,$param3=false,$param4=false,$param5=false,$param6=false,$param7=false);
	function return_expansion($id);
	function print_Char_Dropdown($accountguid);
	function getUserGM($userid);
	function getUserInfo($username,$userid=false);
	function confirmUserPass($username, $password);
	function usernameTaken($username);
	function usernameBanned($username);
	function convertPass($username,$passwordraw);
	function updatePass($username,$password);
	function updateGMlevel($userid,$value,$realm=false);
	function addNewUser($username, $password, $email);
	function addIngameBan($userguid);
	function removeIngameBans($userguid);
	function sendmail($playername, $playerguid, $subject, $item, $realmid=0, $stack=1, $money=0, $externaltext=false);
}
?>
