<?php
/************************************************************************
*													engine/func/adminstylemgr.php
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

/*STYLEMANAGER FUNCTION*/

global $db,$config,$lang_admincp,$lang_admincphelp,$user,$lang,$installer_lang;
/**
* Create new style - copy all variables from db to new style ID
* and change engine_styleid to newly created ID
*/


if (isset($_GET['createnew']))
{
	echo "<h2>".$lang_admincp['Template Editor'].'</h2>';
	//get max style id from db
	$newstyleid_sql=$db->query("SELECT MAX(styleid) FROM ".TBL_TEMPLATE."")or die($db->getLastError());
	$newstyleid=$db->getRow($newstyleid_sql);

	//copy all variables
	$newstyle_sql=$db->query("SELECT * FROM ".TBL_TEMPLATE." WHERE styleid='".$config['engine_styleid']."'")or die($db->getLastError());
	while ($newstyle=$db->getRow($newstyle_sql))
	{
		$db->query("INSERT INTO ".TBL_TEMPLATE." (styleid,title,template,template_un,templatetype,username,version) VALUES ('".($newstyleid[0]+1)."','".$newstyle['title']."','".$db->escape($newstyle['template'])."','".$db->escape($newstyle['template'])."','".$newstyle['templatetype']."','".$user->username."','1')")or die($db->getLastError());

	}
	//copy style dir FINISH THIS
	//$this->smartCopy(PATHROOT.'engine/_style_res/'.$config['engine_styleid'].'', PATHROOT.'engine/_style_res/'.($newstyleid[0]+1).'');
	echo "Please copy ".PATHROOT.'engine/_style_res/'.$config['engine_styleid'].' to '.PATHROOT.'engine/_style_res/'.($newstyleid[0]+1).'<br>';
	$db->query("UPDATE ".TBL_CONFIG." SET conf_value='".($newstyleid[0]+1)."' WHERE conf_name='engine_styleid' LIMIT 1")or die($db->getLastError());
	echo $lang_admincp['Your new styleid is'].': '.($newstyleid[0]+1);
	return;
}
/**
* Deletes style ID if <> 1
*/
elseif(isset($_GET['delete']))
{
	echo "<h2>".$lang_admincp['Template Editor'].'</h2>';
	$delete = preg_replace( "/[^0-9]/", "", $_GET['delete'] ); //only letters and numbers
	if ($delete=='1') {echo $lang_admincp['Locked']; return;}
	$db->query("DELETE FROM ".TBL_TEMPLATE." WHERE styleid='".$delete."'") or die($db->getLastError());
	//FINISH THIS, delete style folder
	echo $lang_admincp['Style'].' '.$delete.' '.$lang_admincp['is deleted'].'.';
	return;
}
/*save order if set*/
if ($_GET['html_order']<>'')
	$db->query("UPDATE ".TBL_TEMPLATE." SET template='".preg_replace( "/[^0-9]/", "", $_GET['html_order'] )."' WHERE title='html_order' AND styleid='".$config['engine_styleid']."'") or die($db->getLastError());
/*save form*/
$post_templatename = preg_replace( "/[^A-Za-z0-9_]/", "", $_POST['template'] ); //only letters and numbers
if (isset($_POST['submit']))
{
	$db->query("UPDATE ".TBL_TEMPLATE." SET template='".$db->escape($_POST['code'])."', template_un='".$db->escape($_POST['backup'])."' WHERE title='".$post_templatename."' AND styleid='".$config['engine_styleid']."'") or die($db->getLastError());
	echo $lang_admincp['Action report'].": <font color='green'>".$lang_admincp['Template is saved'].".</font>";
}
/*undo form*/
if (isset($_POST['undo']))
{
	$db->query("UPDATE ".TBL_TEMPLATE." SET template=template_un WHERE title='".$post_templatename."' AND styleid='".$config['engine_styleid']."'") or die($db->getLastError());
	echo $lang_admincp['Action report'].": <font color='green'>".$lang_admincp['Template is reversed'].".</font>";
}

