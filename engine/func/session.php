<?php
/************************************************************************
*													  engine/func/session.php
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

/**
 * This boolean constant controls whether or
 * not the script keeps track of active users
 * and active guests who are visiting the site,
 * use define("TRACK_VISITORS", false); to lower
 * number of DB queryes on every pageload, for slow
 * servers.
 */
if ($config['engine_logusers']=='false')
	define("TRACK_VISITORS", false);
else
	define("TRACK_VISITORS", true);

/**
 * Timeout Constants - these constants refer to
 * the maximum amount of time (in minutes) after
 * their last page fresh that a user and guest
 * are still considered active visitors.
 */
define("USER_TIMEOUT", 10);
define("GUEST_TIMEOUT", 5);

/**
 * Cookie Constants - these are the parameters
 * to the setcookie function call, change them
 * if necessary to fit your website. If you need
 * help, visit www.php.net for more info.
 * <http://www.php.net/manual/en/function.setcookie.php>
 */
define("COOKIE_EXPIRE", 60*60*24*100);  //100 days by default
define("COOKIE_PATH", "/");  //Available in whole domain

define("GUEST_NAME", "Visitor");

define("TBL_USERS", $config['engine_web_db'].".wwc2_users_more");
define("TBL_CONFIG", $config['engine_web_db'].".wwc2_config");
define("TBL_TEMPLATE", $config['engine_web_db'].".wwc2_template");
define("TBL_ACTIVE_USERS", $config['engine_web_db'].".wwc2_active_users");
define("TBL_ACTIVE_GUESTS", $config['engine_web_db'].".wwc2_active_guests");
define("TBL_BANNED_USERS", $config['engine_web_db'].".wwc2_banned_users");
define("TBL_LINKS", $config['engine_web_db'].".wwc2_links");

class SessionUser {
	var $username;     //Username given on sign-up
	var $userid;       //Random value generated on current login
	var $userlevel;    //The level to which the user pertains
	var $time;         //Time user was last active (page loaded)
	var $logged_in;    //True if user is logged in, false otherwise
	var $userinfo = array();  //The array holding all user info
	var $url;          //The page url current being viewed
	var $referrer;     //Last recorded site page viewed
	/**
	 * Note: referrer should really only be considered the actual
	 * page referrer in process.php, any other time it may be
	 * inaccurate.
	 */

	/* Class constructor */
	function Session(){
		$this->time = time();
		$this->startSession();
	}

	/**
	 * startSession - Performs all the actions necessary to
	 * initialize this session object. Tries to determine if the
	 * the user has logged in already, and sets the variables
	 * accordingly. Also takes advantage of this page load to
	 * update the active visitors tables.
	 */
	function startSession(){
		global $db;  //The database connection
		session_start();   //Tell PHP to start the session

		/* Determine if user is logged in */
		$this->logged_in = $this->checkLogin();
		/**
		 * Set guest value to users not logged in, and update
		 * active guests table accordingly.
		 */
		if(!$this->logged_in){
			$this->username = $_SESSION['username'] = GUEST_NAME;
			$this->userlevel = 0;
			$db->addActiveGuest($_SERVER['REMOTE_ADDR'], $this->time);
		}
		/* Update users last active timestamp */
		else{
			$db->addActiveUser($this->username, $this->time);
		}

		/* Remove inactive visitors from database */
		$db->removeInactiveUsers();
		$db->removeInactiveGuests();

		/* Set referrer page */
		if(isset($_SESSION['url'])){
			$this->referrer = $_SESSION['url'];
		}else{
			$this->referrer = "/";
		}

		/* Set current url */
		$this->url = $_SESSION['url'] = $_SERVER['PHP_SELF'];
	}

