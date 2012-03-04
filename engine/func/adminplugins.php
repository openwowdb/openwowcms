<?php
/************************************************************************
*													engine/func/adminplugins.php
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

global $lang_admincp, $lang_admincphelp, $user;
if (!isset($user) || !$user->isAdmin()) die ('Invalid Access');

unset($_SESSION['update_files']);
echo '<h2>'.$lang_admincp['Plugins'].'</h2>';
$existing_plugins = Html::includeplugins();
$deactivated_plugins = filehandler::getDir('engine/plugins', false, '/deactivated-[0-9]-[0-9]-[0-9]\.php$/i');
if (isset($_GET['x']) && isset($_GET['z']) && isset($_GET['boxed']))
{
	/*filter variables*/
	$x = preg_replace("/[^A-Za-z0-9_]/", "", $_GET['x']);//template name
	$x2 = preg_replace("/[^A-Za-z0-9_]/", "", $_POST['moveto']);//template name
	$y = preg_replace("/[^0-9]/", "", $_GET['y']);//order
	$z = preg_replace("/[^0-9]/", "", $_GET['z']);//before/after content
	$boxed = preg_replace("/[^0-9]/", "", $_GET['boxed']);//1 or 0 or false
	if ($x=='') $x='body1';
	if ($y=='') $y='1';
	if ($z=='') $z='0';
	if ($boxed=='') $boxed='1';
	/**
	* Check if plugin already exists, if yes then print contents to textarea
	* if doesn't then dont copy anything just fill out forms with default and $_GET
	* values.
	*/
	if (filehandler::isExists($x.'-'.$y.'-'.$z.'-'.$boxed.'.php', 'engine/plugins'))
	{
		$content[0] = $lang_admincp['This is existing plugin']." (".$x.'-'.$y.'-'.$z.'-'.$boxed.".php) | <a onclick='javascript:window.open( \"./iframe_fetchplugin.php\", \"webwowplugins\", \"status = 1, height = 300, width = 300, resizable = 1, scrollbars=1\" );' style='color:blue' href='./?f=plugins&x=".$x."&y=".$y."&z=".$z."&boxed=".$boxed."&noparse=true'>[+] ".$lang_admincp['Get Plugins']." (".$lang_admincp['will overwritte this one'].")</a>";
		//buttons enabled/disabled(true/false) -> save, create new, deleted, eactivate
		$content[1] = array(true, true, true, ($x != 'deactivated'));
		/*we want to create new plugin but it already exists*/
		if (isset($_POST['submit_new']))
		{
			/**
			* at this point, we will check for plugin with name next in line in $y (order)
			* Point of this: MAKE SURE FILENAME DOES NOT EXISTS.
			*/
			// Get files in format [$x-0-$z-$boxed.php]
			$files = filehandler::getDir('engine/plugins', false, '/'.$x.'-[0-9]-'.$z.'-'.$boxed.'\.php$/i');
			foreach($files as $file)
			{
				$arr = explode('-', $file);
				if ($y == $arr[1])
					$y = $arr[1]+1;
			}
		}
		else if (isset($_POST['submit_delete'])) //delete plugin
		{
			filehandler::delete($x.'-'.$y.'-'.$z.'-'.$boxed.'.php', 'engine/plugins');
			echo $lang_admincp['Action report'].': '.$lang_admincp['Plugin'].' <strong>'.$x.'-'.$y.'-'.$z.'-'.$boxed.'.php</strong> '.$lang_admincp['is deleted'].'.';
			echo '<br><br><span class="buttonlink"><a href="?f=plugins">'.$lang_admincp['Return to plugins'].'</a></span><br>';return;
		}
		/*we want to deactivate plugin with unique name*/
		elseif (isset($_POST['submit_deactivate']))
		{
			/**
			* at this point, we will check for plugin with name next in line in $y (order)
			* Point of this: MAKE SURE FILENAME DOES NOT EXISTS.
			*/
			$y2 = $y;
			// Get files in format [deactivated-0-$z-$boxed.php]
			$dfiles = filehandler::getDir('engine/plugins', false, '/deactivated-[0-9]-'.$z.'-'.$boxed.'\.php$/i');
			foreach($dfiles as $dfile)
			{
				$arr = explode('-', $dfile);
				if ($y2 == $arr[1])
					$y2 = $arr[1]+1;
			}

			// Rename File
			filehandler::rename($x.'-'.$y.'-'.$z.'-'.$boxed.'.php',
				'deactivated-'.$y2.'-'.$z.'-'.$boxed.'.php',
				'engine/plugins'
			);
			echo  $lang_admincp['Action report'].': '.$lang_admincp['Plugin'].' <strong>'.$x.'-'.$y.'-'.$z.'-'.$boxed.'.php</strong> is deactivated.';
			echo '<br><br><span class="buttonlink"><a href="?f=plugins&x=deactivated&y='.$y2.'&z='.$z.'&boxed='.$boxed.'">'.$lang_admincp['Return to plugins'].'</a></span><br>';return;
		}
		elseif (isset($_POST['submit_move']))
		{
			/**
			* at this point, we will check for plugin with name next in line in $y (order)
			* Point of this: MAKE SURE FILENAME DOES NOT EXISTS.
			*/
			$y2 = $y;
			// Get files in format [$x2-0-$z-$boxed.php]
			$efiles = filehandler::getDir('engine/plugins', false, '/'.$x2.'-[0-9]-'.$z.'-'.$boxed.'\.php$/i');
			foreach($efiles as $efile)
			{
				$arr = explode('-', $efile);
				if ($y2 == $arr[1])
					$y2 = $arr[1]+1;
			}
			// Rename File
			filehandler::rename($x.'-'.$y.'-'.$z.'-'.$boxed.'.php', $x2.'-'.$y2.'-'.$z.'-'.$boxed.'.php', 'engine/plugins');
			echo  $lang_admincp['Action report'].': '.$lang_admincp['Plugin'].' <strong>'.$x.'-'.$y.'-'.$z.'-'.$boxed.'.php</strong> '.$lang_admincp['is moved'].'.';
			echo '<br><br><span class="buttonlink"><a href="?f=plugins&x='.$x2.'&y='.$y2.'&z='.$z.'&boxed='.$boxed.'">'.$lang_admincp['Return to plugins'].'</a></span><br>';return;
		}
	}
	else
	{
		/**
		* if file does not exist activate only "Create new" button (submit1)
		*/
		$content[0] = $lang_admincp['You are about to create a new Plugin']." | <a onclick='javascript:window.open( \"./iframe_fetchplugin.php\", \"webwowplugins\", \"status = 1, height = 300, width = 300, resizable = 1, scrollbars=1\" );' style='color:blue' href='./?f=plugins&x=".$x."&y=".$y."&z=".$z."&boxed=".$boxed."&noparse=true'>[+] ".$lang_admincp['Get Plugins']."</a>";
		//buttons enabled/disabled(true/false) -> save, create new, delete, deactivate
		$content[1] = array(false, true, false, false);
	}

	/**
	* POSTING!!!
	*/
	if(isset($_POST['submit_new']) or isset($_POST['submit_save']))//save plugin or create new-same thing
	{
		if (filehandler::write($x.'-'.$y.'-'.$z.'-'.$boxed.'.php', trim($_POST['code']), 'engine/plugins')){
			echo $lang_admincp['Action report'].': '.$lang_admincp['Plugin'].' <strong>'.$x.'-'.$y.'-'.$z.'-'.$boxed.'.php</strong> '.$lang_admincp['is saved'].'.<br><br>';
			/**
			* If there are files that need to be written, initiate update script in iframe and writte files
			*/
			if (trim($_POST['files'])<>''){
				//$_SESSION['update_files']=explode("[sep]",preg_replace( "/[^A-Za-z0-9-\/_.\[\]]/", "", $_POST['files'] ));
				$files1 = explode("[sep]",preg_replace( "/[^A-Za-z0-9-\/_.\[\]]/", "", $_POST['files'] ));
				$i = 0;
				foreach ($files1 as $files1)
				{
					$files1=trim($files1);
					if ($files1<>'')
					{
						echo "&nbsp;&nbsp;www/<span style='color:darkgray' id='filelist".$i."'>engine/plugins/data/".$files1.'</span><br>';
						$_SESSION['update_files'][$i]='engine/plugins/data/'.$files1;
						$i++;
					}
				}
				echo '<br><br><iframe src ="./iframe_update.php?i=0&v=0" width="100%" height="150" frameborder="0" style="background: white"><p> '.$lang_admincp['Your browser does not support iframes.'].'</p></iframe>';
			}
		}
		else
			echo $lang_admincp['Action report'].': '.$lang_admincp['Plugin'].' <strong>'.$x.'-'.$y.'-'.$z.'-'.$boxed.'.php</strong> '.$lang_admincp['is not saved, there was some problems'].'.';
		echo '<br><span class="buttonlink"><a href="?f=plugins&x='.$x.'&y='.$y.'&z='.$z.'&boxed='.$boxed.'">'.$lang_admincp['Return to plugins'].'</a></span><br>';return;
	}