/*h2 start*/
echo "<h2>".$lang_admincp['Template Editor'];
if (isset($_GET['template'])) echo ' (<a style="color:blue" href="./?f=stylemanager">'.$lang_admincp['Go Back'].'</a>)';
else echo ' (<a style="color:blue" href="./?f=stylemanager&createnew">'.$lang_admincp['Create new'].'</a>) (<a style="color:blue" href="./?f=stylemanager&delete='.$config['engine_styleid'].'">'.$lang_admincp['Delete'].' '.$lang['Style'].' '.$config['engine_styleid'].'</a>)';

/*REMOTE UPDATE link start*/
echo '&nbsp;&nbsp;&nbsp;<a href="./?f=stylemanager&getmorestyles=true" style="color:blue"><img style="border:none" src="../engine/res/download.jpg" /></a>';
/*REMOTE UPDATE link end*/

echo "</h2>";
/*h2 end*/

/*REMOTE UPDATE start*/
if(function_exists("fsockopen") && isset($_GET['getmorestyles']))
{
	include_once(PATHROOT."engine/func/admin_update.php");
	if (isset($_GET['webwowid'])){
		$updateclass->GetStyle( preg_replace( "/[^0-9]/", "", $_GET['webwowid'] ),$_GET['confirm']);

	}
	else
		$updateclass->GetStylesInfo();
	return;
}
else if(!function_exists("fsockopen") && isset($_GET['getmorestyles']) )
{
	echo $lang_admincp["PHP fsockopen() is disabled, update is not possible using this method."].'<br><br>';return;
}
/*REMOTE UPDATE end*/


/*do query for template elements*/

$sql1 = $db->query("SELECT * FROM ".TBL_TEMPLATE." WHERE (templatetype='template' or templatetype='other') AND styleid='".$config['engine_styleid']."'") or die($db->getLastError());
if ($db->numRows()<=19)
	{echo $lang_admincp['There is some template elements missing on current selected style, change to style 1.']; return;}

/*do loop trough page elements*/
while ($template=$db->getRow($sql1))
{
	if ($template['templatetype']=='template'){
		$tpl[$template['title']][0]=htmlspecialchars($template['template']);
		$tpl[$template['title']][1]=$template['template_un'];
	}
	else
		$tpl[$template['title']][0]=$template['template'];
}


