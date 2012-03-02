<?php
/************************************************************************
*															admincp/index.php
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

# INCLUDES:
# - initialization script
error_reporting(~E_NOTICE);
include "defines.php";
/* Initilaze stuff */
include PATHROOT."engine/init.php";

/* License */
if (trim($config['license'])=='') define('LICENSE','FREE');
else define('LICENSE',$config['license']);

/* for large scripts */
//@set_time_limit(0);

/* If no premission redirect to main page */
if(!$user->logged_in || !$user->isAdmin()){
	header('Location: ../index.php');
	exit;
}

/* Include admin functions */
include(PATHROOT.'engine/func/adminfunc.php');
include(PATHROOT."engine/lang/".strtolower($config['engine_lang'])."/admincp.php");
include(PATHROOT."engine/lang/".strtolower($config['engine_lang'])."/installer.php");

/* GET the function to use */
if (!isset($_GET['f'])) $_GET['f'] = false;
$function = preg_replace( "/[^A-Za-z0-9]/", "", $_GET['f'] ); //only letters and numbers
if (!$_GET['f'] or $_GET['f'] == '') $function='main';

/* Start page */
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title><?php echo $lang_admincp['Admin Control Panel']; ?></title>

<script type="text/javascript" src="<?php echo PATHROOT; ?>engine/js/jquery-1.7.1.min.js"></script>
<script type="text/javascript" src="<?php echo PATHROOT; ?>engine/js/jquery-ui-1.8.min.js"></script>
<script type="text/javascript">var PATHROOT = '<?php echo PATHROOT;?>';</script>
<script type="text/javascript" src="<?php echo PATHROOT; ?>engine/js/power.js"></script>
<script type="text/javascript" src="./res/highlight/js/codemirror.js"></script>
<link rel="stylesheet" type="text/css" href="./res/acpstyle.css" />

<script type="text/javascript">
function confirmSubmit()
{
	var agree = confirm("<?php echo $lang_admincp['Are you sure you wish to continue?']; ?>");
	if (agree)
		return true;
	return false;
}

function addmore(id)
{
	id2 = id + 1;
	document.getElementById('1addmore'+id).innerHTML='<input name="conf_name[]" id="conf_name'+id+'" value="" style="width: 200px;" type="text"><div style="height:3px"></div></div><div id="1addmore'+id2+'"><a href="javascript:void();" onclick="javascript:addmore('+id2+');return false;">[+<?php echo $lang_admincp['add more']; ?>]</a>';
	document.getElementById('2addmore'+id).innerHTML='<input name="conf_value[]" id="conf_value'+id+'" value="" style="width: 200px;" type="text">&nbsp;&nbsp;<?php echo $lang_admincp['Note']; ?>: <input name="conf_descr[]" id="conf_descr'+id+'" value="" style="width: 200px;" type="text"><div style="height:3px"></div></div><div id="2addmore'+id2+'">';
}
function addmore2(id)
{
	id2 = id + 1;
	document.getElementById('1addmore'+id).innerHTML='<div style="height:160px"><input name="title[]" id="conf_name'+id+'" value="" style="width: 200px;" type="text"></div></div><div id="1addmore'+id2+'"><a href="javascript:void();" onclick="javascript:addmore2('+id2+');return false;">[+<?php echo $lang_admincp['add more']; ?>]</a>';
	document.getElementById('2addmore'+id).innerHTML='<textarea id="csscode'+id+'" name="template[]" style="width:96%; height:150px"></textarea><div style="height:10px"></div></div></div><div id="2addmore'+id2+'">';

	var editor = CodeMirror.fromTextArea('csscode'+id, {
		height: "150px",
		parserfile: [ "parsecss2.js" ],
		stylesheet: [ "res/highlight/css/csscolors.css" ],
		path: "res/highlight/js/",
		continuousScanning: 2000,
		lineNumbers: true
	});
}

function addmorelink(id)
{
	id2 = id + 1;
	document.getElementById('addmorelink'+id).innerHTML='<div style="background-color:#ECECEC" class="linkmanager"><?php echo $lang_admincp['Title']; ?>: <input name="linktitle[]" type="text" value="" /> URL: <input name="linkurl[]" type="text" value="" /> <?php echo $lang_admincp['Order']; ?>: <input name="linkorder[]" type="text" value="" style="width:30px" /> <i><?php echo $lang_admincp['Group']; ?> <input name="linkgrup[]" type="text" value="" style="width:100px" /></i><div style="height:10px"></div> <?php echo $lang_admincp['Description']; ?>: <input name="linkdescr[]" type="text" value="" style="width:200px" /> <?php echo $lang_admincp['Viewable']; ?>: <select name="linkprems[]"><option value="0"><?php echo $lang_admincp['All']; ?></option><option value="1"><?php echo $lang_admincp['Guests']; ?></option><option value="2"><?php echo $lang_admincp['All logged in']; ?></option><option value="4">&nbsp;&nbsp;<?php echo $lang_admincp['Only Admins']; ?></option><option value="5">&nbsp;&nbsp;<?php echo $lang_admincp["Only GM's and Admins"]; ?></option></select></div><div id="addmorelink'+id2+'"><a href="javascript:void();" onclick="javascript:addmorelink('+id2+');return false;">[+<?php echo $lang_admincp['add more']; ?>]</a>';
}
</script>
</head>

