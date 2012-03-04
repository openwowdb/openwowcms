<?php
/************************************************************************
*														engine/core/arcemu.php
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

############################################################
#  - all returns
#
# ArcEmu Core Specific
#----------------------
#
if (!defined('AXE_db') && !defined('AXE'))
	die("No access...");

define("TBL_ACCOUNT", $config['engine_logon_db'].".accounts");
define("TBL_CHARACTERS", "characters");

class User extends SessionUser implements BaseUser
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
			0 => 'SELECT acct as id,login as username FROM '.TBL_ACCOUNT.' WHERE login LIKE "%'.$db->escape($param1).'%"',
			/**
			* Select character info (donation and vote shops, teleporter):
			*  param1 = realm_id (0->); param2 = char_guid;
			**/
			1 => 'SELECT name,guid,race,class,gender,level,gold as money FROM '.$config_data[$id.'-2'][0].'.'.TBL_CHARACTERS.' WHERE guid="'.$param1.'" AND acct="'.$user->userinfo['guid'].'" LIMIT 1',
			/**
			* Teleport character
			**/
			2 => 'UPDATE '.$config_data[$id.'-2'][0].'.'.TBL_CHARACTERS.' SET positionX="'.$param3.'", positionY="'.$param4.'", positionZ="'.$param5.'", mapid="'.$param1.'", gold="'.$param6.'" WHERE guid="'.$param7.'"',
			/**
			* Unstuck character
			**/
			3 => 'UPDATE '.$config_data[$id.'-2'][0].'.'.TBL_CHARACTERS.' SET positionX = bindpositionX, positionY = bindpositionY, positionZ = bindpositionZ, mapId = bindmapId, zoneId = bindzoneId, deathstate = 0 WHERE guid = "'.$param1.'" LIMIT 1',
			/**
			* Expansion change, 0 to 3
			**/
			4 => "UPDATE ".TBL_ACCOUNT." SET flags='".$param2."' WHERE acct='".$param1."'",
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
				return "8";
				break;
			case 2:
				return "24";
				break;
			case 3:
				return "32";
				break;
			default:
				return "24";
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
			$q="SELECT name,guid FROM ".$split1[0].".".TBL_CHARACTERS." WHERE acct =  '".$accountguid."'";
			$a = $db_realmconnector->query($q) or die($db->getLastError());
			while ($a2=$db_realmconnector->getRow($a)){
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
		$q = "SELECT gm FROM ".TBL_ACCOUNT." WHERE acct = '".$userid."' LIMIT 1";
		$result = $db->query($q);
		/* Error occurred, return given name by default */
		if(!$result || ($db->numRows() < 1))
			return 0;
		/* Return result array */
		$dbarray = $db->getRow($result);
		if ($dbarray['gm']=='')
			return 0;
		return $dbarray['gm'];
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
	* banned (0=no, other=yes)
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
			$username = preg_replace( "/[^0-9]/", "", $username );//this is GUID
			$q = "SELECT a.acct as guid,a.login as username,a.lastlogin,a.lastip,a.banned as banned,a.flags as expansion,b.vp,b.dp,b.question,b.answer,b.gmlevel,b.avatar".
				 " FROM ".TBL_ACCOUNT." a,".TBL_USERS." b WHERE a.acct = '".$username."' AND b.acc_login=a.login";
		}
		else
			$q = "SELECT a.acct as guid,a.login as username,lastlogin,lastip,banned as banned,a.flags as expansion,b.vp,b.dp,b.question,b.answer,b.gmlevel,b.avatar".
				 " FROM ".TBL_ACCOUNT." a,".TBL_USERS." b WHERE UPPER(a.login) = '".$db->escape(strtoupper($username))."' AND b.acc_login=a.login";


		$result = $db->query($q) or die($db->getLastError());

		/* Error occurred, return given name by default */
		if(!$result || ($db->numRows() < 1)){
			return NULL;
		}
		/* Return result array */
		$dbarray = $db->getRow($result);
		//check for bans and set $dbarray['banned'] 1-yes or 0-no
		if ($this->usernameBanned($dbarray['username'])) $dbarray['banned']='1';
		elseif ($db->usernameBanned($dbarray['username'])) $dbarray['banned']='1';
		else $dbarray['banned']='0';

		//check gm
		$dbarray['gm']=$this->getUserGM($dbarray['guid']);
		if ($dbarray['gmlevel']=='') $dbarray['gmlevel']=$dbarray['gm'];
		unset($dbarray['gm']);
		//ban compatibility fix
		if($dbarray['banned']=='' or $dbarray['banned']=='0') $dbarray['banned']='0';
		else
			$dbarray['banned']='0';
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
	function confirmUserPass($username, $password){//TODO
		global $db;
		/* Add slashes if necessary (for query) */
		if(!get_magic_quotes_gpc()) {
			$username = addslashes($username);
		}

		/* Verify that user is in database */
		$q = "SELECT password FROM ".TBL_ACCOUNT." WHERE login = '$username'";
		$result = $db->query($q);
		if(!$result || ($db->numRows() < 1)){
			return 1; //Indicates username failure
		}

		/* Retrieve password from result, strip slashes */
		$dbarray = $db->getRow($result);
		$dbarray[0] = stripslashes($dbarray[0]);
		$password = $this->convertPass($username,$password);

		/* Validate that password is correct */
		if($password == $dbarray[0]){
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
		$q = "SELECT login FROM ".TBL_ACCOUNT." WHERE login = '".$username."'";
		$result = $db->query($q) or die($q);
		return ($db->numRows() > 0);
		//return false;
	}

	/**
	* usernameBanned - Returns true if the username has
	* been banned by INGAME. Function for website ban is
	* located in mysql.php
	*/
	function usernameBanned($username){
		global $db;
		if(!get_magic_quotes_gpc()){
			$username = addslashes($username);
		}
		$q0 = "SELECT acct FROM ".TBL_ACCOUNT." WHERE UPPER(login) = '".strtoupper($username)."' AND banned='1'";
		$result = $db->query($q0) or die($q0);
		return ($db->numRows() > 0);
	}
	/**
	* convertPass - Uses raw pass and hash it to correct
	* format.
	*/
	function convertPass($username,$passwordraw)
	{
		return $passwordraw;
	}
	/**
	* updatePass - Updates user account password.
	*/
	function updatePass($username,$password)
	{
		global $db;
		$password=$this->convertPass($username,$password);
		$q = "UPDATE ".TBL_ACCOUNT." SET password='".$password."',encrypted_password='' WHERE login = '".$db->escape($username)."' LIMIT 1";
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
		$q = "UPDATE ".TBL_ACCOUNT." SET gm='".$value."' WHERE acct = '".$userid."' LIMIT 1";
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

		$q = "INSERT INTO ".TBL_ACCOUNT." (login,password,email) VALUES ('$username', '".$this->convertPass($username,$password)."','$email')";
		return $db->query($q) or die($q.'<br>'.$db->getLastError());
	}
	/**
	* addIngameBan - Bans user ingame.
	*/
	function addIngameBan($userguid)
	{
		global $db;
		$userguid = preg_replace( "/[^0-9]/", "", $userguid );
		$q = "UPDATE ".TBL_ACCOUNT." SET banned='1' WHERE acct = '".$userguid."' LIMIT 1";
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
		$q = "UPDATE ".TBL_ACCOUNT." SET banned='0' WHERE acct = '".$userguid."' LIMIT 1";
		$result = $db->query($q) or die($q);
		return $result;
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
		@set_time_limit(20);

		/**
		* VARIABLE FILTERING:
		*/
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
		* REALM CONNECTION:
		*/
		$split0=explode(';',$config['engine_char_dbs']); //DB1|REALM_PORT|DB1_HOST|DB1_USER|DB1_PASS or DB2|REALM_PORT
		$realm_array=explode('|',$split0[$realmid]);//we have data in array

		$db2=connect_realm($realmid);
		/**
		* START SCRIPT:
		*/
		$query_string="INSERT INTO ".trim($realm_array[0]).".mailbox_insert_queue(sender_guid, receiver_guid, subject, body, stationary, money, item_id, item_stack) VALUES ('".$playerguid."', '".$playerguid."', '".$db->escape($subject)."', '".$db->escape($text)."', '61', '".$money."', '".$item."', '".$stack."')";
		//echo $query_string;
		$sendmail=false;
		$db2->query($query_string) or ($sendmail=$db->getLastError());
		if($sendmail==false)
			return  "<!-- success --><span class=\"colorgood\">Mail is sent! <br>All done!</span>";
		else
			return  "<span class=\"colorbad\">Mail is not sent! Error returned: ".$sendmail."<br>"."INSERT INTO mailbox_insert_queue(sender_guid, receiver_guid, subject, body, stationary, money, item_id, item_stack) VALUES ('".$playerguid."', '".$playerguid."', '".$db->escape($subject)."', '".$db->escape($text)."', '61', '".$money."', '".$item."','".$stack."')"."</span>";

		/**
		* CLOSE REALM SQL CONNECTION:
		*/
		$db2->close();
	}



}