?>

<table width="100%" border="0" class="acptable" cellpadding="8px" >
	<tr>
		<td class="dark" width="100px" style="text-align:right;"><?php echo $lang_admincp['Plugin Info']; ?>:</td>
		<td>
			<?php echo $content[0]; ?>
		</td>
	</tr>
	<tr>
		<td class="dark" width="100px" style="text-align:right;"><?php echo $lang_admincp['Options']; ?>:</td>
		<td><form method="get">
				<input type="hidden" value="plugins" name="f" />
				<input type="hidden" value="<?php echo $x; ?>" name="x" />
				<?php echo $lang_admincp['Order']; ?>: <input type="text" onkeyup="javascript:document.getElementById('check').style.display='table-cell';document.getElementById('submits').style.display='none';document.getElementById('code112table').style.display='none';document.getElementById('code112table2').innerHTML='<?php echo preg_replace( "/'/", "\'",$lang_admincp["Please, 'Check' if plugin exists first"]);?>. &nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;&lt;a href=&quot;#&quot; onclick=&quot;javascript:document.getElementById(\'code112table\').style.display=\'table-cell\';&quot;&gt;<?php echo $lang_admincp["Go Back"]; ?>&lt;/a&gt;';" value="<?php echo $y; ?>" name="y" size="6" /> <font color="gray">(<?php echo $lang_admincp['numeric']; ?>)</font> | <select name="boxed" onchange="javascript:document.getElementById('check').style.display='table-cell';document.getElementById('submits').style.display='none';document.getElementById('code112table').style.display='none';document.getElementById('code112table2').innerHTML='<?php echo preg_replace( "/'/", "\'",$lang_admincp["Please, 'Check' if plugin exists first"]);?>. &nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;&lt;a href=&quot;#&quot; onclick=&quot;javascript:document.getElementById(\'code112table\').style.display=\'table-cell\';&quot;&gt;<?php echo $lang_admincp["Go Back"]; ?>&lt;/a&gt;';">
					<option value="1" <?php if ($boxed==1) echo 'selected="selected" style="font-weight:bold"';?>><?php echo $lang_admincp['Wrapped']; ?></option>
					<option value="0" <?php if ($boxed==0) echo 'selected="selected" style="font-weight:bold"';?>><?php echo $lang_admincp['Not Wrapped']; ?></option>
				</select> |
				<select name="z" onchange="javascript:document.getElementById('check').style.display='table-cell';document.getElementById('submits').style.display='none';document.getElementById('code112table').style.display='none';document.getElementById('code112table2').innerHTML='<?php echo preg_replace( "/'/", "\'",$lang_admincp["Please, 'Check' if plugin exists first"]);?>. &nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;&lt;a href=&quot;#&quot; onclick=&quot;javascript:document.getElementById(\'code112table\').style.display=\'table-cell\';&quot;&gt;<?php echo $lang_admincp["Go Back"]; ?>&lt;/a&gt;';">
					<option value="0" <?php if ($z==0) echo 'selected="selected" style="font-weight:bold"';?>><?php echo $lang_admincp['Before template content']; ?></option>
					<option value="1" <?php if ($z==1) echo 'selected="selected" style="font-weight:bold"';?>><?php echo $lang_admincp['After template content']; ?></option>
				</select>&nbsp;&nbsp; <input type="submit" id="check" name="check" value="<?php echo $lang_admincp['Check']; ?>" style="display:none" />
				<noscript><input type="submit" name="check" value="<?php echo $lang_admincp['Check']; ?>"/></noscript></form>
		</td>
	</tr>

	<tr>
		<td class="dark" style="text-align:right;"><?php echo $lang_admincp['Code']; ?>:</td>
		<td id="code112table"><form name="pluginform" id="pluginform" method="post" onsubmit="return confirmSubmit()" action="./?f=plugins&x=<?php echo $x;?>&y=<?php echo $y;?>&z=<?php echo $z;?>&boxed=<?php echo $boxed;?>">
			<textarea id="code112" name="code" style="width:96%; height:200px; font:10pt consolas,'courier new',courier,monospace"><?php
				echo htmlspecialchars(@file_get_contents(PATHROOT.'engine/plugins/'.$x.'-'.$y.'-'.$z.'-'.$boxed.'.php')); ?></textarea>
			<div id="files_noter">&nbsp;</div><input type="hidden" value="" name="files" /></td>
	</tr>
	<tr>
		<td class="dark" align="right"><?php echo $lang_admincp['Action']; ?>:</td>
		<td id="code112table2"><span id="submits">
		<?php if ($content[1][0]) echo '<input type="submit" name="submit_save" value="'.$lang_admincp['Save to'].' '.$x.'-'.$y.'-'.$z.'-'.$boxed.'.php" />&nbsp;&nbsp;&nbsp;';
					if ($content[1][2]) echo '<input type="submit" name="submit_delete" value="'.$lang_admincp['Delete'].'" />&nbsp;&nbsp;&nbsp;';
					if ($content[1][3]) echo '<input type="submit" name="submit_deactivate" value="'.$lang_admincp['Deactivate'].'" />&nbsp;&nbsp;&nbsp;';
					echo $lang_admincp['Move to'].': <select name="moveto">
						<option value="doctype">DocType</option>
						<option value="head">Head</option>
						<option value="bodytag">Body Tag</option>
						<option value="header">Header</option>
						<option value="body1">'.$lang_admincp['Body'].' 1</option>
						<option value="body2">'.$lang_admincp['Body'].' 2</option>
						<option value="body3">'.$lang_admincp['Body'].' 3</option>
						<option value="footer">Footer</option>
						</select><input type="submit" name="submit_move" value="'.$lang_admincp['Move'].'">&nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;';
					if ($content[1][1]) echo '<input type="submit" name="submit_new" value="'.$lang_admincp['Create new'].'" />';
			?></span></td>
	</tr>
	<tr>
		<td class="dark" width="100px" style="text-align:right;"><?php echo $lang_admincp['Help']; ?>:</td>
		<td>
			<?php echo $lang_admincphelp[27]; ?>: <pre>$db->query("SELECT * FROM ...");</pre>
		</td>
	</tr>