/*editing style now*/
if (isset($_GET['template']))
{
	$tpl_info[0]=preg_replace( "/[^A-Za-z0-9_]/", "", $_GET['template'] );
	$tpl_info[1]=$tpl[preg_replace( "/[^A-Za-z0-9_]/", "", $_GET['template'] )][0];
?>
<form method="post" action="./?f=stylemanager&template=<?php echo $tpl_info[0]; ?>">
<input type="hidden" value="<?php echo $tpl_info[1]; ?>" name="backup" />
<input type="hidden" value="<?php echo $tpl_info[0]; ?>" name="template" />
<table width="100%" border="0" class="acptable" cellpadding="8px" >
<tr>
<td class="dark" width="75px" style="text-align:right;"><?php echo $lang_admincp['Template']; ?>:</td>
<td>
<strong><?php echo $tpl_info[0]; ?></strong>
</td>
</tr>

<tr>
<td class="dark" style="text-align:right;"><?php echo $lang_admincp['Code']; ?>:</td>
<td><textarea id="code112" name="code" style="width:96%; height:200px; font:10pt consolas,'courier new',courier,monospace"><?php echo $tpl_info[1]; ?></textarea></td>
</tr>
<?php
if ($tpl_info[0]=='head' && file_exists(PATHROOT.'engine/_style_res/'.$config['engine_styleid'].'/stylesheet.css'))
	echo '<tr>
<td class="dark" style="text-align:right;">'.$lang_admincp['Stylesheet'].':</td>
<td>'.$lang_admincphelp[6].':
<pre>&lt;link rel="stylesheet" type="text/css" href="./engine/_style_res/'.$config['engine_styleid'].'/stylesheet.css" /&gt;</pre></td>
</tr>';
?>
<tr>
<td class="dark">&nbsp;</td>
<td><input type="submit" name="undo" value="<?php echo $lang_admincp['Undo last save']; ?>" />&nbsp;<input type="submit" name="submit" value="<?php echo $lang_admincp['Save template']; ?>" /></td>
</tr>
<tr>
<td class="dark" width="75px" style="text-align:right;"><?php echo $lang_admincp['PHP Variables and Code']; ?>:</td>
<td>
<?php echo $lang_admincphelp[7]; ?>:<blockquote><pre>$config['variable_name']; <font color="orange">//<?php echo $lang_admincphelp[8]; ?></font></pre></blockquote>
<?php echo $lang_admincphelp[9]; ?>:<blockquote><pre>&lt;?php if($user->logged_in){ ?&gt;<font color="blue"><?php echo $lang_admincphelp[10]; ?>.</font>&lt;?php } ?&gt;</pre></blockquote>
<?php echo $lang_admincphelp[11]; ?>:<blockquote><pre>&lt;?php if(!$user->logged_in){ ?&gt;<font color="blue"><?php echo $lang_admincphelp[12]; ?>.</font>&lt;?php } ?&gt;</pre></blockquote>
<?php echo $lang_admincphelp[13]; ?>:<blockquote><pre>&lt;?php echo $user->username; <font color="orange">//<?php echo $lang_admincphelp[14]; ?></font> ?&gt;<br />&lt;?php echo $user->userid; <font color="orange">//<?php echo $lang_admincphelp[15]; ?></font> ?&gt;
&lt;?php echo $user->userlevel; <font color="orange">//<?php echo $lang_admincphelp[16]; ?></font> ?&gt;
&lt;?php echo $user->time; <font color="orange">//<?php echo $lang_admincphelp[17]; ?></font> ?&gt;
&lt;?php print_r( $user->userinfo ); <font color="orange">//<?php echo $lang_admincphelp[18]; ?></font> ?&gt;
&lt;?php echo $user->userinfo['variable_name']; ?&gt;
</pre></blockquote>
<?php echo $lang_admincphelp[19]; ?>:<blockquote><pre>&lt;?php if ($user->isAdmin()) { ?&gt;<font color="blue"><?php echo $lang_admincphelp[20]; ?>.</font>&lt;?php } ?&gt;</pre></blockquote>

<?php echo $lang_admincp['Menu Manager']; ?>:<blockquote><pre>&lt;?php echo menulinks("menu_group"," | "); ?&gt;</pre></blockquote>

<?php echo $lang_admincphelp[21]; ?>:
<blockquote>
<i><?php echo $lang_admincphelp[22]; ?>:</i>
<pre>&lt;?php echo echo Html::portcheck('Realmname1: |&lt;br&gt;Realmname2: '); ?&gt;</pre>
<i><?php echo $lang_admincphelp[23]; ?>:</i>
<pre>&lt;?php echo echo Html::portcheck('','80','127.0.0.1'); ?&gt;</pre>
</blockquote><?php echo $installer_lang['Mail sending']; ?>
<blockquote>
<pre>echo sendmail($charname, $charguid, $subject, $item, $realmid=0, $stack=1, $money=0, $externaltext=false)</pre>
</blockquote>
<blockquote><pre></pre></blockquote>
</td>
</tr>
<tr>
<td class="dark" width="75px" style="text-align:right;"><?php echo $lang_admincp['Help']; ?>:</td>
<td><?php echo $lang_admincphelp[24]; ?>.<br /><?php echo $lang_admincphelp[25]; ?>.</td>
</tr>

</table>
</form>

<?php
}

