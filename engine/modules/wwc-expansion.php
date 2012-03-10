<?php
/************************************************************************
*													engine/modules/wwc-expansion.php
*                            -------------------
* 	 Copyright (C) 2011
*
* 	 This package is free software: you can redistribute it and/or modify
*    it under the terms of the GNU General Public License as published by
*    the Free Software Foundation, either version 3 of the License, or
*    (at your option) any later version.
*
* 	 Updated: $Date 2012/02/09 19:00 $
*
************************************************************************/

//* This part of website is executed before any output is given
//* so every post data is processed here then using Header(Location:)
//* we simply call normal site and display errors

if (!defined('PATHROOT'))
	define('PATHROOT', '../../');

if (!class_exists("module_base"))
	include PATHROOT."library/classes/modules/modules.php";

if (!class_exists("wwc_expansion")) {
	class wwc_expansion extends module_base {

		function wwc_expansion($proccess) {
			$this->proccess = $proccess;
		}

		function Process(){
			global $user, $lang, $db;
			if(!isset($user) || !$user->logged_in) { if (!$this->proccess) echo "<a href='index.php?page=loginout'>".$lang['Login']."</a>"; return; }
			if ($this->DoInstall()) return;
			if ($this->proccess == true) {
				if (isset($_POST['changeexp'])) {
					$expansion = preg_replace("/[^0-9]/", "", $_POST['expansion']);
					if ($user->userinfo['expansion'] == $expansion) return;
					$done = $db->query($user->CoreSQL(4, $user->userinfo['guid'], $user->return_expansion($expansion)));
					if ($done)
						$_SESSION['notice'] ="<center>".$lang['Success']."!<br><br><a href='./?page=wwc-expansion'>".$lang['OK']."</a></center>";
					else
						$_SESSION['notice'] ="<center>".$lang['Fail']."!<br><br><a href='./?page=wwc-expansion'>".$lang['OK']."</a></center>";
				}
				return parent::Process();
			}

			$this->GenerateForm();
		}

		function GenerateForm() {
			global $lang, $user;
			$SelCla = ''; $SelTBC = ''; $SelWOL = ''; $SelCat = '';
			if (isset($user->userinfo['expansion'])) {
				if ($user->userinfo['expansion'] == 0) $SelCla=' Selected';
				elseif ($user->userinfo['expansion'] == 1) $SelTBC=' Selected';
				elseif ($user->userinfo['expansion'] == 2) $SelWOL=' Selected';
				else $SelCat = ' Selected';
			}

			// Notification
			if (isset($_SESSION['notice']) && $_SESSION['notice'] <> '') {
				echo "<div class=\"post_body_title\">NOTICE!</div>".$_SESSION['notice'];
				$_SESSION['notice'] = '';
				return;
			}
			echo '<div class="post_body_title">Expansion</div>
	<center><form method="post">
	<select name="expansion" style="background-image: none; background-color: #333; border-radius: 4px; border: 1px solid #888;">
	<option value="0"'.$SelCla.'>Classic</option>
	<option value="1"'.$SelTBC.'>TBC</option>
	<option value="2"'.$SelWOL.'>WOTLK</option>
	<option value="3"'.$SelCat.'>Cata</option>
	</select>&nbsp;&nbsp;<input name="changeexp" type="submit" value="'.$lang['OK'].'" /></form><br /></center>';
		}
	}
}

$wwc_expansion = new wwc_expansion(isset($proccess));
// Accessed via ?page=wwc_expansion
return $wwc_expansion->Process();
?>