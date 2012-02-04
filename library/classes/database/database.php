<?php
/**
 * abstract class database
 *
 * Description for abstract class database
 *
 * @author:
*/

abstract class database  {
	public $prefix;
	public $link_id;
	public $query_result;
	public $num_active_users;		//Number of active users viewing site
	public $num_active_guests;		//Number of active guests viewing site
	public $num_members;			//Number of signed-up users

	public $saved_queries = array();
	public $num_queries = 0;
	public $string_queries = '';
	public $ping = '';

	/**
	 * init - Open database connection based on params
	 *
	 * @param string $hostname
	 * @param string $username
	 * @param string $password
	 * @param string $database
	 * @return bool True if opened, false if failed
	 *
	 */
	abstract function init($hostname, $username, $password, $database = null);

	/**
	 * select_db - Changes database to $database
	 *
	 * @param string $database Database Name
	 * @return void
	 *
	 */
	abstract function select_db($database);

	/**
	 * query - Executes a query
	 *
	 * @param string $sql Query to execute
	 * @param bool $unbuffered No result required
	 * @return mixed Returns false on failure, otherwise result id
	 *
	 */
	abstract function query($sql, $unbuffered = false);

	/**
	 * getArray - Returns the query result as an array
	 *
	 * @return array Array taken from result id
	 *
	 */
	abstract function getArray();

	/**
	 * getRow - Returns the current row
	 *
	 * @param result_id $query_result
	 * @return result_row
	 *
	 */
	abstract function getRow($query_result = null);

	/**
	 * numRows - Returns the number of rows in result id
	 *
	 * @return int Number of rows
	 *
	 */
	abstract function numRows();

	/**
	 * insertId - Get the last insert statement unique id
	 *
	 * @return int
	 *
	 */
	abstract function insertId();

	/**
	 * escape - Make $string database safe
	 *
	 * @param string $string
	 * @return string
	 *
	 */
	abstract function escape($string);

	/**
	 * close - Closes the database connection
	 *
	 * @return void
	 *
	 */
	abstract function close();

	/**
	 * getLastError - Returns the last database error message
	 *
	 * @return string Error Message
	 *
	 */
	abstract function getLastError();

	/**
	 * fatal_error - Exit website and tell the user what happened and where
	 *
	 * @param string $msg
	 * @return void
	 *
	 */
	public function fatal_error($msg) {
		echo "<pre>Error!: $msg\n";
		$bt = debug_backtrace();
		foreach($bt as $line) {
			$args = var_export($line['args'], true);
			echo "{$line['function']}($args) at {$line['file']}:{$line['line']}\n";
		}
		echo "</pre>";
		die();
	}

	/**
	* USER SESSION FUNCTIONS
	**/

	/**
	*  updateUserField - Updates a field, specified by the field
	* parameter, in the user's row of the database.
	*/
	function updateUserField($username, $field, $value) {
		$q = "UPDATE ".TBL_USERS." SET ".$field." = '".$value."' WHERE UPPER(acc_login) = '".$this->escape(strtoupper($username))."'";
		return $this->query($q);
	}

