<?php
/************************************************************************
*													 engine/func/adminfunc.php
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

if (!defined('AXE_db') && !defined('AXE'))
	die("Access Error...");

class adminfunc {
	/*
	* printmenu - used for left menu
	*/
	function printmenu(){
		global $lang_admincp;
		$objects=$lang_admincp['License and Version'].'-main[|]'.$lang_admincp['Configuration Variables'].'-updateconfig[|]'.$lang_admincp['Template Editor'].'-stylemanager[|]';
		$objects.=$lang_admincp['CSS Editor'].'-cssmanager[|]'.$lang_admincp['Plugins'].'-plugins[|]'.$lang_admincp['Languages'].'-lang[|]'.$lang_admincp['Announcements &amp; News'].'-announcements[|]'.$lang_admincp['User Manager'].'-user[|]';
		//$objects.=$lang_admincp['Stats &amp; Logs'].'-stats[|]'.$lang_admincp['Maintenance'].'-maintain[|]';
		$objects.=$lang_admincp['Vote Manager'].'-vote[|]'.$lang_admincp['Menu Manager'].'-links[|]'.$lang_admincp['Credits'].'-credits';

		$f = preg_replace( "/[^A-Za-z0-9]/", "", $_GET['f'] ); //only letters and numbers
		if (!$_GET['f'] or $_GET['f']=='') $f='main';
		$objects=explode("[|]",$objects);

		foreach ($objects as $objects2)
		{
			$objects2=explode("-",$objects2);
			if ($f==$objects2[1])
			{
				echo '<strong><a href="./?f='.$objects2[1].'">'.$objects2[0].'</a></strong><div style="height:5px"></div>';
			}
			else
				echo '<a href="./?f='.$objects2[1].'">'.$objects2[0].'</a><div style="height:5px"></div>';
		}
	}

	/*
	* filter - removes ' or " from string
	*/
	function filter($str){
		return preg_replace( "/'/", "", preg_replace( "/\"/", "", $str ));
	}
	/*
	* BELOW all functions that are called
	* by GET method, main() is default
	*/
	function main(){
		global $db,$lang_admincphelp, $lang_admincp;
		$name = $lang_admincp['License and Version'];
		$github = new github();
		$commit = $github->get_last_commit();
		/* Print form */
	?>
	<h2><?php echo $name; ?></h2>

	<table width="100%" border="0" class="acptable" cellpadding="8px">
		<tr>
			<td class="dark" width="150px" style="text-align:right;"><?php echo $lang_admincp['CMS Version']; ?>:</td>
			<td>
				<table width="100%" border="0" >
					<tr>
						<td style="border:none"><strong><?php echo $github->create_link(SHA_VERSION, '#009900'); ?></strong> (<?php echo $lang_admincp['last update']; ?>: <strong><?php echo LASTUPDATE; ?></strong> <font color="#808080"><?php echo $lang_admincp['m/d/y']; ?></font>) (<a href='?f=main&updatecms=true'><?php echo $lang_admincp['Update now']; ?></a>)</td>
						<td width="300px" style="border:none;border-left: solid 1px grey"><center><?php if (SHA_VERSION == $commit->sha) echo '<font color="green">CMS is up to date</font>'; else echo '<font color="red">CMS is out of date</font>';?></center></td>
					</tr>
				</table>
			</td>
		</tr>
		<tr>
			<td class="dark" style="text-align:right;">GitHub Updates:</td>
			<td><?php $w = stream_get_wrappers(); if((function_exists("fsockopen") && (extension_loaded('openssl') || in_array('https', $w)))|| function_exists("curl_init")) echo $lang_admincp['Enabled']; else echo $lang_admincp['Disabled on this web server - Automated CMS update will not be possible.'];?></td>
		</tr>
		<tr>
			<td class="dark" style="text-align:right;">PHP info:</td>
			<td><a href="./?f=phpinfoo"><?php echo $lang_admincp['View PHP info']; ?></a></td>
		</tr>
	</table>
	<?php
		if((function_exists("fsockopen") || function_exists("curl_init")) && isset($_GET['updatecms']))
		{
			include_once(PATHROOT."engine/func/admin_update.php");
			/*
			* CMS updater
			**/
			if (isset($_GET['start_cms']))
			{
				$updateclass->GetPatchInfo();
			}
			/*
			* MODULE updater/uploader
			**/
			elseif (isset($_GET['start_modules']))
			{
				$updateclass->GetModuleInfo();
			}
			else
			{
				$updateclass->GetPatchInfo();
				$updateclass->GetModuleInfo();
			}

		}
		elseif (!function_exists("fsockopen"))
		{
			echo '<h2>'.$lang_admincp['Update now'].'</h2>'.$lang_admincp['PHP fsockopen() is disabled, update is not possible using this method.'].'';
		}
		else
			echo '<center><img src="./res/shema.jpg" /></center>';
		echo '<br><br>';
	}

	/*
	* user - Prints users and makes links to their profile.
	*/
	function user(){
		global $db,$user,$lang,$lang_admincp;
		echo "<h2>".$lang_admincp['User Manager'].'</h2>';
		if (isset($_POST['submit']))
		{
			$sql1 = $db->query($user->CoreSQL(0,$_POST['user']))or die($db->getLastError());
			while ($sql2=$db->getRow($sql1))
			{
				echo '<a href="'.PATHROOT.'?page=profile&id='.$sql2['id'].'">'.$sql2['username'].'</a><br>';
			}
		}
		else
		{
			echo '<form method="post"><input name="user" type="text" /> <input name="submit" type="submit" value="'.$lang['OK'].'" /></form>';
		}
	}
	/*
	* stats -
	*/
	function stats(){
		echo 'Under construction. Not available in BETA.';
	}
	/*
	* maintain -
	*/
	function maintain(){
		echo 'Under construction. Not available in BETA.';
	}

	function stylemanager(){
		include "../engine/func/adminstylemgr.php";
	}

	function cssmanager(){
		global $db,$lang_admincphelp,$lang_admincp,$config;
		echo '<h2>'.$lang_admincp['CSS Editor'].' (<a href="#csscodefull2">Go to testing box</a>)</h2>';
		if ($_GET['javascript']=='1') $javascript='&javascript=1'; else $javascript='&javascript=0';
		if (!isset($_GET['javascript']))
		{
			echo '<span class="buttonlink"><a href="./?f=cssmanager&javascript=1">'.$lang_admincp['Javascript Editor'].'</a></span><br><span class="buttonlink"><a href="./?f=cssmanager&javascript=0">'.$lang_admincp['Clean Editor'].'</a></span><br><br>';
			return;
		}
		if (isset ($_POST['cachetoconfig']))
		{
			//add script za cachanje u stylesheet.css
			Html::cache($_POST['csscodefull'],PATHROOT.'engine/_style_res/'.$config['engine_styleid'].'/stylesheet.css');
			if (!Html::cache($_POST['csscodefull'],PATHROOT.'engine/_style_res/'.$config['engine_styleid'].'/stylesheet_backup.css'))
				echo 'Backup not made, please create empty file:<strong> engine/_style_res/'.$config['engine_styleid'].'/stylesheet_backup.css</strong><br>';
			echo $lang_admincp['Action report'].": <font color='orange'>stylesheet.css is cached with test data, <strong>stylesheet_backup.css</strong> is created and cached.</font>";
			$fulledit2=$_POST['csscodefull'];
		}
		if (isset ($_POST['submit']))
		{
			/* Apply filters */
			//Form::setError('user_name', "* Username below 5 characters");
			$i=0;
			$query_insert=false;
			foreach($_POST['title'] as $x) {
				/* Save variables (only if conf name is not NULL) */
				if ($x<>''){
					#remove quotes from strings, as we don't need them
					$x= $this->filter(trim($x));$_POST['template'][$i]= $this->filter($_POST['template'][$i]);
					#check for errors
					if(!preg_match("/^([0-9a-zA-Z_\/;&#?!.,():% -])+$/", $x)) {Form::setError('title'.$i, "*"); }
					if (trim($_POST['template'][$i])<>'')
					{if(!preg_match("/^([0-9a-zA-Z_\/;&#?!.,()\n\rn:%= -])+$/", trim($_POST['template'][$i]))) Form::setError('template'.$i, "*");}

					if ($_POST['title'][$i]<>'')//dont save/erase it if title is empty
						$query_insert.="('".$config['engine_styleid']."','".$db->escape($x)."','".$db->escape($_POST['template'][$i])."','css'), ";
				}
				$i++;
			}
			$j=$i;
			if ( Form::$num_errors == 0 )
			{
				$db->query("DELETE FROM ".TBL_TEMPLATE." WHERE templatetype='css' AND styleid='".$config['engine_styleid']."'")or die($db->getLastError());
				$query_insert=rtrim($query_insert,', ');
				if ($query_insert=='') $query_insert="('','','','')";
				$db->query("INSERT INTO ".TBL_TEMPLATE." (styleid,title,template,templatetype) VALUES ".$query_insert )or die($db->getLastError());

				echo $lang_admincp['Action report'].": <font color='green'>".$lang_admincp['New data inserted'].".</font>";
				unset($_SESSION['value_array']);
				unset($_SESSION['error_array']);
			}
			else
			{
				echo $lang_admincp['Action report'].": <font color='gray'>".$lang_admincp['No changes made'].".</font>";
			}

			/* Set session arrays if any errors */
			$_SESSION['value_array'] = $_POST;
			//print_r($_SESSION['value_array']);
			$_SESSION['error_array'] = Form::getErrorArray();
		}
	?><form name="adminform" id="adminform" action="./?f=cssmanager<?php echo $javascript; ?>" method="POST">
		<?php
			$sql1 = $db->query("SELECT * FROM ".TBL_TEMPLATE." WHERE templatetype='css' AND styleid='".$config['engine_styleid']."' ORDER BY title ASC")or die($db->getLastError());
			$i=0;
			echo '<table width="100%" border="0" cellpadding="2"><tr><td width="200px">'.$lang_admincp['CSS element'].':</td><td>'.$lang_admincp['CSS proprety'].':</td></tr>';
			$fulledit='';
			while ($sql2=$db->getRow($sql1)){
			?><tr>
				<td valign="top" width="200px"><?php echo Form::error('title'.$i);?><input style="width:96%;font-family:consolas,'courier new',courier,monospace" type="text" name="title[]" value="<?php
							if ($_SESSION['value_array']['title'][$i]<>'')
							{echo htmlspecialchars($_SESSION['value_array']['title'][$i]);$fulledit.=htmlspecialchars($_SESSION['value_array']['title'][$i]);}
							else
							{echo $sql2['title'];$fulledit.= $sql2['title'];}
							$fulledit.=' {
							';
						?>" /></td>
				<td valign="top"><?php echo Form::error('template'.$i);?><textarea style="font-family:consolas,'courier new',courier,monospace;width:100%; font-size:12px" name="template[]" id="csscode<?php echo $i;?>" rows="4"><?php
							if ($_SESSION['value_array']['template'][$i]<>'')
							{echo htmlspecialchars($_SESSION['value_array']['template'][$i]);$fulledit.=htmlspecialchars($_SESSION['value_array']['template'][$i]);}
							else
						{echo $sql2['template'];$fulledit.=$sql2['template']; } ?></textarea>
					<?php
						$fulledit.='
						}

						';
						if ($_GET['javascript']=='1'){ ?>
						<script type="text/javascript">
							var editor = CodeMirror.fromTextArea('csscode<?php echo $i;?>', {
								height: "150px",
								parserfile: [ "parsecss2.js" ],
								stylesheet: [ "res/highlight/css/csscolors.css" ],
								path: "res/highlight/js/",
								continuousScanning: 5000,
								lineNumbers: true
							});
						</script>
						<?php } ?>
				</td>
			</tr>
			<?php
				$i++;
			}
			while ($j>$i)
			{
			?><tr>
				<td valign="top" width="200px"><?php echo Form::error('title'.$i);?><input style="width:96%;font-family:consolas,'courier new',courier,monospace" type="text" name="title[]" value="<?php
							echo htmlspecialchars($_SESSION['value_array']['title'][$i]);
						?>" /></td>
				<td valign="top"><?php echo Form::error('template'.$i);?><textarea name="template[]" id="csscode<?php echo $i;?>" rows="4" style="width:100%"><?php
						echo htmlspecialchars($_SESSION['value_array']['template'][$i]); ?></textarea>
					<script type="text/javascript">
						var editor = CodeMirror.fromTextArea('csscode<?php echo $i;?>', {
							height: "150px",
							parserfile: [ "parsecss2.js" ],
							stylesheet: [ "res/highlight/css/csscolors.css" ],
							path: "res/highlight/js/",
							continuousScanning: 2000,
							lineNumbers: true
						});
					</script>
				</td>
			</tr>
			<?php
				$i++;
			}
			echo '<tr>
			<td valign="top">
			<div id="1addmore'.$i.'"><a href="javascript:void();" onclick="javascript:addmore2('.$i.');return false;">[+'.$lang_admincp['add more'].']</a></div>
			</td>
			<td valign="top">
			<div id="2addmore'.$i.'"></div>
			</td>
			</tr>
			</table><br><br>

			<input type="submit" value="'.$lang_admincp['Save'].'" name="submit">';

			echo '<H2>Full CSS (for testing only)<A NAME="csscodefull2">&nbsp;</A></H2>';
			if ($fulledit2<>'') {$fulledit=$fulledit2;echo '<font color="orange">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;This testing content has been cached but not saved, if you close this page, changes will be lost (search for stylesheet_backup.css). Please add data above to save it.</font>';}
			echo' <textarea name="csscodefull" id="csscodefull" style="width:96%;height:700px">'.$fulledit.'</textarea>';
		?>
		<script type="text/javascript">
			var editor = CodeMirror.fromTextArea('csscodefull', {
				height: "700px",
				parserfile: [ "parsecss2.js" ],
				stylesheet: [ "res/highlight/css/csscolors.css" ],
				path: "res/highlight/js/",
				continuousScanning: 5000,
				lineNumbers: true
			});
		</script>
		<br /><input type="submit" value="Cache to stylesheet.css only (for testing)" name="cachetoconfig" /></form>
	<?php
	}

	function updateconfig(){
		global $db,$lang_admincphelp,$lang_admincp;
		$name= $lang_admincp['Configuration Variables'];
		$descr= $lang_admincphelp[26].".";
		$note= "";
		$j = 0;
		if (isset ($_POST['submit']))
		{
			/* Apply filters */
			//Form::setError('user_name', "* Username below 5 characters");
			$i=0;
			$query_insert=false;
			foreach($_POST['conf_name'] as $x) {
				/* Save variables (only if conf name is not NULL) */
				if ($x<>''){
					#remove quotes from strings, as we don't need them
					$x= $this->filter($x);$_POST['conf_value'][$i]= $this->filter($_POST['conf_value'][$i]);
					$_POST['conf_descr'][$i]= $this->filter($_POST['conf_descr'][$i]);
					$_POST['conf_stickied'][$i]= preg_replace( "/[^0-9]/", "", $_POST['conf_stickied'][$i] );
					#check for errors
					if(!preg_match("/^([0-9a-zA-Z_])+$/", $x)) {Form::setError('conf_name'.$i, "<big title='Invalid characters!'>&#9632;&#9632;&#9632;</big>"); }
					$_POST['conf_value'][$i]=htmlspecialchars($_POST['conf_value'][$i]);
					if(!preg_match("/^([0-9a-zA-Z_\/;&#?!.,-:=@ |()])+$/", $_POST['conf_value'][$i])) Form::setError('conf_value'.$i, "<big title='Invalid characters!'>&#9632;&#9632;&#9632;</big>");
					if ($_POST['conf_descr'][$i]<>'' && ($_POST['conf_stickied'][$i]=='0' or $_POST['conf_stickied'][$i]==''))
						if(!preg_match("/^([0-9a-zA-Z_\/;&#?!.,-:= |()])+$/", $_POST['conf_descr'][$i])) Form::setError('conf_descr'.$i, "<big title='Invalid characters!'>&#9632;&#9632;&#9632;</big>");
						$query_insert.="('".$db->escape($x)."','".$db->escape($_POST['conf_value'][$i])."','".$db->escape($_POST['conf_descr'][$i])."','".$_POST['conf_stickied'][$i]."','".$db->escape($_POST['conf_dropdown'][$i])."'), ";
				}
				$i++;
			}
			$j=$i;
			if ( Form::$num_errors == 0 )
			{
				$db->query("DELETE FROM ".TBL_CONFIG)or die($db->getLastError());
				$query_insert=rtrim($query_insert,', ');
				$db->query("INSERT INTO ".TBL_CONFIG." (conf_name,conf_value,conf_descr,conf_stickied,conf_dropdown) VALUES ".$query_insert )or die($db->getLastError());

				echo $lang_admincp['Action report'].": <font color='green'>".$lang_admincp['New data inserted'].".</font>";
				unset($_SESSION['value_array']);
				unset($_SESSION['error_array']);
			}
			else
			{
				echo $lang_admincp['Action report'].": <font color='gray'>".$lang_admincp['No changes made']."</font>";
			}

			/* Set session arrays if any errors */
			$_SESSION['value_array'] = $_POST;
			//print_r($_SESSION['value_array']);
			$_SESSION['error_array'] = Form::getErrorArray();
		}
		/* Print form */

	?><h2><?php echo $name; ?></h2><?php echo $descr;
		/* Insert configuration variable for trinity new version, used for RA string selector */
		$trin_conf_var=$db->query("SELECT conf_name FROM ".TBL_CONFIG." WHERE conf_name='trinity_version'")or die($db->getLastError());
		if ($db->numRows()=='0'){
			$trin_conf_var = $db->query("INSERT INTO ".TBL_CONFIG." (conf_name,conf_value,conf_descr,conf_stickied,conf_dropdown) VALUES
			('trinity_version','Older','Select RA method used, for compatibility with Trinity Core.','1','Older|Newer')")or die($db->getLastError());
			echo "<div style='border:solid 1px orange;background-color:#f3d7a3;padding: 4px'><center>
			\"trinity_version\" variable is added to configuration, please configure and recache your cms.</center></div>";
		}
		/* end insert conf. variable */

	?><br /><br /><form name="adminform" id="adminform" action="./?f=updateconfig" method="POST">
		<table border="0" cellspacing="0" cellpadding="3"><tr><td><strong><?php echo $lang_admincphelp[7]; ?></strong></td><td><strong><?php echo $lang_admincphelp[2]; ?></strong></td><td></td></tr>
			<?php
				$sql1 = $db->query("SELECT * FROM ".TBL_CONFIG)or die($db->getLastError());
				$i=0;
				while ($sql2=$db->getRow($sql1)){
				?>
				<tr><td  valign="top">
						<?php
							if ($sql2['conf_stickied']=='0'){
								if (isset($_SESSION['value_array']['conf_name'][$i]) && $_SESSION['value_array']['conf_name'][$i]<>'') $sql2['conf_name']=$_SESSION['value_array']['conf_name'][$i];
								echo '<input name="conf_name[]" onmouseout="$WowheadPower.hideTooltip();" onmousemove="$WowheadPower.moveTooltip(event)" onmouseover="$WowheadPower.showTooltip(event, \''.$sql2['conf_name'].'<br><small>'.$lang_admincphelp[1].'</small>\')" type="text" id="'.$sql2['conf_name'].'" value="'.$sql2['conf_name'].'" style="width:200px; color:darkblue" />';
							}
							else
								echo '<span onmouseout="$WowheadPower.hideTooltip();" onmousemove="$WowheadPower.moveTooltip(event)" onmouseover="$WowheadPower.showTooltip(event, \''.$lang_admincphelp[3].'\')">'.$sql2['conf_name'].'</span><input name="conf_name[]" type="hidden" id="'.$sql2['conf_name'].'" value="'.$sql2['conf_name'].'" />';
						echo Form::error('conf_name'.$i).'&nbsp;&nbsp;'; ?></td><td  valign="top">
						<?php
							if (trim($sql2['conf_dropdown'])<>'') {

							?><select name="conf_value[]" style="width:300px">
								<?php
									$sql2['conf_dropdownparts']=explode("|",trim($sql2['conf_dropdown']));
									foreach ($sql2['conf_dropdownparts'] as $conf_dropdownparts)
									{
										echo "<option ";
										if ($conf_dropdownparts==$sql2['conf_value']) echo "selected='selected' ";
										echo "value='".$conf_dropdownparts."'>".$conf_dropdownparts."</option>";
									}
								?>
							</select><input type="hidden" name="conf_dropdown[]" value="<?php echo $sql2['conf_dropdown']; ?>" />
							<?php
							} else {
							?>
							<input type="text" name="conf_value[]" onmouseout="$WowheadPower.hideTooltip();" onmousemove="$WowheadPower.moveTooltip(event)" onmouseover="$WowheadPower.showTooltip(event, '<?php echo $lang_admincphelp[2]; ?>')" value="<?php echo $sql2['conf_value']; ?>" style="width:300px"><input type="hidden" name="conf_dropdown[]" value="" /><?php
							}
							echo Form::error('conf_value'.$i).'&nbsp;&nbsp;'; ?><?php echo '<span onmouseout="$WowheadPower.hideTooltip();" onmousemove="$WowheadPower.moveTooltip(event)" onmouseover="$WowheadPower.showTooltip(event, \''.$lang_admincphelp[4].'\')">'.$sql2['conf_descr'].'</span><input name="conf_descr[]" type="hidden" value="'.$sql2['conf_descr'].'" />'.Form::error('conf_descr'.$i).'&nbsp;&nbsp;'; ?><input type="hidden" name="conf_stickied[]" value="<?php echo $sql2['conf_stickied']; ?>" /></td>
					<td  valign="top"></td></tr>
				<?php
					$i++;
				}
				while ($j>$i)
				{
				?>
				<tr><td  valign="top">
						<?php
							echo '<input name="conf_name[]"  onmouseout="$WowheadPower.hideTooltip();" onmousemove="$WowheadPower.moveTooltip(event)" onmouseover="$WowheadPower.showTooltip(event, \''.$lang_admincphelp[3].'\')" type="text" value="'.$_SESSION['value_array']['conf_name'][$i].'" style="width:200px; color:darkblue" />';
						echo Form::error('conf_name'.$i).'&nbsp;&nbsp;'; ?></td><td  valign="top">
						<input type="text" name="conf_value[]" value="<?php echo $_SESSION['value_array']['conf_value'][$i]; ?>" style="width:200px"><?php
						echo Form::error('conf_value'.$i).'&nbsp;&nbsp;[Note]: '.'<input name="conf_descr[]" style="width: 200px;" type="text" value="'.$_SESSION['value_array']['conf_descr'][$i].'" >'.Form::error('conf_descr'.$i).'&nbsp;&nbsp;'; ?></td>
					<td  valign="top"></td></tr>
				<?php
					$i++;
				}
			?>
			<tr><td valign="top"><?php echo '<div id="1addmore1"><a href="javascript:void();" onclick="javascript:addmore(1);$WowheadPower.hideTooltip();return false;" onmouseout="$WowheadPower.hideTooltip();" onmousemove="$WowheadPower.moveTooltip(event)" onmouseover="$WowheadPower.showTooltip(event, \''.$lang_admincphelp[5].'\')">[+'.$lang_admincp['add more'].']</a></div>'; ?></td><td  valign="top"><?php echo '<div id="2addmore1"></div>'; ?></td><td valign="top"></td></tr>
			<tr><td colspan="2" align="left"><input type="submit" name="submit" value="<?php echo $lang_admincp['Save']; ?>"></td><td></td></tr></table>
	</form><br />
	<?php
		echo $note.'<br>';
		#HELP:
	?>
	<table class="acptable" width="100%" cellpadding="7">
		<tr>
			<td class="dark" width="75px" style="text-align:right;"><?php echo $lang_admincp['Help']; ?>:</td>
			<td><?php echo $lang_admincp['Vote Manager']; ?>:<blockquote>vote_link_X&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<i>X =  1 -> </i><br /><img src="./res/updateconfig_vote.jpg" border="1" /></blockquote></td>
		</tr>
		<tr><td class="dark" style="text-align:right"><?php echo $lang_admincp['Allowed']; ?>:</td><td>0-9 a-z A-Z _ / ; & # ? ! . , : - = @ | ( )</td></tr>
	</table>
	<?php
	}

	function plugins() {
		include "../engine/func/adminplugins.php";
	}

	/**
	* links() - add links, links have its grup ids
	* links are cached into: engine/_cache/cache_menulinks.php
	*/
	function links() {
		global $db,$config,$lang_admincp,$lang_admincphelp;
		echo '<h2>'.$lang_admincp['Menu Manager'].'</h2>';
		if (isset($_POST['submit']))
		{
			/* Apply filters */
			$i=0;
			$query_insert=false;
			foreach($_POST['linktitle'] as $x) {
				/* Save variables (only if conf name is not NULL) */
				if ($x<>''){
					$x						= htmlspecialchars( trim(          $x			) );
					$_POST['linkurl'][$i]   = htmlspecialchars( trim($_POST['linkurl'][$i]  ) );
					$_POST['linkorder'][$i] = preg_replace( "/[^0-9]/", "", $_POST['linkorder'][$i] );
					$_POST['linkdescr'][$i] = htmlspecialchars( trim($_POST['linkdescr'][$i]) );
					$_POST['linkgrup'][$i] = preg_replace( "/[^a-zA-Z0-9]/", "", $_POST['linkgrup'][$i] );
					if ($_POST['linkgrup'][$i]=='')$_POST['linkgrup'][$i]='0';
					$_POST['linkprems'][$i] = preg_replace( "/[^0-9]/", "", $_POST['linkprems'][$i] );
					#check for errors
					if ($x<>'')
						$query_insert.="('".$db->escape($x)."','".$db->escape($_POST['linkurl'][$i])."','".$db->escape($_POST['linkorder'][$i])."','".$db->escape($_POST['linkdescr'][$i])."','".$db->escape($_POST['linkgrup'][$i])."','".$_POST['linkprems'][$i]."'), ";
				}
				$i++;
			}
			$db->query("DELETE FROM ".TBL_LINKS)or die($db->getLastError());
			$query_insert=rtrim($query_insert,', ');
			$db->query("INSERT INTO ".TBL_LINKS." (linktitle,linkurl,linkorder,linkdescr,linkgrup,linkprems) VALUES ".$query_insert )or die($db->getLastError());

			echo $lang_admincp['Action report'].": <font color='green'>".$lang_admincp['New data inserted'];
			if (Html::cache_menulinks()) echo ' '.$lang_admincp['and cached'].'.'; else echo ', '.$lang_admincp['links not cached'].'.';
			echo "</font>";
		}
		$sql1 = $db->query("SELECT * FROM ".TBL_LINKS." ORDER BY linkgrup ASC, linkorder ASC, linktitle")or die($db->getLastError());
		$i='nothing';$color="#C9C9C9";
		echo '<form action="./?f=links" method="post">';
		while ($sql2=$db->getRow($sql1)){
			if ($i<>$sql2['linkgrup']) {
				echo "<h3>".$lang_admincp['Group']." ".$sql2['linkgrup'].'</h3>'.$lang_admincp['Code to print links in this group'].': <input style="width:400px;font-family: consolas,\'courier new\',courier,monospace;" type="text" value="&lt;?php echo menulinks(&quot;'.$sql2['linkgrup'].'&quot;,&quot; | &quot;); ?&gt;" /> <small>('.$lang_admincp['second argument is link seperator in HTML code'].')</small><div style="height:6px"></div>';
				$i=$sql2['linkgrup'];
			}
			echo '<div style="background-color: '.$color.';" class="linkmanager">'.$lang_admincp['Title'].': <input name="linktitle[]" type="text" value="'.$sql2['linktitle'].'" /> URL: <input name="linkurl[]" type="text" value="'.$sql2['linkurl'].'" /> '.$lang_admincp['Order'].': <input name="linkorder[]" type="text" value="'.$sql2['linkorder'].'" style="width:30px" /> <i>'.$lang_admincp['Group'].' <input name="linkgrup[]" type="text" value="'.$sql2['linkgrup'].'" style="width:100px" /></i><div style="height:10px"></div>
			'.$lang_admincp['Description'].': <input name="linkdescr[]" type="text" value="'.$sql2['linkdescr'].'" style="width:200px" /> '.$lang_admincp['Viewable'].':
			<select name="linkprems[]">
			<option value="0"'; if ($sql2['linkprems']=='0') echo ' selected="selected"'; echo '>'.$lang_admincp['All'].'</option>
			<option value="1"'; if ($sql2['linkprems']=='1') echo ' selected="selected"'; echo '>'.$lang_admincp['Guests'].'</option>
			<option value="2"'; if ($sql2['linkprems']=='2') echo ' selected="selected"'; echo '>'.$lang_admincp['All logged in'].'</option>
			<!--<option value="3"'; if ($sql2['linkprems']=='3') echo ' selected="selected"'; echo '>&nbsp;&nbsp;Only normal players</option>-->
			<option value="4"'; if ($sql2['linkprems']=='4') echo ' selected="selected"'; echo '>&nbsp;&nbsp;'.$lang_admincp['Only Admins'].'</option>
			<option value="5"'; if ($sql2['linkprems']=='5') echo ' selected="selected"'; echo '>&nbsp;&nbsp;'.$lang_admincp["Only GM's and Admins"].'</option>
			</select></div>';
			if ($color=='#C9C9C9') $color="#ECECEC"; else $color='#C9C9C9';
		}
		echo '<div id="addmorelink1"><a href="javascript:void();" onclick="javascript:addmorelink(1);return false;">[+ '.$lang_admincp['add more'].']</a></div><br><input name="submit" type="submit" value="'.$lang_admincp['Save and Cache'].'" /></form>';
	?><br /><br />
	<table width="100%" border="0" class="acptable" cellpadding="8px" >
		<tr>
			<td class="dark" width="75px" style="text-align:right;">Help:</td>
			<td>
				<?php echo $lang_admincphelp[29]; ?>:
				<pre>&lt;span class=&quot;menulink<font color="orange">{X}</font>&quot;&gt;
					&lt;a href=&quot;<font color="orange">{Linkurl}</font>&quot;
					onmouseout=&quot;$WowheadPower.hideTooltip();&quot;
					onmousemove=&quot;$WowheadPower.moveTooltip(event)&quot;
					onmouseover=&quot;$WowheadPower.showTooltip(event, \'<font color="orange">{Linkdescr}</font>\')&quot;&gt;
					<font color="orange">{Linktitle}</font>
					&lt;/a&gt;
					&lt;/span&gt;
					<font color="orange">{link seperator here}</font>
					&lt;span class=&quot;menulink<font color="orange">{X}</font>&quot;&gt;
					&lt;a href=&quot;<font color="orange">{Linkurl}</font>&quot;&gt;<font color="orange">{Linktitle}</font>&lt;/a&gt;
					&lt;/span&gt;</pre>
			<?php echo $lang_admincphelp[30]; ?></td>
		</tr>

	</table>
	<?php
	}
	/**
	* lang() - Can create new language by copying default (English) files or
	* can edit files.
	*/
	function lang() {
		global $config,$lang_admincp,$lang_admincphelp,$db;
	?>
	<h2><a href="./?f=lang"><?php echo $lang_admincp['Languages']; ?></a></h2>
	<?php
		if (!isset($_POST['create']) && !isset($_GET['lang']) && !isset($_GET['save'])){
		?>
		<?php echo $lang_admincp['Note']; ?>: <?php echo $lang_admincphelp[31]; ?><br /><br />
		<form method="get"><input type="hidden" value="lang" name="f" />
			<?php
				echo Html::lang_selection($config['engine_lang']);
			?>
			<input type="submit" name="submit" value="OK" /> <input type="submit" name="submit2" value="<?php echo $lang_admincp['Save']; ?>" />
		</form><br />
		<form method="post"><input type="text" name="lang_name" /> <input type="submit" name="create" value="Create new" /></form>
		<?php
		}
		if (isset($_GET['submit2']))
		{
			$savelang = strtolower(preg_replace( "/[^A-Za-z0-9_-]/", "", $_GET['lang'] ));
			$db->query("UPDATE ".$config['engine_web_db'].".wwc2_config SET conf_value='".$db->escape($savelang)."' WHERE conf_name='engine_lang'")or die($db->getLastError());
			echo ucwords($lang_admincp['is saved']).'.';
			return;
		}
		if (isset($_POST['create']))
		{
			/**
			* What we will do is copy files from default (English) language
			*/
			$lang_name = strtolower(preg_replace( "/[^A-Za-z0-9_-]/", "", $_POST['lang_name'] ));
			if ($lang_name==''){echo $lang_admincphelp[33].'.';return;}
			$dir=PATHROOT.'engine/lang/english/';
			echo $lang_admincp['Copying files'].":<blockquote>";
			if (is_dir(PATHROOT.'engine/lang/'.$lang_name.'/')){
				echo $lang_admincphelp[34].'<div class="buttonlink"><a href="./?f=lang">'.$lang_admincp['Go Back'].'</a></div>';return;}
			if (is_dir($dir)) {
				if ($dh = opendir($dir)) {
					while (($file = readdir($dh)) !== false) {
						if ($file<>'..' && $file<>'.')
						{
							@mkdir(PATHROOT.'engine/lang/'.$lang_name.'/', 0777);
							if (Html::cache(@file_get_contents(PATHROOT.'engine/lang/english/'.$file),PATHROOT.'engine/lang/'.$lang_name.'/'.$file))
								echo $lang_admincp['From'].': ./engine/lang/<strong>english</strong>/'.$file.'&nbsp;&nbsp;&nbsp;'.$lang_admincp['to'].'&nbsp;&nbsp;&nbsp;./engine/lang/<strong>'.$lang_name.'</strong>/'.$file.' <br>';
							else
								echo $lang_admincp['File']." ".'<strong>engine/lang/'.$lang_name.'/'.$file.'</strong> '.$lang_admincp['Not Writable'].', '.$lang_admincp['please chmod this file to 777'].'.<br>';
						}
					}
					closedir($dh);
				}
			}
			echo '<div class="buttonlink"><a href="./?f=lang&lang='.$lang_name.'">'.$lang_admincp['Start Editing'].'</a></div></blockquote>';
		}
		/**
		* inside engine/lang/<language>/
		* We will loop all files and make editor for them its a simplest way.
		*/
		if (isset($_GET['lang']))
		{
			$editlang = strtolower(preg_replace( "/[^A-Za-z0-9_-]/", "", $_GET['lang'] ));
			echo '<big>&rsaquo; '.ucwords($editlang).'</big>';
			echo '<form method="post" action="./?f=lang&save=true"><input type="hidden" value="'.$editlang.'" name="lang"><center><input type="submit" value="'.$lang_admincp['Save'].'" name="saveall" ></center>';

			$dir=PATHROOT.'engine/lang/'.$editlang.'/';
			if (is_dir($dir)) {
				if ($dh = opendir($dir)) {
					while (($file = readdir($dh)) !== false) {
						if ($file<>'..' && $file<>'.' && preg_match("/.php/",$file))
						{
							$file2=preg_replace( '/\./', "_",$file );
							echo '<span class="phpfile" style="margin-left:40px;margin-bottom:5px">'.$file.'</span>';
						?>
						<textarea style="width:96%; height:350px" name="code<?php echo $file2;?>" id="code<?php echo $file2;?>"><?php
								echo htmlspecialchars(@file_get_contents(PATHROOT.'engine/lang/'.$editlang.'/'.$file));
						?></textarea><br />
						<script type="text/javascript">
							var editor = CodeMirror.fromTextArea('code<?php echo $file2;?>', {
								height: "350px",
								parserfile: [ "../contrib/php/js/tokenizephp.js", "../contrib/php/js/parsephp.js"],
								stylesheet: "res/highlight/css/xmlcolors.css",
								stylesheet: ["res/highlight/contrib/php/css/phpcolors.css"],
								path: "res/highlight/js/",
								continuousScanning: 2000,
								lineNumbers: true
							});
						</script>
						<?php
						}
					}
					closedir($dh);
				}
			}
			else
			{echo $lang_admincphelp[32];return;}
			echo '<center><input type="submit" value="'.$lang_admincp['Save'].'" name="saveall" ></center></form><br><br>';
		?>
		<table width="100%" border="0" class="acptable" cellpadding="8px" >
			<tr>
				<td class="dark" width="75px" style="text-align:right;">Help:</td>
				<td>
					<?php echo $lang_admincp['Example line']; ?>:<pre>"Next Step" => "Next Step",</pre><?php echo $lang_admincp['Translate it like this']; ?>:<pre>"Next Step" => "Translated Text",</pre><br /><br />
					<?php echo $lang_admincp['Example line']; ?>:<pre>2 => 'Value of the configuration variable.',</pre><?php echo $lang_admincp['Translate it like this']; ?>:<pre>2 => 'Translated text here.',</pre><br /><br /><?php echo $lang_admincp['Incorrect']; ?>: <pre>12 => 'This can'<span style="background-color:#FF0000">t be "correct".</span>',<br />"This is example string" => "Here i want to translate with "<span style="background-color:#FF0000">quotes" and 'singlequotes'.</span>",</pre><?php echo $lang_admincp['Correct']; ?>:<pre>12 => 'This can\'t be "correct".',<br />"This is example string" => "Here i want to translate with \"quotes\" and 'singlequotes'.",</pre><!--<br /><br />Also if you want to add wierd characters, it is suggested that you use ASCII HTML character codes. (use form below)<br /><br /><iframe src="./iframe_ascii.php" frameborder="0" style="width:96%" height="100px">Your browser does not support iframes.</iframe>-->
				</td>
			</tr>
		</table>
		<?php
		}
		/**
		* cache all language files
		*/
		if(isset($_POST['saveall']) && isset($_GET['save']))
		{
			$editlang = strtolower(preg_replace( "/[^A-Za-z0-9_-]/", "", $_POST['lang'] ));
			echo $lang_admincp['Action report'].': <font color=green>'.$editlang.' '.$lang_admincp['is saved'].'</font><br><br>';
			echo $lang_admincp['Saved files'].": <blockquote>";
			foreach ($_POST as $postedkey => $postedvalue)
			{	$postedvalue=trim($postedvalue);

				/*
				* check if postedkey is a file, also do not save file if its empty
				*/
				if (substr($postedkey,-4)=='_php' && $postedvalue<>''){

					if (Html::cache($postedvalue,PATHROOT.'engine/lang/'.$editlang.'/'.substr(preg_replace( '/\_/', '.',$postedkey ),4)))
						echo '<font color=gray>./ engine / lang / </font>'.$editlang.' / <strong>'.substr(preg_replace( '/\_/', '.',$postedkey ),4) .'</strong><br>';
					else
						echo $lang_admincp['File'].' <strong>'.substr(preg_replace( '/\_/', '.',$postedkey ),4).'</strong> '.$lang_admincp['Not Writable'].', '.$lang_admincp['please chmod this file to 777'].'.<br>';

				}
			}
			echo '</blockquote>';
			echo '<span class="buttonlink"><a href="?f=lang&lang='.$editlang.'">'.$lang_admincp['Go Back'].'</a></span><br>';return;
		}

	}

	function announcements() {
		global $lang_admincp,$lang,$config,$db,$user;
		$id = preg_replace( "/[^A-Za-z0-9]/", "", $_GET['id'] ); //only letters and numbers
		echo "<h2>".$lang_admincp['Announcements &amp; News'];
		if ($id=='') echo " - ".$lang_admincp['Create new'];
		else echo " - (<a href='./?f=announcements' style='color:blue'>".$lang_admincp['Create new']."</a>)";
		echo "</h2>";
		if(isset($_GET['delete']))
		{
			$delete = preg_replace( "/[^0-9]/", "", $_GET['delete'] ); //only letters and numbers
			$db->query("DELETE FROM ".$config['engine_web_db'].".wwc2_news WHERE id='".$delete."'") or die($db->getLastError());
			//FINISH THIS, delete style folder
			echo 'ID '.$delete.' '.$lang_admincp['is deleted'].'.';
			return;
		}
		if (isset($_POST['submit']))
		{
			$db->query("INSERT INTO ".$config['engine_web_db'].".wwc2_news (title,content,stickied,timepost,hidden,author) VALUES ('".$db->escape(htmlspecialchars($_POST['title']))."','".$db->escape($_POST['content'])."','".$db->escape($_POST['stickied'])."','".@date("U")."','".$db->escape($_POST['hidden'])."','".$user->username."')")or die($db->getLastError());
			echo ucwords($lang_admincp['is saved']).'.';
			return;
		}
		elseif (isset($_POST['edit']))
		{
			$db->query("UPDATE ".$config['engine_web_db'].".wwc2_news SET title='".$db->escape(htmlspecialchars($_POST['title']))."',content='".$db->escape($_POST['content'])."',stickied='".$db->escape($_POST['stickied'])."',hidden='".$db->escape($_POST['hidden'])."' WHERE id='".$_POST['id']."'")or die($db->getLastError());
			echo ucwords($lang_admincp['is saved']).'.';
			return;
		}

		$sql3 = $db->query("SELECT * FROM ".$config['engine_web_db'].".wwc2_news WHERE id='".$id."' LIMIT 1")or die($db->getLastError());
		$sql4 = $db->getRow($sql3);
	?>
	<form action="./?f=announcements" method="post">
		<input type="text" name="title" style="width:95%" maxlength="254" value="<?php echo $sql4['title']; ?>" /><br />
		<textarea name="content" style="height:100px; width:95%"><?php echo $sql4['content']; ?></textarea><br />
		<select name="hidden">
			<option <?php if ($sql4['hidden']=='0') echo 'selected="selected"';?> value="0"><?php echo $lang_admincp['Shown']; ?></option>
			<option <?php if ($sql4['hidden']=='1') echo 'selected="selected"';?> value="1"><?php echo $lang_admincp['Hidden']; ?></option></select>
		<select name="stickied">
			<option <?php if ($sql4['stickied']=='1') echo 'selected="selected"';?> value="1"><?php echo $lang_admincp['Announcement']; ?></option>
			<option <?php if ($sql4['stickied']=='0') echo 'selected="selected"';?> value="0"><?php echo $lang_admincp['News']; ?></option>
		</select> <?php if ($id<>'') echo '<input type="hidden" value="'.$id.'" name="id"><input type="submit" name="edit" value="'.$lang['OK'].'" />'; else echo '<input type="submit" name="submit" value="'.$lang['OK'].'" />'; ?>
	</form><blockquote>
	<?php
		$sql1 = $db->query("SELECT * FROM ".$config['engine_web_db'].".wwc2_news ORDER BY stickied DESC,id DESC")or die($db->getLastError());
		while ($sql2=$db->getRow($sql1))
		{
			if ($id==$sql2['id']){
				if ($sql2['stickied']=='1')
					echo '<i>'.$lang_admincp['Announcement'].':</i> ';
				echo "<b><a href='./?f=announcements&id=".$sql2['id']."'>".htmlspecialchars($sql2['title'])."</a></b> <a href='./?f=announcements&delete=".$sql2['id']."'>(".$lang_admincp['Delete'].")</a>";
			}
			else
			{
				if ($sql2['stickied']=='1')
					echo '<i>'.$lang_admincp['Announcement'].':</i> ';
				echo "<a href='./?f=announcements&id=".$sql2['id']."'>".htmlspecialchars($sql2['title'])."</a> <a href='./?f=announcements&delete=".$sql2['id']."'>(".$lang_admincp['Delete'].")</a>";
			}
			if ($sql2['hidden']=='1')
				echo " <i><font color=gray>(".$lang_admincp['Hidden'].")</font></i>";
			echo "<br>";
		}
		echo "</blockquote>";
	}

	/**
	* vote() - Full vote manager, links, images etc etc, vote images are not stored at style folder
	*          but they are universal for all styles. images: engine/res/vote
	*/
	function vote(){
		global $db,$lang_admincp,$config;
		echo '<h2>'.$lang_admincp['Vote Manager'].'</h2>';
	?>
	<table class="acptable" width="100%" cellpadding="7">
		<?php
			$sql1 = $db->query("SELECT * FROM ".TBL_CONFIG." WHERE conf_name LIKE 'vote_link_%' ORDER BY conf_name ASC")or die($db->getLastError());
			if ($db->numRows()=='0') echo '<tr>
				<td colspan="2"><a href="./?f=updateconfig">'.$lang_admincp['Configuration Variables'].'</a></td>

				</tr>';
			while ($sql2=$db->getRow($sql1))
			{
			?>
			<tr>
				<td class="dark" width="75px" style="text-align:right;"><?php echo $sql2['conf_name']; ?>:</td>
				<td>
					<?php echo $sql2['conf_value'].' (<a href="./?f=updateconfig#'.$sql2['conf_name'].'">'.$lang_admincp['Edit'].'</a>)';

						$voteid=preg_replace( "/vote_link_/", "", $sql2['conf_name']);
						$imgpath='../engine/_style_res/'.$config['engine_styleid'].'/images/voteimg/';

						if ($_REQUEST[completed] == $voteid) {
							$newname = $voteid.".gif";
							move_uploaded_file($_FILES['mailfile']['tmp_name'],$imgpath.$newname);
					} ?>
					<?php if ($_REQUEST[completed] != $voteid) { ?>
						<form enctype='multipart/form-data' method='post'>
							<input type='hidden' name='MAX_FILE_SIZE' value='1500000'>
							<input type='hidden' name='completed' value='<?php echo $voteid; ?>'>
							<?php echo $lang_admincp['Choose image to send'].' (GIF)'; ?>: <input type='file' accept="image/gif" name='mailfile'>
							<input type='submit' value='<?php echo $lang_admincp['Save']; ?>'></form>
						<?php } else { echo '<br /><a href="./?f=vote"><img src="./res/ok_green.gif" style="border:none" /></a>'; }
						#
						#
						if (file_exists($imgpath.$voteid.'.gif'))
							echo '<br /><img src="'.$imgpath.$voteid.'.gif" style="float:left;" />&nbsp;'.$imgpath.$voteid.'.gif';
						else
							echo '<br><img src="./res/novote.jpg" style="float:left;" />&nbsp;--';
					?>
				</td>
			</tr>
			<?php
			}
		?>
		<tr>
			<td class="dark" width="75px" style="text-align:right;"><?php echo $lang_admincp['Img Folder']; ?>:</td>
			<td><strong><?php echo $imgpath; ?></strong></td>
		</tr>
	</table>
	<?php
	}

	/**
	* credits() - This function should be viewable to all users.
	*/
	function credits() {
		Html::credits_cms();
	}

	function phpinfoo() {
		phpinfo();
	}
}

$adminfunc = new adminfunc;
unset($_SESSION['value_array']);
unset($_SESSION['error_array']);




