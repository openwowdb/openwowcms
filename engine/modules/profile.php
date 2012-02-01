<?php
global $user,$db,$form,$lang,$config;

/**
* Access premission:
**/
if(!$user->logged_in){ if (!isset($proccess)) echo "<a href='./?page=loginout'>".$lang['Login']."</a>"; return; }
/**
* This part of website is executed before any output is given
* so every post data is processed here then using Header(Location:)
* we simply call normal site and display errors
**/
if(isset($proccess) && $proccess == TRUE){
	/**
    * Processes the user submitted login form, if errors
    * are found, the user is redirected to correct the information,
    * if not, the user is effectively logged in to the system.
	* If user is logged in, he will be logged out and redirected to 
	* index.php page.
    */
	global $config;
	$config['title']=$lang['Profile']. ' - ' .$config['title'];
	
	function Process(){
	   global $user, $form,$db,$config;
	   //get info
	   $userid = preg_replace( "/[^0-9]/", "", $_GET['id'] );
		if ($userid=='') $userid=$user->userinfo['guid'];
		$userinfo=$user->getUserInfo($userid,true);
		//end info
		if ($user->userinfo['guid']==$userid or strtolower($user->userinfo['gmlevel'])==strtolower($config['premission_admin']))
		{
			$avatar = preg_replace( "/[^A-Za-z0-9-() ]/", "", $_POST['avatar'] );
			$db->updateUserField($userinfo['username'], 'avatar', $avatar);
			
			$question = preg_replace( "/[^0-9a-zA-Z_;?!.,:= ]/", "", trim($_POST['question']) );
			$db->updateUserField($userinfo['username'], 'question', $question);
			
			$answer = preg_replace( "/[^0-9a-zA-Z_;?!.,:= ]/", "", trim($_POST['answer']) );
			if ($answer<>'')
			$db->updateUserField($userinfo['username'], 'answer', $answer);
			
			$pass = preg_replace( "/[^A-Za-z0-9]/", "", trim($_POST['password']) );
			if ($pass<>'')
			$user->updatePass($userinfo['username'], $pass);
		}
		if (strtolower($user->userinfo['gmlevel'])==strtolower($config['premission_admin']))
		{
			//vote and donor points,gm and ban, only admins can change
			$vp = preg_replace( "/[^0-9]/", "", trim($_POST['vp']) );
			if ($vp=='') $vp='0'; 
			$db->updateUserField($userinfo['username'], 'vp', $vp);
			$dp = preg_replace( "/[^0-9]/", "", trim($_POST['dp']) );
			if ($dp=='') $dp='0'; 
			$db->updateUserField($userinfo['username'], 'dp', $dp);
			
			$realm=preg_replace( "/[^0-9-]/", "", trim($_POST['realm']) );
			if($realm<>'---'){
			$gm=preg_replace( "/[^0-9a-zA-Z]/", "", trim($_POST['gm']) );
			$user->updateGMlevel($userinfo['guid'], $gm,$realm);
			}
			
			$gmweb = preg_replace( "/[^0-9a-zA-Z]/", "", trim($_POST['gmweb']) );
			$db->updateUserField($userinfo['username'], 'gmlevel', $gmweb);
			
			$bangame = preg_replace( "/[^0-9]/", "", trim($_POST['bangame']) );
			if ($bangame=='1')
				$user->addIngameBan($userinfo['guid']);
			else
				$user->removeIngameBans($userinfo['guid']);
			
			$banweb = preg_replace( "/[^0-9]/", "", trim($_POST['banweb']) );
			if ($banweb=='1')
				$db->addBan($userinfo['username']);
			else
				$db->removeBan($userinfo['username']);
			
		}
		
	}
	
	if (isset($_POST['submit'])){
		/* Initialize process */
		Process();
	}
	else
	{
		//add code if any
	}
	
	/* Reinitilaze 'form' proccess with latest session data */
	$form->_Form();
	
	return;
	
}