	/**
	* addUserInfo - Adds user to additional website data, for
	* first time login.
	*/
	function addUser_more($username) {
		$this->query("SELECT acc_login FROM ".TBL_USERS." WHERE acc_login='".$this->escape($username)."' LIMIT 1") or die($this->getLastError());
		if ($this->numRows() == 0)
		{
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
	static function usernameBanned($username) {
		global $db;
		if(!get_magic_quotes_gpc()){
			$username = addslashes($username);
		}
		$q = "SELECT username FROM ".TBL_BANNED_USERS." WHERE username = '$username'";
		$db->query($q) or die($db->getLastError());
		return ($db->numRows() > 0);
	}

	/**
	* getNumMembers - Returns the number of signed-up users
	* of the website, banned members not included. The first
	* time the function is called on page load, the database
	* is queried, on subsequent calls, the stored result
	* is returned. This is to improve efficiency, effectively
	* not querying the database when no call is made.
	*/
	function getNumMembers() {
		if($this->num_members <= 0){
			$q = "SELECT count(*) FROM ".TBL_USERS;
			$this->query($q) or die($this->getLastError());
			$arr = $this->getRow();
			$this->num_members = $arr[0];
		}
	}

	/**
	* calcNumActiveUsers - Finds out how many active users
	* are viewing site and sets class variable accordingly.
	*/
	function calcNumActiveUsers() {
		if (!$this->num_active_users){
			/* Calculate number of users at site */
			$q = "SELECT COUNT(*) FROM ".TBL_ACTIVE_USERS;
			$this->query($q) or die($this->getLastError());
			$arr = $this->getRow();
			$this->num_active_users = $arr[0];
		}
		else
			return false;
	}

	/**
	* calcNumActiveGuests - Finds out how many active guests
	* are viewing site and sets class variable accordingly.
	*/
	function calcNumActiveGuests() {
		/* Calculate number of guests at site */
		if (!$this->num_active_guests){
			$q = "SELECT COUNT(*) FROM ".TBL_ACTIVE_GUESTS;
			$this->query($q) or die($this->getLastError());
			$arr = $this->getRow();
			$this->num_active_guests = $arr[0];
		}
		else
			return false;
	}

	/**
	* addActiveUser - Updates username's last active timestamp
	* in the database, and also adds him to the table of
	* active users, or updates timestamp if already there.
	*/
	function addActiveUser($username, $time) {
		$q = "UPDATE ".TBL_USERS." SET timestamp = '$time' WHERE username = '$username'";
		$this->query($q);

		if(!TRACK_VISITORS) return;
		$q = "REPLACE INTO ".TBL_ACTIVE_USERS." VALUES ('$username', '$time')";
		$this->query($q);
		$this->calcNumActiveUsers();
	}

	/* addActiveGuest - Adds guest to active guests table */
	function addActiveGuest($ip, $time) {
		if(!TRACK_VISITORS) return;
		$q = "REPLACE INTO ".TBL_ACTIVE_GUESTS." VALUES ('$ip', '$time')";
		$this->query($q);
		$this->calcNumActiveGuests();
	}

	/* These functions are self explanatory, no need for comments */

	/* removeActiveUser */
	function removeActiveUser($username) {
		if(!TRACK_VISITORS) return;
		$q = "DELETE FROM ".TBL_ACTIVE_USERS." WHERE username = '$username'";
		$this->query($q) ;
		$this->calcNumActiveUsers();
	}

	/* removeActiveGuest */
	function removeActiveGuest($ip) {
		if(!TRACK_VISITORS) return;
		$q = "DELETE FROM ".TBL_ACTIVE_GUESTS." WHERE ip = '$ip'";
		$this->query($q) ;
		$this->calcNumActiveGuests();
	}

	/* removeInactiveUsers */
	function removeInactiveUsers() {
		if(!TRACK_VISITORS) return;
		$timeout = time()-USER_TIMEOUT*60;
		$q = "DELETE FROM ".TBL_ACTIVE_USERS." WHERE timestamp < $timeout";
		$this->query($q) ;
		$this->calcNumActiveUsers();
	}

	/* removeInactiveGuests */
	function removeInactiveGuests() {
		if(!TRACK_VISITORS) return;
		$timeout = time()-GUEST_TIMEOUT*60;
		$q = "DELETE FROM ".TBL_ACTIVE_GUESTS." WHERE timestamp < $timeout";
		$this->query($q) ;
		$this->calcNumActiveGuests();
	}

	static function removeBan($username) {
		global $db;
		$q = "DELETE FROM ".TBL_BANNED_USERS." WHERE username='".$username."'";
		$result = $db->query($q) or die($db->getLastError());
		return $result;
	}

	static function addBan($username){
		global $db;
		$q = "INSERT INTO ".TBL_BANNED_USERS." (username,timestamp) VALUES ('".$username."','".date("U")."')";
		$result = $db->query($q) or die($db->getLastError());
		return $result;
	}
}
?>