	/**
	 * checkLogin - Checks if the user has already previously
	 * logged in, and a session with the user has already been
	 * established. Also checks to see if user has been remembered.
	 * If so, the database is queried to make sure of the user's
	 * authenticity. Returns true if the user has logged in.
	 **/
	function checkLogin(){
		global $db;  //The database connection
		/* Check if user has been remembered */
		if(isset($_COOKIE['cookname']) && isset($_COOKIE['cookid'])){
			$this->username = $_SESSION['username'] = $_COOKIE['cookname'];
			$this->userid   = $_SESSION['userid']   = $_COOKIE['cookid'];
		}

		/* Username and userid have been set and not guest */
		if(isset($_SESSION['username']) && isset($_SESSION['userid']) &&
			$_SESSION['username'] != GUEST_NAME){

				/* Confirm that username and userid are valid */
				if($this->confirmUserID($_SESSION['username'], $_SESSION['userid']) != 0){
					/* Variables are incorrect, user not logged in */
					//check if user is not existant in table
					unset($_SESSION['username']);
					unset($_SESSION['userid']);
					return false;
				}

				/* User is logged in, set class variables */
				$this->userinfo  = $this->getUserInfo($_SESSION['username']);
				$this->username  = $this->userinfo['username'];
				$this->userid    = $_SESSION['userid'];
				//$this->userguid  = $this->userinfo['guid'];
				$this->userlevel = $this->userinfo['gmlevel'];

				return true;
			}
			/* User not logged in */
			else{
				return false;
			}
		}
	/**
		 * confirmUserID - Checks whether or not the given
		 * username is in the database, if so it checks if the
		 * given userid is the same userid in the database
		 * for that user. If the user doesn't exist or if the
		 * userids don't match up, it returns an error code
		 * (1 or 2). On success it returns 0.
		 */
	function confirmUserID($username, $userid){
		global $db;
		/* Add slashes if necessary (for query) */
		if(!get_magic_quotes_gpc()) {
			$username = addslashes($username);
		}

		/* Verify that user is in database */
		$q = "SELECT userid FROM ".TBL_USERS." WHERE UPPER(acc_login) = '".strtoupper($username)."'";
		$result = $db->query($q);

		if(!$result || ($db->numRows() < 1)){
			return 1; //Indicates username failure
		}

		/* Retrieve userid from result, strip slashes */
		$dbarray = $db->getRow($result);
		$dbarray[0] = stripslashes($dbarray[0]);
		$userid = stripslashes($userid);

		/* Validate that userid is correct */
		if($userid == $dbarray[0]){
			return 0; //Success! Username and userid confirmed
		}
		else{
			return 2; //Indicates userid invalid
		}
	}
	/**
	 * login - The user has submitted his username and password
	 * through the login form, this function checks the authenticity
	 * of that information in the database and creates the session.
	 * Effectively logging in the user if all goes well.
	 */
	function login($subuser, $subpass, $subremember){
		global $db, $user, $lang;  //The database and form object

		/* Username error checking */
		$field = "user";  //Use field name for username
		if(!$subuser || strlen($subuser = trim($subuser)) == 0){
			Form::setError($field, "* ".$lang['Username']." ".$lang['not entered']);
		}
		else{
			/* Check if username is not alphanumeric */
			if(!ctype_alnum($subuser)){
				Form::setError($field, "* ".$lang['Username']." ".$lang['not alphanumeric']);
			}
			/* Check if username is banned ingame */
			else if($user->usernameBanned($subuser)){
				Form::setError($field, "* ".$lang['Username'].' '.strtolower($lang['Banned']));
			}
			/* Check if username is banned website */
			else if($db->usernameBanned($subuser)){
				Form::setError($field, "* ".$lang['Username'].' '.strtolower($lang['Banned']));
			}
		}

		/* Password error checking */
		$field = "pass";  //Use field name for password
		if(!$subpass){
			Form::setError($field, "* ".$lang['Password']." ".$lang['not entered']);
		}

		/* Return if form errors exist */
		if(Form::$num_errors > 0){
			return false;
		}

		/* Checks that username is in database and password is correct */
		$subuser = stripslashes($subuser);
		$result = $user->confirmUserPass($subuser, $subpass);

		/* Check error codes */
		if($result == 1){
			$field = "user";
			Form::setError($field, "* ".$lang['Username']." not found");
		}
		else if($result == 2){
			$field = "pass";
			Form::setError($field, "* ".$lang['Invalid']." ".strtolower($lang['Password']));
		}

		/* Return if form errors exist */
		if(Form::$num_errors > 0){
			return false;
		}
		/* Insert data to wwc2_users_more if doesnt exists */
		$db->addUser_more($subuser);

		/* Username and password correct, register session variables */
		$this->userinfo  = $user->getUserInfo($subuser);
		$this->username  = $_SESSION['username'] =  $this->userinfo['username'];
		//$this->userguid  = $_SESSION['userguid']   = $this->userinfo['guid'];
		$this->userid    = $_SESSION['userid']   = $this->generateRandID();
		$this->userlevel = $this->userinfo['gmlevel'];


		/* Insert userid into database and update active users table */
		$db->updateUserField($this->username, "userid", $this->userid);
		$db->addActiveUser($this->username, $this->time);
		$db->removeActiveGuest($_SERVER['REMOTE_ADDR']);

		/**
		 * This is the cool part: the user has requested that we remember that
		 * he's logged in, so we set two cookies. One to hold his username,
		 * and one to hold his random value userid. It expires by the time
		 * specified in constants. Now, next time he comes to our site, we will
		 * log him in automatically, but only if he didn't log out before he left.
		 */
		if($subremember){
			setcookie("cookname", $this->username, time()+COOKIE_EXPIRE, COOKIE_PATH);
			setcookie("cookid",   $this->userid,   time()+COOKIE_EXPIRE, COOKIE_PATH);
		}

		/* Login completed successfully */
		return true;
	}