?>
<!-- This element is important, must be at beginning of module output, dont change it, except module name -->
<div class="post_body_title"><?php echo $lang['Profile']; ?></div>
<script type="text/javascript">
function preview()
{
	document.getElementById("preview").innerHTML='<img style="border:solid 1px black" width="64" height="64" src="./engine/res/avatars/'+document.getElementById("avatar").value+'.gif" />';
}
function realm112()
{
	document.getElementById("realm").value=document.getElementById("gmrealm").value;
	document.getElementById("gm").disabled=false;
}
</script>
<?php
if (!isset($_GET['id'])) $_GET['id']=false;
$userid = preg_replace( "/[^0-9]/", "", $_GET['id'] );
if ($userid=='') $userid=$user->userinfo['guid'];
$userinfo=$user->getUserInfo($userid,true);
if ($userinfo['username']=='') {echo 'Unknown or inactive user.'; return; }

echo '<table width="100%" border="0" class="profile_simple">
  <tr>
    <td width="64px"><div class="avatar"><img src="'.$user->avatar($userinfo['avatar']).'" /></div></td>
    <td><span id="preview" style="float:right"></span>
		<big>'.$userinfo['username'].'</big><br>
		'.$lang['GM level'].': '.$userinfo['gmlevel'].'<br>
		'.$lang['Vote Points'].': '.$userinfo['vp'].'<br>
		'.$lang['Donation Points'].': '.$userinfo['dp'].'<br>
		'.$lang['Banned'].': ';
		if( $userinfo['banned']=='0') echo '<span class="colorgood">'.$lang['Not Banned']; else echo '<span class="colorbad">'.$lang['Banned'];
		echo '</span><br>
	</td>
  </tr>
