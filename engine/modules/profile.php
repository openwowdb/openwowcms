<?php
/************************************************************************
*													engine/modules/profile.php
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
if (!defined('PATHROOT'))
	define('PATHROOT', '../../');

if (!class_exists("module_base"))
	include PATHROOT."library/classes/modules/modules.php";

if (!class_exists("profile"))
{
	class profile extends module_base {
		function profile($proccess) {
			$this->proccess = $proccess;
		}

		function canupdateinfo($userid, &$userinfo) {
			global $user, $config, $db, $lang;
			if ($user->userinfo['guid'] != $userid && !$user->isAdmin())
				return;
			?>
			<form action="index.php?page=profile&id=<?php echo $userid; ?>" method="post">
				<table width="100%" border="0" class="profile_advenced">
					<tr><td><?php echo $lang['Avatar']; ?>:</td><td width="70%">
					<select name="avatar" id="avatar" onchange="preview();">
						<?php
						//loop trough modules
						$folder = "engine/res/avatars";
						$handle = @opendir($folder);
						# Making an array containing the files in the current directory:
						$files = array();
						while ($file = @readdir($handle)) $files[] = $file;
						@closedir($handle);
						sort($files);
						#echo the files
						foreach ($files as $file)
						{
							if (strstr($file, ".gif"))
							{
								$file2=substr($file, 0,-4); //without .gif
								$file3=str_replace('_',' ',$file2); //replace "_" with " "
								if ($userinfo['avatar']==$file2)
									echo '<option value="'.$file2.'" selected="selected">'.ucfirst($file3).'</option>';
								else
									echo '<option value="'.$file2.'">'.ucfirst($file3).'</option>';
							}
						}
						?>
					</select></td></tr>
					<tr><td><?php echo $lang['Question']; ?>:</td><td><input type="text" name="question" style="width:95%" maxlength="100" value="<?php echo $userinfo['question']; ?>" /></td></tr>
					<tr><td valign="top"><?php echo $lang['Answer'].' ('.$lang['hidden']; ?>):</td><td><input type="text" name="answer" style="width:95%" maxlength="100" value="<?php if ($user->isAdmin()) echo htmlspecialchars($userinfo['answer']); ?>" /></td></tr>
					<tr><td valign="top"><?php echo $lang['Password'].' ('.$lang['hidden']; ?>):</td><td><input type="text" name="password" style="width:95%" maxlength="100" value="" /></td></tr>
					<?php
					if ($user->isAdmin())
					{
						echo '<tr><td>';

						//get gm level on website
						$gmlevelweb_sql = $db->query("SELECT gmlevel FROM ".TBL_USERS." WHERE acc_login='".$db->escape($userinfo['username'])."' LIMIT 1")or die($db->getLastError());
						$gmlevelweb = $db->getRow($gmlevelweb_sql);
						echo $lang['GM level'].':</td>';
						echo '<td>
							<input type="text" name="gm" style="width:10%" maxlength="50" value="'.$user->getUserGM($userinfo['guid']).'" id="gm" disabled="disabled" />
							<input type="text" id="realm" name="realm" style="width:10%" maxlength="50" value="---" onmouseout="$WowheadPower.hideTooltip();" onmousemove="$WowheadPower.moveTooltip(event)" onmouseover="$WowheadPower.showTooltip(event, \'Realm ID\')" />
							<select style="width:70%" id="gmrealm" name="gmrealm" onchange="javascript:realm112()">
								<option value="---" selected="selected">---</option>
								<option value="">'.$lang['All realms'].'</option>';

						// loop trough realms and get statuses
						$config['engine_char_dbs2'] = explode(';',$config['engine_char_dbs']);
						$i = 1;
						$realm_names2 = explode("|",$config['engine_realmnames']);
						foreach($config['engine_char_dbs2'] as $realms)
						{
							$realm_data = explode("|", $realms);
							if (isset($realm_names2[($i-1)]))
								echo '<option value="'.$i.'">(ID: '.$i.') '.substr($realm_names2[($i-1)],0,15).'</option>';
							$i++;
						}
						echo '</select></td></tr>';
						?>
						<tr><td><?php echo $lang['Web GM level']; ?>:</td><td><input type="text" name="gmweb" style="width:95%" maxlength="50" value="<?php echo $gmlevelweb[0]; ?>" /></td></tr>
						<tr><td><?php echo $lang['Vote Points']; ?></td><td><input type="text" style="width:95%" name="vp" value="<?php echo $userinfo['vp']; ?>"/></td></tr>
						<tr><td><?php echo $lang['Donation Points']; ?></td><td><input type="text" style="width:95%" name="dp" value="<?php echo $userinfo['dp']; ?>"/></td></tr>
						<tr><td><?php echo $lang['Ingame Ban']; ?>:</td>
							<td><select name="bangame">
								<option value="0" <?php if (!$user->usernameBanned($userinfo['username'])) echo 'selected="selected"'; ?>><?php echo $lang['Not Banned']; ?></option>
								<option value="1" <?php if ($user->usernameBanned($userinfo['username'])) echo 'selected="selected"'; ?>><?php echo $lang['Banned']; ?></option>
							</select></td>
						</tr>
						<tr><td><?php echo $lang['Website Ban']; ?>:</td>
							<td><select name="banweb">
								<option value="0" <?php if (!$db->usernameBanned($userinfo['username'])) echo 'selected="selected"'; ?>><?php echo $lang['Not Banned']; ?></option>
								<option value="1" <?php if ($db->usernameBanned($userinfo['username'])) echo 'selected="selected"'; ?>><?php echo $lang['Banned']; ?></option>
							</select></td>
						</tr>
						<?php
					}
					?>
					<tr><td>&nbsp;</td><td><input type="submit" name="submit_profile" value="<?php echo $lang['OK']; ?>"/></td></tr>
				</table><br />
				<?php echo $lang['Allowed Characters']; ?>: <span class="colorgood">A-Z</span>,<span class="colorgood"> 0-9</span>,<span class="colorgood"> _ ; ? ! . , : =</span>
			</form>
			<?php
		}

		function updateinfo() {
			global $user, $db, $config;
			if (!isset($_GET['id'])) return;
			//get info
			$userid = preg_replace( "/[^0-9]/", "", $_GET['id']);
			if ($userid == '') $userid = $user->userinfo['guid'];
			$userinfo = $user->getUserInfo($userid, true);
			//end info

			if ($user->userinfo['guid'] == $userid or $user->isAdmin())
			{
				$avatar = preg_replace( "/[^A-Za-z0-9-() ]/", "", $_POST['avatar']);
				if ($avatar != $userinfo['avatar'])
					$db->updateUserField($userinfo['username'], 'avatar', $avatar);

				$question = preg_replace( "/[^0-9a-zA-Z_;?!.,:= ]/", "", trim($_POST['question']));
				if ($question != $userinfo['question'])
					$db->updateUserField($userinfo['username'], 'question', $question);

				$answer = preg_replace( "/[^0-9a-zA-Z_;?!.,:= ]/", "", trim($_POST['answer']));
				if ($answer != $userinfo['answer'])
					$db->updateUserField($userinfo['username'], 'answer', $answer);

				$pass = preg_replace( "/[^A-Za-z0-9]/", "", trim($_POST['password']));
				if ($pass <> '')
					$user->updatePass($userinfo['username'], $pass);
			}

			//vote and donor points,gm and ban, only admins can change
			if (!$user->isAdmin())
				return;

			$vp = preg_replace( "/[^0-9]/", "", trim($_POST['vp']));
			if ($vp == '') $vp = '0';
			if ($vp != $userinfo['vp'])
				$db->updateUserField($userinfo['username'], 'vp', $vp);

			$dp = preg_replace( "/[^0-9]/", "", trim($_POST['dp']));
			if ($dp == '') $dp = '0';
			if ($dp != $userinfo['dp'])
				$db->updateUserField($userinfo['username'], 'dp', $dp);

			$realm = preg_replace( "/[^0-9-]/", "", trim($_POST['realm']));
			if($realm <> '---'){
				$gm = preg_replace( "/[^0-9a-zA-Z]/", "", trim($_POST['gm']));
				$user->updateGMlevel($userinfo['guid'], $gm,$realm);
			}

			$gmweb = preg_replace( "/[^0-9a-zA-Z]/", "", trim($_POST['gmweb']));
			if ($gmweb != $userinfo['gmlevel'])
				$db->updateUserField($userinfo['username'], 'gmlevel', $gmweb);

			$bangame = preg_replace( "/[^0-9]/", "", trim($_POST['bangame']));
			if ($bangame == '1')
				$user->addIngameBan($userinfo['guid']);
			else
				$user->removeIngameBans($userinfo['guid']);

			$banweb = preg_replace( "/[^0-9]/", "", trim($_POST['banweb']));
			if ($banweb == '1')
				$db->addBan($userinfo['username']);
			else
				$db->removeBan($userinfo['username']);
		}

		function process() {
			global $user, $lang, $config, $db;
			if(!isset($user) || !$user->logged_in) { if (!$this->proccess) echo "<a href='index.php?page=loginout'>".$lang['Login']."</a>"; return; }
			$config['title'] = $lang['Profile']. ' - ' .$config['title'];
			if ($this->proccess == true) {
				global $user, $db, $config;
				if (isset($_POST['submit_profile']))
				{
					$this->updateinfo();
				}
				return parent::process();
			}
			?>
			<!-- This element is important, must be at beginning of module output, dont change it, except module name -->
			<div class="post_body_title"><?php echo $lang['Profile']; ?></div>
			<script type="text/javascript">
			function preview()
			{
				$("#preview").html('<img style="border:solid 1px black" width="64" height="64" src="./engine/res/avatars/'+$("#avatar").val()+'.gif" />');
			}
			function realm112()
			{
				$("#realm").val($("#gmrealm").val());
				$("#gm").removeAttr('disabled');
			}
			</script>
			<?php
			if (!isset($_GET['id'])) $_GET['id'] = false;
			$userid = preg_replace( "/[^0-9]/", "", $_GET['id']);
			if ($userid=='') $userid = $user->userinfo['guid'];
			$userinfo = $user->getUserInfo($userid,true);
			if ($userinfo['username']=='')
			{
				echo 'Unknown or inactive user.';
				return;
			}

			echo '<table width="100%" border="0" class="profile_simple"><tr><td width="64px"><div class="avatar"><img src="'.$user->avatar($userinfo['avatar']).'" /></div></td>
			<td><span id="preview" style="float:right"></span>
			<big>'.$userinfo['username'].'</big><br>'.$lang['GM level'].': '.$userinfo['gmlevel'].'<br>'.$lang['Vote Points'].': '.$userinfo['vp'].'<br>'.$lang['Donation Points'].': '.$userinfo['dp'].'<br>'.$lang['Banned'].': ';

			if( $userinfo['banned']=='0')
				echo '<span class="colorgood">'.$lang['Not Banned']; else echo '<span class="colorbad">'.$lang['Banned'];

			echo '</span><br></td></tr></table>';
			$this->canupdateinfo($userid, $userinfo);
		}
	}
}
$profile = new profile(isset($proccess));
// Accessed via ?page=profile
return $profile->process();
?>
