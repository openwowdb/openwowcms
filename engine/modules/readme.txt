Please do not change module names, it may lead to error. 

Filename format: <unique_designation>-<module_name>.php

MODULE TEMPLATE
<?php
global $user,$db,$lang,$config;

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
	function Process(){
		global $user;

		/* Login attempt */
		$retval = $user->login($_POST['user'], $_POST['pass'], isset($_POST['remember']));

		if (!$retval) {											// Login Failed
			$_SESSION['value_array'] = $_POST;
			$_SESSION['error_array'] = Form::getErrorArray();
		}
		else													// Login Success
			header("Location: index.php");
	}

	if (isset($_POST['sublogin'])) {
		/* Initialize process */
		Process();
	}
	else
	{
		// add code if any
	}

	/* Reinitilaze 'form' proccess with latest session data */
	Form::_Form();
	return;
}
?>

<!-- This element is important, must be at beginning of module output, dont change it, except module name -->
<div class="post_body_title">MODULE NAME</div>
HTML CODE
<?php
//php code here, or HTML CODE
?>
HTML CODE