	/**
	 * logout - Gets called when the user wants to be logged out of the
	 * website. It deletes any cookies that were stored on the users
	 * computer as a result of him wanting to be remembered, and also
	 * unsets session variables and demotes his user level to guest.
	 */
	function logout(){

		global $db;  //The database connection
		/**
		 * Delete cookies - the time must be in the past,
		 * so just negate what you added when creating the
		 * cookie.
		 */
		if(isset($_COOKIE['cookname']) && isset($_COOKIE['cookid'])){
			setcookie("cookname", "", time()-COOKIE_EXPIRE, COOKIE_PATH);
			setcookie("cookid",   "", time()-COOKIE_EXPIRE, COOKIE_PATH);
		}

		/* Unset PHP session variables */
		unset($_SESSION['username']);
		unset($_SESSION['userid']);

		/* Reflect fact that user has logged out */
		$this->logged_in = false;

		/**
		 * Remove from active users table and add to
		 * active guests tables.
		 */
		$db->removeActiveUser($this->username);
		$db->addActiveGuest($_SERVER['REMOTE_ADDR'], $this->time);

		/* Set user level to guest */
		$this->username  = GUEST_NAME;
		$this->userlevel = 0;
	}

	/**
	 * register - Gets called when the user has just submitted the
	 * registration form. Determines if there were any errors with
	 * the entry fields, if so, it records the errors and returns
	 * 1. If no errors were found, it registers the new user and
	 * returns 0. Returns 2 if registration failed.
	 */
	function register($subuser, $subpass, $subemail){
		global $db, $user,$lang;//, $mailer;  //The database, form and mailer object

		/* Username error checking */
		$field = "user_name";  //Use field name for username

		if(!$subuser || strlen($subuser = trim($subuser)) == 0){
			Form::setError($field, "* Username not entered");
		}
		else{
			/* Spruce up username, check length */
			$subuser = stripslashes($subuser);
			if(strlen($subuser) < 5){
				Form::setError($field, "* ".$lang['Username']." ".$lang['below 5 characters']."");
			}
			else if(strlen($subuser) > 30){
				Form::setError($field, "* ".$lang['Username']." ".$lang['above 30 characters']."");
			}
			/* Check if username is not alphanumeric */
			else  if(!ctype_alnum($subuser)){
				Form::setError($field, "* ".$lang['Username']." ".$lang['not alphanumeric']."");
			}
			/* Check if username is reserved */
			else if(strcasecmp($subuser, GUEST_NAME) == 0){
				Form::setError($field, "* ".$lang['Username']." ".$lang['reserved word']."");
			}
			/* Check if username is already in use */
			else if($user->usernameTaken($subuser)){
				Form::setError($field, "* ".$lang['Username']." ".$lang['already in use']."");
			}
			/* Check if username is banned */
			else if($user->usernameBanned($subuser)){
				Form::setError($field, "* ".$lang['Username']." ".strtolower($lang['Banned']));
			}
		}

		/* Password error checking */
		$field = "pass_word";  //Use field name for password
		if(!$subpass){
			Form::setError($field, "* ".$lang['Password']." ".$lang['not entered']."");
		}
		else{
			/* Spruce up password and check length*/
			$subpass = stripslashes($subpass);
			if(strlen($subpass) < 5){
				Form::setError($field, "* ".$lang['Password']." ".$lang['below 5 characters']."");
			}
			/* Check if password is not alphanumeric */
			else if(!ctype_alnum($subpass = trim($subpass))){
				Form::setError($field, "* ".$lang['Password']." ".$lang['not alphanumeric']."");
			}
			/**
			 * Note: I trimmed the password only after I checked the length
			 * because if you fill the password field up with spaces
			 * it looks like a lot more characters than 4, so it looks
			 * kind of stupid to report "password too short".
			 */
		}

		/* Email error checking */
		$field = "email";  //Use field name for email
		if(!$subemail || strlen($subemail = trim($subemail)) == 0){
			Form::setError($field, "* Email ".$lang['not entered']);
		}
		else{
			if(!preg_match('/^([a-z0-9])(([-a-z0-9._])*([a-z0-9]))*\@([a-z0-9])*(\.([a-z0-9])([-a-z0-9_-])+)*$/i', $subemail)){
				Form::setError($field, "* Email ".strtolower($lang['Invalid']));
			}
			$subemail = stripslashes($subemail);
		}

		/* Errors exist, have user correct them */
		if(Form::$num_errors > 0){
			return 1;  //Errors with form
		}
		/* No errors, add the new account to the */
		else{
			if($user->addNewUser($subuser, $subpass, $subemail)){
				//if(EMAIL_WELCOME)
				//$mailer->sendWelcome($subuser,$subemail,$subpass);
				return 0;  //New user added succesfully
			}else{
				return 2;  //Registration attempt failed
			}
		}
	}