</table>
</form>

<?php
	/**
	* Print links of all plugins that are related to template element
	*/

	echo "<h4>".$lang_admincp['Plugins located in this element']." (".$x."):</h4>";
	if ($existing_plugins[$x])
	{
		$counter=0;
		while ($counter<=(count($existing_plugins[$x])-1)){
			$plugin_info=explode('-',$existing_plugins[$x][$counter]);
			echo '<span class="phpfile"><a href="./?f=plugins&x='.$x.'&y='.$plugin_info[0].'&z='.$plugin_info[1].'&boxed='.$plugin_info[2].'">'.$x.'-'.$existing_plugins[$x][$counter].".php</a>
			<font color=gray>(".$lang_admincp['Attached to element'].": <strong>".$x."</strong>. ".$lang_admincp['Order'].": <strong>".$plugin_info[0]."</strong>. ".$lang_admincp['located'].": <strong>";
			if ($plugin_info[1]==0) echo $lang_admincp['before'].'</strong> '.$x.', '.$lang_admincp['content'];
			else echo $lang_admincp['after'].'</strong> '.$x.', '.$lang_admincp['content'];
			echo ": <strong>";
			if ($plugin_info[2]==0) echo $lang_admincp['not'].'</strong> '.strtolower($lang_admincp['Wrapped']);
			else echo strtolower($lang_admincp['Wrapped']).'</strong>';
			echo ")</font></span><br>";
			$counter++;
		}
	}
	else
		echo "<i>".$lang_admincp['No plugins attached to']." <strong>".$x."</strong> ".$lang_admincp['element yet'].".</i><br>";
}
else
{
	echo $lang_admincphelp[28].'<h3>'.$lang_admincp['Plugins'].'</h3>';
	$printoutlist=array(
		'DocType[|]doctype',
		'Head[|]head',
		'Body Tag[|]bodytag',
		'Header[|]header',
		'Body 1[|]body1',
		'Body 2[|]body2',
		'Body 3[|]body3',
		'Footer[|]footer');
	foreach ($printoutlist as $printoutlist2)
	{
		$printoutlist3=explode('[|]',$printoutlist2);
		echo '<blockquote>
		<h4>'.$printoutlist3[0].'</h4>
		<blockquote>';
		$counter=0;
		foreach ($existing_plugins[$printoutlist3[1]] as $pluginname)
		{
			$pluginname2=explode("-",$pluginname);
			echo '<span class="phpfile"><a href="./?f=plugins&x='.$printoutlist3[1].'&y='.$pluginname2[0].'&z='.$pluginname2[1].'&boxed='.$pluginname2[2].'">'.$printoutlist3[1].'-'.$pluginname.'.php</a></span><br>';
			$counter++;
		}
		if ($counter==0)
			echo '<i><a href="./?f=plugins&x='.$printoutlist3[1].'&y=&z=&boxed=">'.$lang_admincp['No plugins attached to'].' '.$printoutlist3[1].'.</a></i>';
		echo '</blockquote></blockquote>';
	}

	echo '<blockquote><h4>'.$lang_admincp['Deactivated Plugins (will not be used in website)'].'</h4>';
	echo '<blockquote>';
	if (count($deactivated_plugins) > 0)
	{
		foreach($deactivated_plugins as $pluginname)
		{
			$pluginname2=explode("-",$pluginname);
			echo '<span class="phpfile"><a href="./?f=plugins&x=deactivated&y='.$pluginname2[1].'&z='.$pluginname2[2].'&boxed='.$pluginname2[3].'">'.$pluginname.'</a></span><br>';
		}
	}
	else
		echo '<i><a href="./?f=plugins&x=deactivated&y=&z=&boxed=">'.$lang_admincp['No plugins attached to'].' deactivated.</a></i>';
	echo '</blockquote></blockquote>';
}
?>