</table>';
if ($user->userinfo['guid']==$userid or strtolower($user->userinfo['gmlevel'])==strtolower($config['premission_admin']))
{
	//change info
	?>
	<form action="./?page=profile&id=<?php echo $userid; ?>" method="post">
	<table width="100%" border="0" class="profile_advenced">
	  <tr>
		<td><?php echo $lang['Avatar']; ?>:</td>
		<td width="70%"><select name="avatar" id="avatar" onchange="preview();"><?php
		//loop trough modules
		$folder = "engine/res/avatars";
		$handle = opendir($folder);
		# Making an array containing the files in the current directory:
		while ($file = readdir($handle))
		{
			$files[] = $file;
		}
		closedir($handle);
		
		#echo the files
		$cont2 = "";
		foreach ($files as $file) 
		{
			
			if (strstr($file, ".gif"))
			{
				
				$file2=substr($file, 0,-4); //without .gif
				$file3=str_replace('_',' ',$file2); //replace "_" with " "
				if ($userinfo['avatar']==$file2)
				$cont2.= '<option value="'.$file2.'" selected="selected">'.ucfirst($file3).'</option>';
				else
				$cont2.= '<option value="'.$file2.'">'.ucfirst($file3).'</option>';
				
			} 
		}
		echo $cont2;
		?></select></td>
	  </tr>
	  <tr>
		<td><?php echo $lang['Question']; ?>:</td>
		<td><input type="text" name="question" style="width:95%" maxlength="100" value="<?php echo $userinfo['question']; ?>" /></td>
	  </tr>
	  <tr>
		<td valign="top"><?php echo $lang['Answer'].' ('.$lang['hidden']; ?>):</td>
		<td><input type="text" name="answer" style="width:95%" maxlength="100" value="<?php if (strtolower($user->userinfo['gmlevel'])==strtolower($config['premission_admin'])) echo htmlspecialchars($userinfo['answer']); ?>" /></td>
	  </tr>
	  <tr>
		<td valign="top"><?php echo $lang['Password'].' ('.$lang['hidden']; ?>):</td>
		<td><input type="text" name="password" style="width:95%" maxlength="100" value="" /></td>
	  </tr>
	  <?php
	  if (strtolower($user->userinfo['gmlevel'])==strtolower($config['premission_admin']))
	  {
	  	
		
	  ?>
	  <tr>
		<td><?php
		//get gm level on website
		$gmlevelweb_sql=$db->query("SELECT gmlevel FROM ".TBL_USERS." WHERE acc_login='".$db->escape($userinfo['username'])."' LIMIT 1")or die(mysql_error());
		$gmlevelweb=$db->fetch_array($gmlevelweb_sql); 
		echo $lang['GM level']; ?>:</td>
		<td><input type="text" name="gm" style="width:10%" maxlength="50" value="<?php echo $user->getUserGM($userinfo['guid']); ?>" id="gm" disabled="disabled" /> <input type="text" id="realm" name="realm" style="width:10%" maxlength="50" value="---" onmouseout="$WowheadPower.hideTooltip();" onmousemove="$WowheadPower.moveTooltip(event)" onmouseover="$WowheadPower.showTooltip(event, 'Realm ID')" /> <select style="width:70%" id="gmrealm" name="gmrealm" onchange="javascript:realm112()"><option value="---" selected="selected">---</option>
		<option value=""><?php echo $lang['All realms']; ?></option>
		<?php
			//loop trough realms and get statuses
				$config['engine_char_dbs2'] = explode(';',$config['engine_char_dbs']);
				$i=1;
				$out = "";
				$realm_names2=explode("|",$config['engine_realmnames']);
				foreach($config['engine_char_dbs2'] as $realms)
				{
					$realm_data=explode("|",$realms);
					$out.= '<option value="'.$i.'">(ID: '.$i.') '.substr($realm_names2[($i-1)],0,15).'</option>';
					$i++;
				}
				echo $out;
		?>
		</select></td>
	  </tr>
	  <tr>
		<td><?php echo $lang['Web GM level']; ?>:</td>
		<td><input type="text" name="gmweb" style="width:95%" maxlength="50" value="<?php echo $gmlevelweb[0]; ?>" /></td>
	  </tr>
	  <tr>
		<td><?php echo $lang['Vote Points']; ?></td>
		<td><input type="text" style="width:95%" name="vp" value="<?php echo $userinfo['vp']; ?>"/></td>
	  </tr>
	  <tr>
		<td><?php echo $lang['Donation Points']; ?></td>
		<td><input type="text" style="width:95%" name="dp" value="<?php echo $userinfo['dp']; ?>"/></td>
	  </tr>
	  <tr>
		<td><?php echo $lang['Ingame Ban']; ?>:</td>
		<td><select name="bangame">
				<option value="0" <?php if (!$user->usernameBanned($userinfo['username'])) echo 'selected="selected"'; ?>><?php echo $lang['Not Banned']; ?></option>
				<option value="1" <?php if ($user->usernameBanned($userinfo['username'])) echo 'selected="selected"'; ?>><?php echo $lang['Banned']; ?></option>
			</select></td>
	  </tr>
	  <tr>
		<td><?php echo $lang['Website Ban']; ?>:</td>
		<td><select name="banweb">
				<option value="0" <?php if (!$db->usernameBanned($userinfo['username'])) echo 'selected="selected"'; ?>><?php echo $lang['Not Banned']; ?></option>
				<option value="1" <?php if ($db->usernameBanned($userinfo['username'])) echo 'selected="selected"'; ?>><?php echo $lang['Banned']; ?></option>
			</select></td>
	  </tr>
	  
	  <?php
	  }
	  ?>
	  <tr>
		<td>&nbsp;</td>
		<td><input type="submit" name="submit" value="<?php echo $lang['OK']; ?>"/></td>
	  </tr>
	  
	</table><br />
<?php echo $lang['Allowed Characters']; ?>: <span class="colorgood">A-Z</span>,<span class="colorgood"> 0-9</span>,<span class="colorgood"> _ ; ? ! . , : =</span>
	</form>
	<?php
}