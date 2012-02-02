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
		$exclude_modulenames=explode('|',$config['module_userpanel']);
		if (array_search("shoutbox.php", $exclude_modulenames) == null)
		{
			array_push($exclude_modulenames, 'shoutbox.php');
			$config['module_userpanel'] = implode('|', $exclude_modulenames);
		}
		if($Html->moduleinstall('shoutbox',
			array('shoutbox_refresh_time','shoutbox_idle_time'),
			array('30','120'),
			array('How often should the shoutbox update (in seconds)','When should shoutbox stop updating  (in seconds.. 0 = off)'),
			array('DROP TABLE '.$config['engine_web_db'].'.mod_shoutbox;', 'CREATE TABLE IF NOT EXISTS '.$config['engine_web_db'].'.mod_shoutbox (`id` bigint(20) NOT NULL AUTO_INCREMENT, `poster` varchar(255) COLLATE latin1_general_ci NOT NULL, `message` text COLLATE latin1_general_ci NOT NULL,`timepost` varchar(100) COLLATE latin1_general_ci NOT NULL,PRIMARY KEY (`id`)) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;',
				 "UPDATE ".$config['engine_web_db'].".wwc2_config SET conf_value='".$db->escape($config['module_userpanel'])."' WHERE conf_name='module_userpanel'")
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

if (!function_exists("echo_shout"))
{
	function echo_shout($arr)
	{
		global $user;
		if (count($arr) >= 1)
		{
			foreach ($arr as $shout)
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
}


if (isset($_POST['message']))
{
	if($user->logged_in && trim($_POST['message']) <> '')
	{
		$username = $db->escape($user->username);
		$message = $db->escape($_POST['message']);
		$date = date("U");
		$db->query("INSERT INTO ".$config['engine_web_db'].".mod_shoutbox (poster,message,timepost) VALUES ('".$username."','".$message."','".$date."')") or die(mysql_error());
		$id = mysql_insert_id();
		echo_shout(array(array('id'=>$id, 'poster'=>$username, 'message'=>$message, 'timepost'=>$date)));
	}
}

if (isset($_POST['latest']))
{
	$shouts_sql = $db->query("SELECT * FROM ".$config['engine_web_db'].".mod_shoutbox WHERE id > " .$db->escape($_POST['latest'])."  ORDER by id DESC") or die(mysql_error());
	$shouts = array();
	while ($row = $db->fetch_array($shouts_sql))
	{
		array_push($shouts, $row);
	}
	echo_shout($shouts);
}
?>