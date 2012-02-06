<?php
class ajaxkey {
	public static function challenge($data) {
		return hash_hmac('md5', $data, $_SERVER['SERVER_ADDR']);
	}

	public static function createkey($uid, $expire, $action = -1)
	{
		$i = ceil(time() / $expire);
		// set key
		$_SESSION['ajaxkey'] = substr(ajaxkey::challenge($i . $action . $uid), -12, 10);
	}

	public static function verifykey($uid, $expire, $action = -1)
	{
		$i = ceil(time() / $expire);
		// verify key
		if(substr(ajaxkey::challenge($i . $action . $uid), -12, 10) == $_SESSION['ajaxkey']
		|| substr(ajaxkey::challenge(($i - 1) . $action . $uid), -12, 10) == $_SESSION['ajaxkey'])
			return true;
		return false;
	}
}
?>