/*viewing style list*/
else{
	$i=0;if ( !$tpl['html_order'][0] or strlen($tpl['html_order'][0])<=6 ) {$tpl['html_order'][0]='4015263';
		/*save order if set*/
		$db->query("UPDATE ".TBL_TEMPLATE." SET template='".$tpl['html_order'][0]."' WHERE title='html_order' AND styleid='".$config['engine_styleid']."'") or die($db->getLastError());
	}


	while ($i<=6)
	{
		/*make ordering links*/

		$tplorder[$i]=$tpl['html_order'][0][($i+1)];
		$tplorder[($i+1)]=$tpl['html_order'][0][$i];

		$tplorder2[$i]=$tpl['html_order'][0][($i-1)];
		$tplorder2[($i-1)]=$tpl['html_order'][0][$i];

		$orderlinks_num_down=
			$tpl['html_order'][0][($i-5)].
			$tpl['html_order'][0][($i-4)].
			$tpl['html_order'][0][($i-3)].
			$tpl['html_order'][0][($i-2)].
			$tpl['html_order'][0][($i-1)].
			$tplorder[$i].
			$tplorder[($i+1)].
			$tpl['html_order'][0][($i+2)].
			$tpl['html_order'][0][($i+3)].
			$tpl['html_order'][0][($i+4)].
			$tpl['html_order'][0][($i+5)].
			$tpl['html_order'][0][($i+6)];

		$orderlinks_num_up=
			$tpl['html_order'][0][($i-6)].
			$tpl['html_order'][0][($i-5)].
			$tpl['html_order'][0][($i-4)].
			$tpl['html_order'][0][($i-3)].
			$tpl['html_order'][0][($i-2)].
			$tplorder2[($i-1)].
			$tplorder2[$i].
			$tpl['html_order'][0][($i+1)].
			$tpl['html_order'][0][($i+2)].
			$tpl['html_order'][0][($i+3)].
			$tpl['html_order'][0][($i+4)].
			$tpl['html_order'][0][($i+5)];

		$orderlinks='<span style="float:left"><a href="./?f=stylemanager&html_order='.$orderlinks_num_up.'">&#9650;</a><a href="./?f=stylemanager&html_order='.$orderlinks_num_down.'">&#9660;</a></span>';

		if ($i==0) $orderlinks='<span style="float:left"><a href="./?f=stylemanager&html_order='.$orderlinks_num_down.'">&#9660;</a></span>';
		if ($i==6) $orderlinks='<span style="float:left"><a href="./?f=stylemanager&html_order='.$orderlinks_num_up.'">&#9650;</a></span>';
		/*end ordering links*/
		if ($tpl['html_order'][0][$i]=='0')//if (content)
			$parts_order[$i]='

<tr>
<td align="right">&nbsp;</td>
<td style="border:solid 1px #DADA7F; background:#FFFF99; border-bottom:none;" class="templateboxes">
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href="./?f=stylemanager&template=box_start0">'.$lang_admincp['Content Wrapper Top'].'</a></td>
<td align="left">'.$lang_admincp['Template content <strong>before</strong> included module'].' .</td>
</tr>
<tr>
<td align="right" class="templateboxes">'.$orderlinks.'</td>
<td style="border:solid 1px #DADA7F; background:#FFFF99; border-bottom:none; border-top:none">

<strong style="color:#C8C876">('.$lang_admincp['content'].')</strong></td>
<td align="left">'.$lang_admincp['Included module here'].'.</td>
</tr>
<tr>
<td align="right">&nbsp;</td>
<td style="border:solid 1px #DADA7F; background:#FFFF99; border-top:none"  class="templateboxes">
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href="./?f=stylemanager&template=box_end0">'.$lang_admincp['Content Wrapper Bottom'].'</a></td>
<td align="left">'.$lang_admincp['Template content <strong>after</strong> included module'].'.</td>
</tr>
';
		else //if bodies
		{
			if ($tpl['html_order'][0][$i]<=3)
				$parts_order[$i]='
<tr>
<td align="right" width="22px">&nbsp;</td>
<td style="padding:0px; margin-top:5px"><div style="height:4px"></div>
<div class="pluginbox" style="border-bottom:none"><a href="./?f=stylemanager&template=box_start'.$tpl['html_order'][0][$i].'">'.$lang_admincp['Template Wrapper top'].'</a><br><a href="./?f=plugins&x=body'. $tpl['html_order'][0][$i] .'&z=0&boxed=1">'.$lang_admincp['Plugin here'].'</a><br><a href="./?f=stylemanager&template=box_end'.$tpl['html_order'][0][$i].'">'.$lang_admincp['Template Wrapper bottom'].'</a></div>
</td>
<td align="left"></td>
<tr>
<td align="right" class="templateboxes">'.$orderlinks.'</td>
<td style="border:solid 1px #24FF24; background:#CCFFCC;" class="templateboxes">
<a href="./?f=stylemanager&template=body'.$tpl['html_order'][0][$i].'"><strong>'.$lang_admincp['Body'].' '. $tpl['html_order'][0][$i] .'</strong></a></td>
<td align="left">';
			else
				$parts_order[$i]='<tr><td height="4px"></td></tr>
<td align="right" class="templateboxes">'.$orderlinks.'</td>
<td style="border:solid 1px #BBDDFF; background:#F0F8FF;" class="templateboxes">
<a href="./?f=stylemanager&template=body'.$tpl['html_order'][0][$i].'"><strong>'.$lang_admincp['Body'].' '. $tpl['html_order'][0][$i] .'</strong></a></td>
<td align="left">';



			if ($tpl['html_order'][0][$i]<=3)
				$parts_order[$i].=$lang_admincp['Suggested for table independent content'].'.';
			else
				$parts_order[$i].=$lang_admincp['Suggested for table elements'].'.';




			$parts_order[$i].='</td>
</tr>';
			if ($tpl['html_order'][0][$i]<=3)
				$parts_order[$i].='<tr>
<td align="right" width="22px">&nbsp;</td>
<td width="300px" style="padding:0px">
<div class="pluginbox" style="border-top:none"><a href="./?f=stylemanager&template=box_start'.$tpl['html_order'][0][$i].'">'.$lang_admincp['Template Wrapper top'].'</a><br><a href="./?f=plugins&x=body'. $tpl['html_order'][0][$i] .'&z=1&boxed=1">Plugin here</a><br><a href="./?f=stylemanager&template=box_end'.$tpl['html_order'][0][$i].'">Template Wrapper bottom</a></div><div style="height:4px"></div>
</td>
<td align="left"></td>
</tr>
';
			else $parts_order[$i].='<tr><td height="4px"></td></tr>';
		}
		$i++;
	}
?>
<table width="100%" border="0" cellspacing="0" style="text-align:center">
<tr>
<td align="right" width="22px">&nbsp;</td>
<td width="300px" style="padding:0px">

<div class="pluginbox" style="border-bottom:none"><a href="./?f=plugins&x=doctype&z=0&boxed=0"><?php echo $lang_admincp['Plugin here']; ?></a></div>

</td>
<td align="left"></td>
</tr>
<tr>
<td align="right" width="22px">&nbsp;</td>
<td width="300px" style="border:solid 1px gray; background:#E5E5E5"  class="templateboxes"><a href="./?f=stylemanager&template=doctype">DocType</a></td>
<td align="left"><strong>&lt;html&gt;&lt;head&gt;</strong></td>
</tr>
<tr>
<td align="right" width="22px">&nbsp;</td>
<td width="300px" style="padding:0px">
<div class="pluginbox" style="border-top:none;border-bottom:none"><a href="./?f=plugins&x=head&z=0&boxed=0"><?php echo $lang_admincp['Plugin here']; ?></a></div>
</td>
<td align="left"></td>
</tr>

<tr>
<td align="right">&nbsp;</td>
<td style="border:solid 1px gray; background:#E5E5E5" class="templateboxes"><a href="./?f=stylemanager&template=head">Head section</a></td>
<td align="left"></td>
</tr>
<tr>
<td align="right" width="22px">&nbsp;</td>
<td width="300px" style="padding:0px">
<div class="pluginbox" style="border-top:none;border-bottom:none"><a href="./?f=plugins&x=bodytag&z=0&boxed=0"><?php echo $lang_admincp['Plugin here']; ?></a></div>
</td>
<td align="left"></td>
</tr>
<tr>
<td align="right">&nbsp;</td>
<td  style="border:solid 1px gray; background:#E5E5E5" class="templateboxes"><a href="./?f=stylemanager&template=bodytag">Body Tag</a></td>
<td align="left"><strong>&lt;/head&gt;&lt;body&gt;</strong> (important!)</td>
</tr>
<tr>
<td align="right" width="22px">&nbsp;</td>
<td width="300px" style="padding:0px">
<div class="pluginbox" style="border-top:none;border-bottom:none"><a href="./?f=plugins&x=header&z=0&boxed=0"><?php echo $lang_admincp['Plugin here']; ?></a></div>
</td>
<td align="left"></td>
</tr>
<tr>
<td align="right">&nbsp;</td>
<td style="border:solid 1px #1C8DFF; background:#CCE6FF" class="templateboxes"><a href="./?f=stylemanager&template=header">Header</a></td>
<td align="left">&nbsp;</td>
</tr>
<tr>
<td align="right" width="22px">&nbsp;</td>
<td width="300px" style="padding:0px">
<div class="pluginbox" style="border-top:none"><a href="./?f=plugins&x=header&z=1&boxed=0"><?php echo $lang_admincp['Plugin here']; ?></a></div>



</td>
<td align="left"></td>
</tr>
<?php echo $parts_order[0]; ?>
<?php echo $parts_order[1]; ?>
<?php echo $parts_order[2]; ?>
<?php echo $parts_order[3]; ?>
<?php echo $parts_order[4]; ?>
<?php echo $parts_order[5]; ?>
<?php echo $parts_order[6]; ?>
<tr>
<td align="right" width="22px">&nbsp;</td>
<td width="300px" style="padding:0px">
<div class="pluginbox" style="border-bottom:none"><a href="./?f=plugins&x=footer&z=0&boxed=0"><?php echo $lang_admincp['Plugin here']; ?></a></div>


</td>
<td align="left"></td>
</tr>
<tr>
<td align="right">&nbsp;</td>
<td style="border:solid 1px #1C8DFF; background:#CCE6FF" class="templateboxes"><a href="./?f=stylemanager&template=footer">Footer</a></td>
<td align="left"><strong>&lt;/body&gt;&lt;/html&gt;</strong></td>
</tr>
<tr>
<td align="right" width="22px">&nbsp;</td>
<td width="300px" style="padding:0px">
<div class="pluginbox" style="border-top:none"><a href="./?f=plugins&x=footer&z=1&boxed=0"><?php echo $lang_admincp['Plugin here']; ?></a></div>


</td>
<td align="left"></td>
</tr>
</table><br />

<?php
$stylelist=$sql1 = $db->query("SELECT styleid FROM ".TBL_TEMPLATE." GROUP BY styleid")or die($db->getLastError());
echo "<blockquote>";
while ($stylelist2=$db->getRow($stylelist))
{
	if ($stylelist2[0]==$config['engine_styleid'])
		echo '<b>'.$lang['Style'].' '.$stylelist2[0].' (<a href="./?f=stylemanager&delete='.$stylelist2[0].'">'. $lang_admincp['Delete'].'</a>)</b>';
	else
		echo $lang['Style'].' '.$stylelist2[0].' (<a href="./?f=stylemanager&delete='.$stylelist2[0].'">'. $lang_admincp['Delete'].'</a>)';
	echo '<br>';
}
echo "</blockquote>";
}

?>