	function avatar($imagename) {
		if (!file_exists(PATHROOT.'/engine/res/avatars/'.$imagename.'.gif'))
			return './engine/res/avatars/default.gif';
		else
			return './engine/res/avatars/'.$imagename.'.gif';
	}

	/**
	 * isAdmin - Returns true if currently logged in user is
	 * an administrator, false otherwise.
	 */
	function isAdmin(){
		global $config;
		return (strtolower($this->userlevel) == strtolower($config['premission_admin']));
	}

	/**
	 * isGM - Returns true if currently logged in user is
	 * a Game Master, false otherwise.
	 */
	function isGM(){
		global $config;
		return (strtolower($this->userlevel) == strtolower($config['premission_gm']));
	}

	/**
	* generateRandID - Generates a string made up of randomized
	* letters (lower and upper case) and digits and returns
	* the md5 hash of it to be used as a userid.
	*/
	function generateRandID(){
		return md5($this->generateRandStr(16));
	}

	/**
	 * generateRandStr - Generates a string made up of randomized
	 * letters (lower and upper case) and digits, the length
	 * is a specified parameter.
	 */
	function generateRandStr($length){
		$randstr = "";
		for($i=0; $i<$length; $i++){
			$randnum = mt_rand(0,61);
			if($randnum < 10){
				$randstr .= chr($randnum+48);
			}else if($randnum < 36){
				$randstr .= chr($randnum+55);
			}else{
				$randstr .= chr($randnum+61);
			}
		}
		return $randstr;
	}

	function hasVoted($id) {
		global $db, $config;
		$sql1 = $db->query("SELECT * FROM ".$config['engine_web_db'].".wwc2_vote_data WHERE siteid = '".$id."' AND timevoted >= '".@date("U")."' AND (userid = '".$this->userinfo['guid']."' OR voteip = '".$_SERVER['REMOTE_ADDR']."') LIMIT 1")or die($db->getLastError());
		if ($db->numRows() == '1')
			return true;
		return false;
	}
};