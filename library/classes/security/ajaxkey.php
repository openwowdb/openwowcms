<?php
/************************************************************************
*											library/classes/security/ajaxkey.php
*                            -------------------
* 	 Copyright (C) 2011
*
* 	 This package is free software: you can redistribute it and/or modify
*    it under the terms of the GNU General Public License as published by
*    the Free Software Foundation, either version 3 of the License, or
*    (at your option) any later version.
*
* 	 Updated: $Date 2012/02/08 14:00 $
*
************************************************************************/
class ajaxkey {
	public static function challenge($data) {
		return hash_hmac('md5', $data, $_SERVER['SERVER_ADDR']);
	}

	public static function createkey($uid, $expire, $action = -1) {
		$i = ceil(time() / $expire);
		// set key
		if (!isset($_SESSION['ajaxkey']) || !is_array($_SESSION['ajaxkey'])) $_SESSION['ajaxkey'] = array();
		$_SESSION['ajaxkey'][$action] = substr(ajaxkey::challenge($i . $action . $uid), -12, 10);
	}

	public static function verifykey($uid, $expire, $action = -1) {
		$i = ceil(time() / $expire);
		if (!isset($_SESSION['ajaxkey'])) return false;
		if (!isset($_SESSION['ajaxkey'][$action])) return false;

		// verify key
		if(substr(ajaxkey::challenge($i . $action . $uid), -12, 10) == $_SESSION['ajaxkey'][$action]
		|| substr(ajaxkey::challenge(($i - 1) . $action . $uid), -12, 10) == $_SESSION['ajaxkey'][$action])
			return true;
		return false;
	}
}
?>
