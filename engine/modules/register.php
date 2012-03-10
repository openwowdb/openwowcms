<?php
/************************************************************************
*													engine/modules/register.php
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
if(isset($proccess) && $proccess == TRUE)
{
	/**
	* Processes the user submitted registration form,
	* if errors are found, the user is redirected to correct the
	* information, if not, the user is effectively registered with
	* the system and an email is (optionally) sent to the newly
	* created user.
	*/
	global $config;
	$config['title'] = $lang['Register']. ' - ' .$config['title'];

	function Process() {
		global $user;
		/* Registration attempt */
		$retval = $user->register($_POST['user_name'], $_POST['pass_word'], $_POST['email']);

		/* Registration Successful */
		if ($retval == 0) {
			$_SESSION['reguname'] = $_POST['user_name'];
			$_SESSION['regsuccess'] = true;

		}
		/* Error found with form */
		else if ($retval == 1) {
			$_SESSION['value_array'] = $_POST;
			$_SESSION['error_array'] = Form::getErrorArray();
		}
		/* Registration attempt failed */
		else if ($retval == 2) {
			$_SESSION['reguname'] = $_POST['user_name'];
			$_SESSION['regsuccess'] = false;
		}
	}

	/* Initialize process */
	if (isset($_POST['subjoin'])) Process();

	/* Reinitilaze 'form' proccess with latest session data */
	Form::_Form();
	return;
}
?>
<!-- This element is important, must be at beginning of module output, dont change it, except module name -->
<div class="post_body_title"><?php echo $lang['Register']; ?></div>
<?php
/**
 * The user is already logged in, not allowed to register.
 */
if ($user->logged_in) {
	echo "<h1>".$lang['Fail']."</h1>";
	echo "<p><b>$user->username</b> ".$lang["you've already registered"].".</p>";
	return;
}
/**
 * The user has submitted the registration form and the
 * results have been processed.
 */
else if (isset($_SESSION['regsuccess'])) {
	/* Registration was successful */
	if ($_SESSION['regsuccess']) {
		echo "<h1>".$lang['Success']."</h1>";
		echo "<p>".$lang['Thank you']." <b>".$_SESSION['reguname']."</b>, ".$lang['your information has been added'].".</p>";
	}
	/* Registration failed */
	else {
		echo "<h1>".$lang['Fail']."</h1>";
		echo "<p>".$lang['Error has occurred and your registration for the']." ".strtolower($lang['Username'])." <b>".$_SESSION['reguname']."</b></p>";
	}
	unset($_SESSION['regsuccess']);
	unset($_SESSION['reguname']);
	return;
}

/**
 * The user has not filled out the form yet.
 * Below is the page with the sign-up form, the names
 * of the input fields are important and should not
 * be changed.
 */
if (Form::$num_errors > 0) {
	echo "<font color=\"#ff0000\">".Form::$num_errors." ".$lang['error(s) found']."</font>";
}
?>
<form action="index.php?page=register" method="POST">
	<table  border="0" cellspacing="0" cellpadding="3">
		<tr>
			<td><?php echo $lang['Username']; ?>:</td><td><input type="text" name="user_name" maxlength="30" value="<?php echo Form::value("user_name"); ?>"></td>
			<td><?php echo Form::error("user_name"); ?></td>
		</tr>
		<tr>
			<td><?php echo $lang['Password']; ?>:</td><td><input type="password" name="pass_word" maxlength="30" value="<?php echo Form::value("pass_word"); ?>"></td>
			<td><?php echo Form::error("pass_word"); ?></td>
		</tr>
		<tr>
			<td>Email:</td><td><input type="text" name="email" maxlength="50" value="<?php echo Form::value("email"); ?>"></td>
			<td><?php echo Form::error("email"); ?></td>
		</tr>
		<tr>
			<td colspan="2" align="right">
				<input type="hidden" name="subjoin" value="1">
				<input type="submit" value="<?php echo $lang['OK']; ?>">
			</td>
		</tr>
		<tr><td colspan="2" align="left"></td></tr>
	</table>
</form>