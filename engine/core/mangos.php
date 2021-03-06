<?php
###################################################################
# This file is a part of OpenWoW CMS by www.openwow.com
#
#   Project Owner    : OpenWoW CMS (http://www.openwow.com)
#   Copyright        : (c) www.openwow.com, 2010
#   Credits          : Based on work done by AXE and Maverfax
#   License          : GPLv3
##################################################################

############################################################
#  - all returns
#
# MaNGOS Core Specific
#----------------------
#
if (!defined('AXE_db') && !defined('AXE'))
die("No access...");

define("TBL_ACCOUNT", $config['engine_logon_db'].".account");
define("TBL_CHARACTERS", "characters");
define("TBL_BANNED_USERS_SERVER", $config['engine_logon_db'].".account_banned");

class User extends SessionUser
{
/**
* CoreSQL - Returns Core specific SQL string
*/
function CoreSQL($id, $param1=false,$param2=false,$param3=false,$param4=false,$param5=false,$param6=false,$param7=false)
{
global $config,$user,$db;
/**
* Special data fetching:
**/
if ($id=='1' or $id=='2' or $id=='3')
{
$config_data[$id]=explode(";",$config['engine_char_dbs']);
$config_data[$id.'-2']=explode("|",$config_data[$id][$param2]);
}
else
$config_data=false;


$sqls = array(
/**
* Select username and userids:
*  param1 = username;
**/
0 => 'SELECT id,username FROM '.TBL_ACCOUNT.' WHERE username LIKE "%'.$db->escape($param1).'%"',
/**
* Select character info (donation and vote shops):
*  param1 = realm_id (0->); param2 = char_guid;
**/
1 => 'SELECT name,guid,race,class,gender,level,money FROM '.$config_data[$id.'-2'][0].'.'.TBL_CHARACTERS.' WHERE guid="'.$param1.'" AND account="'.$user->userinfo['guid'].'" LIMIT 1',
/**
* Teleport character
**/
2 => 'UPDATE '.$config_data[$id.'-2'][0].'.'.TBL_CHARACTERS.' SET position_x="'.$param3.'", position_y="'.$param4.'", position_z="'.$param5.'", map="'.$param1.'", money="'.$param6.'" WHERE guid="'.$param7.'"',
/**
* Unstuck character
**/
3 => "UPDATE ".$config_data[$id.'-2'][0].'.'.TBL_CHARACTERS." INNER JOIN ".$config_data[$id.'-2'][0].".character_homebind
ON ".TBL_CHARACTERS.".guid = character_homebind.guid AND  ".TBL_CHARACTERS.".guid = '".$param1."'
SET  ".TBL_CHARACTERS.".position_X = character_homebind.position_x,
".TBL_CHARACTERS.".position_Y = character_homebind.position_y,
".TBL_CHARACTERS.".position_z = character_homebind.position_z",
/**
* Expansion change
**/
4 => "UPDATE ".TBL_ACCOUNT." SET expansion='".$param2."' WHERE id='".$param1."'",
);
return $sqls[$id];
}
/**
* return expansion string for id 0-classic, 1-tbc, 2-wotlk, 3-cata
*/
function return_expansion($id)
{
switch ($id) {
case 0:
return "0";
break;
case 1:
return "1";
break;
case 2:
return "2";
break;
case 3:
return "3";
break;
default:
return "2";
}
}
/**
* print_Char_Dropdown
*/
function print_Char_Dropdown($accountguid)
{
global $config,$db;
echo '<select name="character">';
$split_realmname=explode('|',$config['engine_realmnames']);//we have data in array
$split0=explode(';',$config['engine_char_dbs']); //DB1|REALM_PORT|DB1_HOST|DB1_USER|DB1_PASS or DB2|REALM_PORT
foreach ($split0 as $key=>$split00)
{
$split1=explode('|',$split00);//we have data in array


/* loop realms then loop characters */
$db_realmconnector=connect_realm($key);
$q="SELECT name,guid FROM ".$split1[0].".".TBL_CHARACTERS." WHERE account =  '".$accountguid."'";
$a = $db_realmconnector->query($q) or die($db->error('error_msg'));
while ($a2=$db_realmconnector->fetch_array($a)){
echo '<option value="'.$key.'-'.$a2[1].'">'.$split_realmname[$key].' &raquo; '.$a2[0].'</option>';
}
}
echo '</select>';
}

/**
* getUserGM - Returns the result of logged in user
* gm premission value FROM wow accounts!!!
* On error returns 0 witch means
* no premission (default for normal user).
*/
function getUserGM($userid)
{
global $db;
$q = "SELECT gmlevel FROM ".TBL_ACCOUNT." WHERE id = '".$userid."' LIMIT 1";
$result = $db->query($q);
/* Error occurred, return given name by default */
if(!$result || (mysql_numrows($result) < 1))
return 0;
/* Return result array */
$dbarray = mysql_fetch_array($result);
if ($dbarray['gmlevel']=='')
return 0;
return $dbarray['gmlevel'];
}
/**
* getUserInfo - Returns the result array from a mysql
* query asking for all information stored regarding
* the given username. If query fails, NULL is returned.
* To search by wowserver GUID then make second variable true.
*
* List of data returned:
* id as guid
* username as username
* joindate
* last_ip
* online (not fetched)
* expansion
*
* banned
*
* vp
* dp
* question
* answer
* gmlevel (this one overrides gm status in server db)
* avatar
*/
function getUserInfo($username,$userid=false)
{
global $db;
if ($userid)
{
$username = preg_replace( "/[^0-9]/", "", $username );
$q = "SELECT a.id as guid,a.username as username,joindate,last_ip,locked as banned,expansion,vp,dp,question,answer,b.gmlevel,avatar FROM ".TBL_ACCOUNT." a,".TBL_USERS." b WHERE a.id = '".$username."' AND UPPER(b.acc_login)=UPPER(a.username)";
}
else
$q = "SELECT a.id as guid,a.username as username,joindate,last_ip,locked as banned,expansion,vp,dp,question,answer,b.gmlevel,avatar FROM ".TBL_ACCOUNT." a,".TBL_USERS." b WHERE UPPER(a.username) = '".$db->escape(strtoupper($username))."' AND b.acc_login=a.username";


$result = $db->query($q) or die($db->error('error_msg'));
/* Error occurred, return given name by default */
if(!$result || (mysql_numrows($result) < 1)){
return NULL;
}
/* Return result array */
$dbarray = mysql_fetch_array($result);
//check for bans and set $dbarray['banned'] 1-yes or 0-no
if ($this->usernameBanned($dbarray['username'])) $dbarray['banned']='1';
elseif ($db->usernameBanned($dbarray['username'])) $dbarray['banned']='1';
else $dbarray['banned']='0';

//check gm
$dbarray['gm']=$this->getUserGM($dbarray['guid']);
if ($dbarray['gmlevel']=='') $dbarray['gmlevel']=$dbarray['gm'];
unset($dbarray['gm']);

return $dbarray;

}

/**
* confirmUserPass - Checks whether or not the given
* username is in the database, if so it checks if the
* given password is the same password in the database
* for that user. If the user doesn't exist or if the
* passwords don't match up, it returns an error code
* (1 or 2). On success it returns 0.
*/
function confirmUserPass($username, $password){
global $db;
/* Add slashes if necessary (for query) */
if(!get_magic_quotes_gpc()) {
$username = addslashes($username);
}

/* Verify that user is in database */
$q = "SELECT sha_pass_hash FROM ".TBL_ACCOUNT." WHERE username = '$username'";
$result = $db->query($q);
if(!$result || (mysql_numrows($result) < 1)){
return 1; //Indicates username failure
}

/* Retrieve password from result, strip slashes */
$dbarray = mysql_fetch_array($result);
$dbarray[0] = stripslashes($dbarray[0]);
$password = $this->convertPass($username,$password);

/* Validate that password is correct */
if(strtoupper($password) == strtoupper($dbarray[0])){
return 0; //Success! Username and password confirmed
}
else{
return 2; //Indicates password failure
}
}

/**
* usernameTaken - Returns true if the username has
* been taken by another user, false otherwise.
*/
function usernameTaken($username){
global $db;
if(!get_magic_quotes_gpc()){
$username = addslashes($username);
}
$q = "SELECT username FROM ".TBL_ACCOUNT." WHERE username = '".$username."'";
$result = $db->query($q) or die($q);
return ($db->num_rows($result) > 0);
//return false;
}

/**
* usernameBanned - Returns true if the username has
* been banned by INGAME or locked. Function for website ban is
* located in mysql.php
*/
function usernameBanned($username){
global $db;
if(!get_magic_quotes_gpc()){
$username = addslashes($username);
}
$q0 = "SELECT id FROM ".TBL_ACCOUNT." WHERE UPPER(username) = '".strtoupper($username)."'";
$result0 = $db->query($q0) or die($q0);
$result1=$db->fetch_array($result0);

$q = "SELECT id FROM ".TBL_BANNED_USERS_SERVER." WHERE id = '".$result1[0]."' AND active='1'";
$result = $db->query($q) or die($q);
if ($db->num_rows($result) >='1')
{
return true;
}
else
{
$q5 = "SELECT id FROM ".TBL_ACCOUNT." WHERE id = '".$result1[0]."' AND locked='1'";
$result5 = $db->query($q5) or die($q5);
if ($db->num_rows($result5) >='1'){
return true;}
}
return false;
}
/**
* convertPass - Uses raw pass and hash it to correct
* format.
*/
function convertPass($username,$passwordraw)
{
return sha1(strtoupper($username.':'.$passwordraw));
}
/**
* updatePass - Updates user account password.
*/
function updatePass($username,$password)
{
global $db;
$password=$this->convertPass($username,$password);
$q = "UPDATE ".TBL_ACCOUNT." SET sha_pass_hash='".$password."',v='',s='',sessionkey='' WHERE username = '".$db->escape($username)."' LIMIT 1";
$result = $db->query($q) or die($q);
return $result;
}
/**
* updateGMlevel - Updates user account gm level ingame.
*/
function updateGMlevel($userid,$value,$realm=false)
{
global $db;
$userid = preg_replace( "/[^0-9]/", "", $userid );
$q = "UPDATE ".TBL_ACCOUNT." SET gmlevel='".$value."' WHERE id = '".$userid."' LIMIT 1";
$result = $db->query($q) or die($q);
return $result;
}


/**
* addNewUser - Inserts the given (username, password, email)
* info into the database. Appropriate user level is set.
* Returns true on success, false otherwise.
*/
function addNewUser($username, $password, $email){
global $db;
$time = time();

$q = "INSERT INTO ".TBL_ACCOUNT." (username,sha_pass_hash,v,s,email) VALUES ('$username', '".$this->convertPass($username,$password)."', '','', '$email')";
return $db->query($q) or die($q);
}
/**
* addIngameBan - Bans user ingame.
*/
function addIngameBan($userguid)
{
global $db;
$userguid = preg_replace( "/[^0-9]/", "", $userguid );
$q = "UPDATE ".TBL_ACCOUNT." SET locked='1' WHERE id = '".$userguid."' LIMIT 1";
$result = $db->query($q) or die($q);
return $result;
}
/**
* removeIngameBans - Removes all bans from ingame for user.
*/
function removeIngameBans($userguid)
{
global $db;
$userguid = preg_replace( "/[^0-9]/", "", $userguid );
$q = "UPDATE ".TBL_ACCOUNT." SET locked='0' WHERE id = '".$userguid."' LIMIT 1";
$result = $db->query($q) or die($q);
//remove from bans table too:
$q = "DELETE FROM ".TBL_BANNED_USERS_SERVER." WHERE id = '".$userguid."'";
$result2 = $db->query($q) or die(mysql_error().$q);
return true;
}

/**
* sendmail - sendmail function using RA!
* This function sends pure item, no questions ask, all point(MP,DP) reducing, filtering etc
* must be done before this function is called!
* shopid -> 0=vote, 1=donation
* realmid-> 0=first realm in config (engine_char_dbs), 1, 2, etc...
* money  -> 0= no money is sent (this is by default), if money is stated there will be no item send, you have to send seperately
*/
function sendmail($playername, $playerguid, $subject, $item, $realmid=0, $stack=1, $money=0, $externaltext=false)
{
global $config,$db_host,$db;

/**
* VARIABLE FILTERING:
*/
$status=false;
$port_array=explode("|",$config['trinity_ra_port']);
$playername = str_replace(array("\n", "\""), "", $playername);
$subject = preg_replace( "/[^A-Za-z0-9]/", "", str_replace(array("\n", "\""), "", $subject)); //no whitespaces
$item = preg_replace( "/[^0-9]/", "", $item); //item id
$stack = preg_replace( "/[^0-9]/", "", $stack); //number of items
$realmid = preg_replace( "/[^0-9]/", "", $realmid); //item id
if ($item<>'') $item = " ".$item;
$text = str_replace(array("\n", "\""), "", $externaltext);
if (!$text)
$text = "This mail was sent trough website. Timestamp: ".date("U");
$money= preg_replace( "/[^0-9]/", "", $money);
/**
* REALM DETECTION:
*/
$split0=explode(';',$config['engine_char_dbs']); //DB1|REALM_PORT|DB1_HOST|DB1_USER|DB1_PASS or DB2|REALM_PORT
$realm_array=explode('|',$split0[$realmid]);//we have data in array
if (!isset($realm_array[2]))
$realm_host=$db_host;
else
$realm_host=trim($realm_array[2]);
//port:
$realm_soap_port=explode("|",$config['mangos_soap_port']);
/**
* ADMIN USER/PASS:
*/
$soap_userpass=explode("|",$config['mangos_soap_userpass']);
$soap_user=strtoupper($soap_userpass[0]);
$soap_pass=strtoupper($soap_userpass[1]);



/**
* PORT OPENED CHECK:
*/
if (!isset($realm_array[2])) $realm_array[2]='127.0.0.1';

if ($realm_soap_port[$realmid]=='')
return '<span class="colorbad">No port defined on <strong>'.$realm_host.'</strong> host in configuration (config.php &raquo; trinity_ra_port).</span>';
if ($realm_host=='')
return '<span class="colorbad">No host defined in configuration (config.php &raquo; engine_char_dbs or default SQL host).</span>';
$s = @fsockopen($realm_host, $realm_soap_port[$realmid], $ERROR_NO, $ERROR_STR,(float)0.5);
if($s){@fclose($s);} else return '<span class="colorbad">Port <strong>"'.$realm_soap_port[$realmid].'"</strong> on <strong>'.$realm_host.'</strong> is closed.</span><br>Tip: Sometimes DNS name providers block certain ports, you can try using pure IP address as HOST.';

/**
* START:
*/
if ($item<>'' && $item<>'0')//send item
{
$command=".send items ".$playername." \"".$subject."\" \"".$text."\" ".$item."[:".$stack."]";
$mailtext="Mail with item sent! No money was sent.";$moneytext=false;
}
elseif ($money>'0' && $money<>'')//send money
{
$command=".send money ".$playername." \"".$subject."\" \"".$text."\" ".$money."";
$moneytext="Mail with money sent! No item was sent.";$mailtext=false;
}
else //send letter
{
$command=".send mail ".$playername." \"".$subject."\" \"".$text."\"";
$moneytext="Normal Mail sent!";$mailtext=false;
}

/**
* RUN SOAP:
*/
$client = new SoapClient(NULL,
array(
"location" => "http://".$realm_array[2].":".$realm_soap_port[$realmid]."/",
"uri" => "urn:MaNGOS",
"style" => SOAP_RPC,
'login' => $soap_user,
'password' => $soap_pass
));

try {
$result = $client->executeCommand(new SoapParam($command, "command"));
return  "<!-- success --><span class=\"colorgood\">".$mailtext.$moneytext."<br /></span><br />".$status;
}
catch (Exception $e)
{
echo "Command failed on 'http://". $realm_array[2].':'.$realm_soap_port[$realmid]."'!<br>Reason: ";
echo $e->getMessage();
}

}

}

