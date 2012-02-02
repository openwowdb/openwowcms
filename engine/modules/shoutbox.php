<?php
global $user,$db,$form,$lang,$config,$Html;

/**
* This part of website is executed before any output is given
* so every post data is processed here then using Header(Location:)
* we simply call normal site and display errors
**/

/**
* Access premission:
**/
if (isset($user))
{
	if(!isset($user) || !$user->logged_in){ if (!isset($proccess)) echo "<a href='./?page=loginout'>".$lang['Login']."</a>"; return; }

	/**
	* MODULE SELF INSTALLATION:
	**/
	if (!isset($proccess))
	{
		if($Html->moduleinstall('shoutbox',
			array('shoutbox_refresh_time','shoutbox_update_old_time','shoutbox_idle_time'),
			array('30','60','120'),
			array('How often should the shoutbox update (in seconds).','How often should the shoutbox old post time be updated (in seconds, 0 = off)','When should shoutbox stop updating if player has not said anything (in seconds.. 0 = off)'),
			array('CREATE TABLE IF NOT EXISTS '.$config['engine_web_db'].'.mod_shoutbox (`id` bigint(20) NOT NULL AUTO_INCREMENT, `poster` varchar(255) COLLATE latin1_general_ci NOT NULL, `message` text COLLATE latin1_general_ci NOT NULL,`timepost` varchar(100) COLLATE latin1_general_ci NOT NULL,PRIMARY KEY (`id`)) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;') // Sql
			))
			return;
	}
	/**
	* :END MODULE SELF INSTALLATION
	**/

	if(isset($proccess) && $proccess == TRUE){
		/**
		* Processes the user submitted login form, if errors
		* are found, the user is redirected to correct the information,
		* if not, the user is effectively logged in to the system.
		* If user is logged in, he will be logged out and redirected to
		* index.php page.
		*/
		function Process(){
		}
		if (isset($_POST['shoutbox'])){
			/* Initialize process */
			Process();
		}

		/* Reinitilaze 'form' proccess with latest session data */
		$form->_Form();
		return;
	}
	return;
}

define('PATHROOT', "../../");
require(PATHROOT . "engine/init.php");
require(PATHROOT . "engine/func/parser.php");
require(PATHROOT . "engine/func/nicetime.php");

if (isset($_POST['message']))
{
	if($user->logged_in && trim($_POST['message']) <> '')
		$db->query("INSERT INTO ".$config['engine_web_db'].".mod_shoutbox (poster,message,timepost) VALUES ('".$db->escape($user->username)."','".$db->escape($_POST['message'])."','".date("U")."')") or die(mysql_error());
}

if (isset($_POST['dotimeupdate']) && isset($_POST['data']))
{
	$items = explode(";", $_POST['data']);
	echo "<script type='text/javascript'>";
	if (count($items) > 1)
	{
		foreach ($items as $item)
		{
			$subitem = explode("|", $item);
			if (count($subitem) == 2)
			{
				echo "$('#".$subitem[0]."_update').html('(". nicetime($subitem[1]).")');";
			}
		}
	}
	echo "</script>";
}

if (isset($_POST['latest']))
{
	$shouts_sql = $db->query("SELECT * FROM ".$config['engine_web_db'].".mod_shoutbox WHERE id >= " .$db->escape($_POST['latest'])."  ORDER by id DESC") or die(mysql_error());
	$shouts = array();
	while ($row = $db->fetch_array($shouts_sql))
	{
		array_push($shouts, $row);
	}
	if (count($shouts) >= 1)
	{
		foreach ($shouts as $shout)
		{
			echo '<div id="shoutmsg' . $shout['id'] . '">';
			$userinfo=$user->getUserInfo($shout['poster']);
			echo '<img src="'.$user->avatar($userinfo['avatar']).'" height="15px" width="15px" alt="avatar" /> ';
			echo $shout['poster'].": ";
			echo '<font color="#00FFFF">'.$shout['message'].'</font><br> <small class="comments_poster" id="time_'.$shout['id'].'_update">('. nicetime($shout['timepost']).')</small>';
			echo '<var id="time_' . $shout['id'] . '" style="display:none;">'.$shout['timepost'].'</var>';
			echo '</div>';
		}
	}
}
?>
