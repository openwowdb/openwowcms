<?php
/**
* This part of website is executed before any output is given
* so every post data is processed here then using Header(Location:)
* we simply call normal site and display errors
**/
if (!class_exists("module_base"))
	include $_SERVER["DOCUMENT_ROOT"]. "/library/classes/modules/modules.php";

if (!class_exists("shoutbox"))
{
	class shoutbox extends module_base {
		function shoutbox($proccess) {
			global $config, $db;
			$this->proccess = $proccess;
			$this->configFields = array(
				'shoutbox_refresh_time' => array('30', 'How often should the shoutbox update (in seconds)'),
				'shoutbox_idle_time' => array('120', 'When should shoutbox stop updating  (in seconds.. 0 = off)')
				);
			$this->sqlQueries = array(
				'DROP TABLE '.$config['engine_web_db'].'.mod_shoutbox;',
				'CREATE TABLE '.$config['engine_web_db'].'.mod_shoutbox (`id` bigint(20) NOT NULL AUTO_INCREMENT, `poster` varchar(255) COLLATE latin1_general_ci NOT NULL, `message` text COLLATE latin1_general_ci NOT NULL,`timepost` varchar(100) COLLATE latin1_general_ci NOT NULL,PRIMARY KEY (`id`)) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;',
				);
			$exclude_modulenames=explode('|',$config['module_userpanel']);
				if (array_search("shoutbox.php", $exclude_modulenames) !== false)
			{
				array_push($exclude_modulenames, 'shoutbox.php');
				$config['module_userpanel'] = implode('|', $exclude_modulenames);
				array_push($this->sqlQueries, 'UPDATE '.$config['engine_web_db'].'.wwc2_config SET conf_value="'.$config['module_userpanel'].'" WHERE conf_name="module_userpanel")');
			}
		}

		function process() {
			global $user, $lang;
			if(!isset($user) || !$user->logged_in) { if (!$this->proccess) echo "<a href='./?page=loginout'>".$lang['Login']."</a>"; return; }
			if ($this->DoInstall()) return;
			if ($this->proccess == true) {
				if (isset($_POST['module'])){
					/* do something */
				}
				return parent::process();
			}
		}

		function processAjaxRequest() {
			global $db, $config, $user;
			if (isset($_POST['message']))
			{
				if($user->logged_in && trim($_POST['message']) <> '')
				{
					$username = $db->escape($user->username);
					$message = $db->escape($_POST['message']);
					$date = date("U");
					$db->query("INSERT INTO ".$config['engine_web_db'].".mod_shoutbox (poster,message,timepost) VALUES ('".$username."','".$message."','".$date."')") or die($db->getLastError());
					$id = $db->insertId();
					$this->echo_shout(array('id'=>$id, 'poster'=>$username, 'message'=>$message, 'timepost'=>$date));
				}
			}

			if (isset($_POST['latest']))
			{
				$shouts_sql = $db->query("SELECT * FROM ".$config['engine_web_db'].".mod_shoutbox WHERE id > " .$db->escape($_POST['latest'])."  ORDER by id DESC") or die($db->getLastError());
				while ($row = $db->getRow($shouts_sql))
				$this->echo_shout($row);
			}
		}

		function plugin() {
			global $config, $lang, $user;
			echo "<center><strong>[Shoutbox]</strong></center><br>";
			if(!isset($config['shoutbox_refresh_time']))
			{
				echo 'Shoutbox has not been setup, go to: <a href="?page=shoutbox">?page=shoutbox</a>';
				return;
			}
			echo "<div id='shoutbox'>";
			if ($user->logged_in)
				echo '<center><input type="text" value="" id="shoutout" /><br><input type="button" onclick="shoutbox.addshout()" value="Shout it!" /></center>';
			else
				echo '<center><span style="color: red">Login to make a shoutout!</span></center>';
			echo '<div id="idle" style="display: none;"></div><div id="shoutcontent" style="height:150px;overflow:auto;"></div></div>';
			echo '<script type="text/javascript" src="./engine/js/shoutbox.js"></script>';
			echo '<script type="text/javascript">';
			echo 'shoutbox.lang = '.json_encode($lang).';';
			echo 'shoutbox.refresh_time = '.($config['shoutbox_refresh_time'] * 1000).';';
			echo 'shoutbox.idle_time = '.($config['shoutbox_idle_time'] * 1000).';';
			echo '</script>';
		}

		function echo_shout($arr) {
			global $user;
			if (is_array($arr) && isset($arr[0]) && is_array($arr[0]))
			{
				array_map("self::echo_shout", $arr);
				return;
			}

			echo '<div id="shoutmsg' . $arr['id'] . '">';
			$userinfo = $user->getUserInfo($arr['poster']);
			echo '<img src="'.$user->avatar($userinfo['avatar']).'" height="15px" width="15px" alt="avatar" /> ';
			echo $arr['poster'].": ";
			echo '<font color="#00FFFF">'.$arr['message'].'</font><br> <small class="comments_poster" id="time_'.$arr['id'].'_update">('. nicetime($arr['timepost']).')</small>';
			echo '<var id="time_' . $arr['id'] . '" style="display:none;">'.$arr['timepost'].'</var>';
			echo '</div>';
		}
	}
}

$shoutbox = new shoutbox(isset($proccess));
// Accessed via Plugin
if (isset($isplugin))
{
	$shoutbox->plugin();
	unset($isplugin);
	return;
}

// Accessed via Ajax.post/.get
if ($shoutbox->isAjaxRequest())
{
	define('PATHROOT', "../../");
	include(PATHROOT . "engine/init.php");
	include(PATHROOT . "engine/func/nicetime.php");
	return $shoutbox->processAjaxRequest();
}

// Accessed via ?page=shoutbox
return $shoutbox->process();
?>