<body>
<table width="100%" border="0">
	<tr>
		<td width="91px"><img src="res/logo.png" alt="WWCMS v2" title="WWCMS v2"  width="91" /></td>
		<td> <?php echo $lang_admincp['Welcome to WWCMS v2'].' '.$lang_admincp['Admin Control Panel']; ?></td>
	</tr>
</table>

<div id="header">
	<div id="trcorner">
		<div id="tlcorner">
			<div id="brcorner">
				<div id="blcorner">
					<div id="headerinner">
						<small><a href="<?php echo PATHROOT; ?>index.php"><?php echo $lang_admincp['Main Page']; ?></a> | <a href="<?php echo PATHROOT; ?>index.php?page=loginout"><?php echo $lang['Logout']; ?></a> | <a href="<?php echo PATHROOT; ?>engine/documentation/readme.txt"><?php echo $lang_admincp['Help']; ?></a>
							<noscript> | <font color="#FF8A8A"><?php echo $lang['JavaScript is disabled, please enable JavaScript.']; ?></font></noscript>
						</small>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<table width="100%" border="0">
	<tr>
		<td width="200px" valign="top">
			<b class="menu_round"><b class="menu_round_l3"></b><b class="menu_round_l2"></b><b class="menu_round_l1"></b></b>
			<div id="menu_content"><div style="height:5px"></div><?php $adminfunc->printmenu(); ?></div>
			<b class="menu_round"><b class="menu_round_l1"></b><b class="menu_round_l2"></b><b class="menu_round_l3"></b></b>
			<div style="height:14px"></div>
			<b class="menu_round"><b class="menu_round_l3"></b><b class="menu_round_l2"></b><b class="menu_round_l1"></b></b>
			<div id="menu_content"><div style="height:5px"></div>
				<center>
				<a href="javascript:void(0);" onclick="$.get('<?php echo PATHROOT; ?>index.php?cache=true&noreload=1', function(data) { $('#cache_recache').html(data); })" >[<?php echo $lang_admincp['Recache Website']; ?>]</a>
				</center><br />
				<div id="cache_recache"></div>
			</div>
			<b class="menu_round"><b class="menu_round_l1"></b><b class="menu_round_l2"></b><b class="menu_round_l3"></b></b>
		</td>
		<td style="padding-left:10px" valign="top">
			<b class="menu_round"><b class="menu_round_l3"></b><b class="menu_round_l2"></b><b class="menu_round_l1"></b></b>
			<div id="menu_content"><?php
				@$adminfunc->$function();
				if(Form::$num_errors > 0)
					echo "<br><span class='error'>".Form::$num_errors."</span>";
				?>
			</div>
			<b class="menu_round"><b class="menu_round_l1"></b><b class="menu_round_l2"></b><b class="menu_round_l3"></b></b>
		</td>
	</tr>
</table>
<?php
echo '<small><center>'.$lang['Page generated'].': '.sprintf("%.4f",get_microtime()-TIMESTART).' | '.$lang['Queries executed'].': '.database::$num_queries.'</center></small>';
if (($_GET['f'] == 'stylemanager' or $_GET['f'] == 'plugins') && (isset($_GET['template']) or (isset($_GET['x']) && !isset($_GET['noparse'])) )){
?>
<script type="text/javascript">
var editor = CodeMirror.fromTextArea('code112', {
	height: "350px",
	parserfile: ["parsexml.js", "parsecss.js", "tokenizejavascript.js", "parsejavascript.js",
	"../contrib/php/js/tokenizephp.js", "../contrib/php/js/parsephp.js",
	"../contrib/php/js/parsephphtmlmixed.js"],
	stylesheet: ["res/highlight/css/xmlcolors.css", "res/highlight/css/jscolors.css", "res/highlight/css/csscolors.css", "res/highlight/contrib/php/css/phpcolors.css"],
	path: "res/highlight/js/",
	continuousScanning: 1000,
	lineNumbers: true
});
</script>
<?php
}
?>
</body>
</html>
