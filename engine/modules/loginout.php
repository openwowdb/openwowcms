<?php
/************************************************************************
*													engine/modules/loginout.php
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

global $user, $db, $lang;
/**
* This part of website is executed before any output is given
* so every post data is processed here then using Header(Location:)
* we simply call normal site and display errors
**/
if (isset($proccess) && $proccess == TRUE) {
	/**
	* Processes the user submitted login form, if errors
	* are found, the user is redirected to correct the information,
	* if not, the user is effectively logged in to the system.
	* If user is logged in, he will be logged out and redirected to
	* index.php page.
	*/
	global $config;
	$config['title'] = $lang['Login']. ' - ' .$config['title'];
	function Process() {
		global $user;
		/* Login attempt */
		$retval = $user->login($_POST['user'], $_POST['pass'], isset($_POST['remember']));
		/* Login failed */
		if (!$retval) {
			$_SESSION['value_array'] = $_POST;
			$_SESSION['error_array'] = Form::getErrorArray();
		}
		else
			header("Location: index.php");
	}

	if (isset($_POST['sublogin'])) {
		/* Initialize process */
		Process();
	}
	else
	{
		/* If user is logged in/not logged in, then logout/relogout(for cookie issue) and redirect to index */
		if ($user->logged_in) {
			$user->logout();
			header("Location: index.php");
		}
		else
			$user->logout();
	}

	/* Reinitilaze 'form' proccess with latest session data */
	Form::_Form();
	return;
}
//sql to delete user from wwc2_active_users that have blank name
$db->query("DELETE FROM ".TBL_ACTIVE_USERS." WHERE username=''");
?>
<!-- This element is important, must be at beginning of module output, dont change it, except module name -->
<div class="post_body_title"><?php echo $lang['Login']; ?></div>
<?php
if(Form::$num_errors > 0){
	echo "<font class='colorbad'>".Form::$num_errors." ".$lang['error(s) found']."</font>";
}
?>
<form action="index.php?page=loginout" method="POST">
<table border="0" cellspacing="0" cellpadding="3" class="module_box" width="100%">
	<tr>
		<td><?php echo $lang['Username']; ?>:</td>
		<td><input type="text" name="user" maxlength="30" value="<?php echo Form::value("user"); ?>"></td>
		<td><?php echo Form::error("user"); ?></td>
	</tr>
	<tr>
		<td><?php echo $lang['Password']; ?>:</td>
		<td><input type="password" name="pass" maxlength="30" value="<?php echo Form::value("pass"); ?>"></td>
		<td><?php echo Form::error("pass"); ?></td>
	</tr>
	<tr>
		<td colspan="2" align="left">
			<input type="checkbox" name="remember" <?php if(Form::value("remember") != ""){ echo "checked"; } ?>>
			<font size="2"><?php echo $lang['Remember me next time']; ?>&nbsp;&nbsp;&nbsp;&nbsp;
			<input type="hidden" name="sublogin" value="1">
			<input type="submit" value="<?php echo $lang['Login'];?>">
		</td>
	</tr>
</table>
</form>