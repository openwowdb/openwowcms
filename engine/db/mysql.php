<?php
// Make sure we have built in support for MySQL
if (!function_exists('mysql_connect'))
	exit('This PHP environment doesn\'t have MySQL support built in. MySQL support is required if you want to use a MySQL database to run this site. Consult the PHP documentation for further assistance.');
$db_name='';
/**
 * Database Table Constants - these constants
 * hold the names of all the database tables used
 * in the script.
 */
define("TBL_USERS", $config['engine_web_db'].".wwc2_users_more");
define("TBL_CONFIG", $config['engine_web_db'].".wwc2_config");
define("TBL_TEMPLATE", $config['engine_web_db'].".wwc2_template");
define("TBL_ACTIVE_USERS", $config['engine_web_db'].".wwc2_active_users");
define("TBL_ACTIVE_GUESTS", $config['engine_web_db'].".wwc2_active_guests");
define("TBL_BANNED_USERS", $config['engine_web_db'].".wwc2_banned_users");
define("TBL_LINKS", $config['engine_web_db'].".wwc2_links");


class DBLayer
{
	var $prefix;
	var $link_id;
	var $query_result;
   	var $num_active_users;   //Number of active users viewing site
   	var $num_active_guests;  //Number of active guests viewing site
   	var $num_members;        //Number of signed-up users

	var $saved_queries = array();
	var $num_queries = 0;
	var $string_queries = '';
	var $ping='';


	function DBLayer($db_host, $db_user, $db_pass, $db_name=false)
	{
		$this->link_id = @mysql_connect($db_host, $db_user, $db_pass);
		
		//$this->ping=mysql_ping($this->link_id);
		//ako je connectalo onda:
		if ($this->link_id)
		{
			if ($db_name)
				$this->select_db($db_name);
		}
		else
		{
			trigger_error('Unable to connect to MySQL server. MySQL server is offline or you dont have access to connect to it or username/password is wrong.<br><br></strong><br>Possible problems:<br><blockquote>- SQL server (<strong>'.$db_host.'</strong>) is offline<br>- <strong>'.$db_host.'</strong> server does not allow external connections<br>- SQL User/Pass is wrong</blockquote>', E_USER_WARNING);exit;
		}
	}

	function select_db($dbsel)
	{
		return @mysql_select_db($dbsel, $this->link_id);
	}

	function start_transaction()
	{
		return;
	}


	function end_transaction()
	{
		return;
	}


	function query($sql, $unbuffered = false)
	{
		

		if ($unbuffered)
			$this->query_result = @mysql_unbuffered_query($sql, $this->link_id);
		else
			$this->query_result = @mysql_query($sql, $this->link_id);

		if ($this->query_result)
		{
			++$this->num_queries;
			$this->string_queries .= '<br>'.$sql;

			return $this->query_result;
		}
		else
		{
			return false;
		}
	}


	function result($query_id = 0, $row = 0)
	{
		return ($query_id) ? @mysql_result($query_id, $row) : false;
	}
	
	

	function fetch_assoc($query_id = 0)
	{
		return ($query_id) ? @mysql_fetch_assoc($query_id) : false;
	}
	
	function fetch_array($query_id = 0)
	{
		return ($query_id) ? @mysql_fetch_array($query_id) : false;
	}


	function fetch_row($query_id = 0)
	{
		return ($query_id) ? @mysql_fetch_row($query_id) : false;
	}


	function num_rows($query_id = 0)
	{
		return ($query_id) ? @mysql_num_rows($query_id) : false;
	}


	function affected_rows()
	{
		return ($this->link_id) ? @mysql_affected_rows($this->link_id) : false;
	}


	function insert_id()
	{
		return ($this->link_id) ? @mysql_insert_id($this->link_id) : false;
	}


	function get_num_queries()
	{
		return $this->num_queries;
	}


	function get_saved_queries()
	{
		return $this->saved_queries;
	}


	function free_result($query_id = false)
	{
		return ($query_id) ? @mysql_free_result($query_id) : false;
	}


	function escape($str)
	{
		if (is_array($str))
			return '';
			
		else if (function_exists('mysql_real_escape_string'))
		{
			
				return mysql_real_escape_string($str, $this->link_id);
		}
		else
		{
				return mysql_escape_string($str);
		}
	}


	function error($what=false)
	{
		$result['error_sql'] = @current(@end($this->saved_queries));
		$result['error_no'] = @mysql_errno($this->link_id);
		$result['error_msg'] = @mysql_error($this->link_id);
		
		if($what=='error_sql')
			return $result['error_sql'];	
		if ($what=='error_no')
			return $result['error_no'];
		if ($what=='error_msg')
			return $result['error_msg'];
		else
			return $result;
	}


	function close()
	{
		if ($this->link_id)
			@mysql_close($this->link_id);
		return false;
	}
   /**
	* USER SESSION FUNCTIONS
	**/

   /**
    *  updateUserField - Updates a field, specified by the field
    * parameter, in the user's row of the database.
    */
   function updateUserField($username, $field, $value){
      $q = "UPDATE ".TBL_USERS." SET ".$field." = '".$value."' WHERE UPPER(acc_login) = '".$this->escape(strtoupper($username))."'";
      return $this->query($q);
   }
   
   /**
    * addUserInfo - Adds user to additional website data, for 
	* first time login.
    */
   function addUser_more($username)
   {
   	
		$result = $this->query("SELECT acc_login FROM ".TBL_USERS." WHERE acc_login='".$this->escape($username)."' LIMIT 1") or die(mysql_error());
		if ($this->num_rows($result)==0){
		
			$q = "INSERT INTO ".TBL_USERS." (acc_login,vp,dp) VALUES ('".$this->escape(strtoupper($username))."','0','0')";
			
			 return $this->query($q);
		}
		else
			return false;
   }
	/**
    * usernameBanned - Returns true if the username has
    * been banned by WEBSITE. Function for INGAME ban is
	* located in <corename>.php
    */
	function usernameBanned($username){
    global $db;
      if(!get_magic_quotes_gpc()){
         $username = addslashes($username);
      }
      $q = "SELECT username FROM ".TBL_BANNED_USERS." WHERE username = '$username'";
	
      $result = $db->query($q) or die(mysql_error());
      return ($db->num_rows($result) > 0);
   }
   
   /**
    * getNumMembers - Returns the number of signed-up users
    * of the website, banned members not included. The first
    * time the function is called on page load, the database
    * is queried, on subsequent calls, the stored result
    * is returned. This is to improve efficiency, effectively
    * not querying the database when no call is made.
    */
   function getNumMembers(){
      if($this->num_members <= 0){
         $q = "SELECT count(*) FROM ".TBL_USERS;
         $result = $this->query($q) or die(mysql_error());
		 $a=$this->fetch_array($result);
         $this->num_members = $a[0];
      }
   }
   
   /**
    * calcNumActiveUsers - Finds out how many active users
    * are viewing site and sets class variable accordingly.
    */
   function calcNumActiveUsers(){
   	if (!$this->num_active_users){
      /* Calculate number of users at site */
      $q = "SELECT COUNT(*) FROM ".TBL_ACTIVE_USERS;
      $result = $this->query($q) or die(mysql_error());
	   $result2= $this->fetch_array($result);
      $this->num_active_users = $result2[0];
	  }
	  else
	  return false;
   }
   
   /**
    * calcNumActiveGuests - Finds out how many active guests
    * are viewing site and sets class variable accordingly.
    */
   function calcNumActiveGuests(){
      /* Calculate number of guests at site */
	  if (!$this->num_active_guests){
      $q = "SELECT COUNT(*) FROM ".TBL_ACTIVE_GUESTS;
      $result = $this->query($q) or die(mysql_error());
	   $result2= $this->fetch_array($result);
      $this->num_active_guests = $result2[0];
	  } 
	  else
	  return false;
   }
   
   /**
    * addActiveUser - Updates username's last active timestamp
    * in the database, and also adds him to the table of
    * active users, or updates timestamp if already there.
    */
   function addActiveUser($username, $time){
      $q = "UPDATE ".TBL_USERS." SET timestamp = '$time' WHERE username = '$username'";
      $this->query($q) ;
      
      if(!TRACK_VISITORS) return;
      $q = "REPLACE INTO ".TBL_ACTIVE_USERS." VALUES ('$username', '$time')";
      $this->query($q) ;
      $this->calcNumActiveUsers();
   }
   
   /* addActiveGuest - Adds guest to active guests table */
   function addActiveGuest($ip, $time){
      if(!TRACK_VISITORS) return;
      $q = "REPLACE INTO ".TBL_ACTIVE_GUESTS." VALUES ('$ip', '$time')";
      $this->query($q);
      $this->calcNumActiveGuests();
   }
   
   /* These functions are self explanatory, no need for comments */
   
   /* removeActiveUser */
   function removeActiveUser($username){
      if(!TRACK_VISITORS) return;
      $q = "DELETE FROM ".TBL_ACTIVE_USERS." WHERE username = '$username'";
      $this->query($q) ;
      $this->calcNumActiveUsers();
   }
   
   /* removeActiveGuest */
   function removeActiveGuest($ip){
      if(!TRACK_VISITORS) return;
      $q = "DELETE FROM ".TBL_ACTIVE_GUESTS." WHERE ip = '$ip'";
      $this->query($q) ;
      $this->calcNumActiveGuests();
   }
   
   /* removeInactiveUsers */
   function removeInactiveUsers(){
      if(!TRACK_VISITORS) return;
      $timeout = time()-USER_TIMEOUT*60;
      $q = "DELETE FROM ".TBL_ACTIVE_USERS." WHERE timestamp < $timeout";
      $this->query($q) ;
      $this->calcNumActiveUsers();
   }

   /* removeInactiveGuests */
   function removeInactiveGuests(){
      if(!TRACK_VISITORS) return;
      $timeout = time()-GUEST_TIMEOUT*60;
      $q = "DELETE FROM ".TBL_ACTIVE_GUESTS." WHERE timestamp < $timeout";
      $this->query($q) ;
      $this->calcNumActiveGuests();
   }
	function removeBan($username){
		global $db;
		$q = "DELETE FROM ".TBL_BANNED_USERS." WHERE username='".$username."'";
		$result = $db->query($q) or die(mysql_error());
		return $result;
	}
	function addBan($username){
		global $db;
		$q = "INSERT INTO ".TBL_BANNED_USERS." (username,timestamp) VALUES ('".$username."','".date("U")."')";
		$result = $db->query($q) or die(mysql_error());
		return $result;
	} 
}
$db = new DBLayer($db_host, $db_user, $db_pass);//indicates connection to website DB and WOWACCOUNT DB

function connect_realm($id) //id -> from 0 to infinity
{
	global $config,$db;
	#check char db and assign connector or connect
	#DB1|REALM_PORT|DB1_HOST|DB1_USER|DB1_PASS;DB2|REALM_PORT;etc...
	$split0=explode(';',$config['engine_char_dbs']); //DB1|REALM_PORT|DB1_HOST|DB1_USER|DB1_PASS or DB2|REALM_PORT
	$split1=explode('|',$split0[$id]);//we have data in array
	
	#check if its on localhost or remote server:
	if (!isset($split1[2]))//its on localhost, apply connector now
		return $db;
	else
		return new DBLayer(trim($split1['2']), trim($split1['3']), trim($split1['4']), trim($split1['0']));
}
