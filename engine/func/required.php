<?php

###################################################################
# This file is a part of OpenWoW CMS by www.openwow.com
#
#   Project Owner    : OpenWoW CMS (http://www.openwow.com)
#   Copyright        : (c) www.openwow.com, 2010
#   Credits          : Based on work done by AXE and Maverfax
#   License          : GPLv3
##################################################################

############################################################
# GET's
#	- cache ->initiates all files caching (only if admin)
############################################################

class Html {

	var $page_output;

	function _construct()
	{
		global $lang,$db,$config,$user,$error_reporting_cache;

		// cache it to file and then just INCLUDE that file (this will allow php)
		if ((!file_exists('./engine/_cache/cache_page.php') or !file_exists('./engine/_cache/cache_menulinks.php') or !file_exists('./engine/_cache/cache_vote_loggedin.php') or !file_exists('./engine/_cache/cache_vote_loggedout.php')) or (isset($_GET['cache']) && strtolower($user->userlevel) == strtolower($config['premission_admin'])))
		{
			// CACHE PAGE START
			error_reporting($error_reporting_cache);

			// construct string ($this->page_output), clear it with false first:
			// include plugins:
			$plugins_inc = $this->includeplugins();

			// load page structure from database (boxes only) -> precache them:
			$boxes_sql = $db->query("SELECT * FROM ".$config['engine_web_db'].".wwc2_template WHERE styleid='".$config['engine_styleid']."' AND ( title='box_start0' OR title='box_end0' OR title='box_start1' OR title='box_end1' OR  title='box_start2' OR title='box_end2' OR title='box_start3' OR title='box_end3' OR title='html_order')")or die(mysql_error());

			while ($template_boxes = mysql_fetch_assoc($boxes_sql))
			{
				$page_output_boxes[$template_boxes['title']] = $template_boxes['template'];

				if ($template_boxes['title'] == 'html_order')
				{
					$page_output_html_order = $template_boxes['template'];	
				}
			}

			// load page structure from database (everything):
			$template_sql = $db->query("SELECT * FROM ".$config['engine_web_db'].".wwc2_template WHERE styleid='".$config['engine_styleid']."'")or die(mysql_error());

			while ($template = mysql_fetch_assoc($template_sql))
			{
				############################
				# START CONSTRUCTING PAGE: #
				############################
				/**
				* Limitations: Only 3 bodies, every body have its own box_startX and box_endX
				* other 3 bodies dont have wrappers - used for formatting table elements.
				* PLUGINS: Can be placed on any element except wrappers. Boxed content only on
				* elements: body1,body2,body3.
				*/
				$page_output[$template['title']] = $template['template'];

				if ($plugins_inc[$template['title']][0] != false) // yes plugin
				{
					$i=0;

					while ($plugins_inc[$template['title']][$i] != false)// make loop trough plugins in same block
					{
						$plugins_inc[$template['title']][$i] = explode('-',$plugins_inc[$template['title']][$i]); //make array from order-updown

						switch ($template['title'])
						{
							case "body1":
								$thisbox['start'] = $page_output_boxes['box_start1'];
								$thisbox['end'] = $page_output_boxes['box_end1'];
							break;

							case "body2":
								$thisbox['start'] = $page_output_boxes['box_start2'];
								$thisbox['end'] = $page_output_boxes['box_end2'];
							break;

							case "body3":
								$thisbox['start'] = $page_output_boxes['box_start3'];
								$thisbox['end'] = $page_output_boxes['box_end3'];
							break;

							default:
								$thisbox['start'] = '';
								$thisbox['end'] = '';
						}

						if ($plugins_inc[$template['title']][$i][1] == '0')//if before content
						{
							if ($plugins_inc[$template['title']][$i][2] == '1')//if template boxed
							{
								//echo \'<span style="border:solid 1px red; width:100px; position:absolute;float:right">Edit plugin</span>\';
								$page_output[$template['title']]  = $thisbox['start'].'<?php @include("./engine/plugins/';
								$page_output[$template['title']] .= $template['title'].'-'.$plugins_inc[$template['title']][$i][0];
								$page_output[$template['title']] .= '-'.$plugins_inc[$template['title']][$i][1].'-';
								$page_output[$template['title']] .= $plugins_inc[$template['title']][$i][2].'.php"); ?>'.$this->ln();
								$page_output[$template['title']] .= $thisbox['end'].$page_output[$template['title']];
							}

							else//pure plugin without template box around it
							{
								$page_output[$template['title']]  = '<?php @include("./engine/plugins/'.$template['title'];
								$page_output[$template['title']] .= '-'.$plugins_inc[$template['title']][$i][0];
								$page_output[$template['title']] .= '-'.$plugins_inc[$template['title']][$i][1];
								$page_output[$template['title']] .= '-'.$plugins_inc[$template['title']][$i][2].'.php"); ?>';
								$page_output[$template['title']] .= $this->ln() . $page_output[$template['title']];
							}
						}

						else//else after content
						{
							if ($plugins_inc[$template['title']][$i][2]=='1')//if template boxed
							{
								$page_output[$template['title']]=$page_output[$template['title']].$thisbox['start'].'<?php @include("./engine/plugins/'.
								$template['title'].
								'-'.$plugins_inc[$template['title']][$i][0].
								'-'.$plugins_inc[$template['title']][$i][1].
								'-'.$plugins_inc[$template['title']][$i][2].'.php"); ?>'.$this->ln().$thisbox['end'];
							}

							else
							{
								$page_output[$template['title']]=$page_output[$template['title']].'<?php @include("./engine/plugins/'.
								$template['title'].
								'-'.$plugins_inc[$template['title']][$i][0].
								'-'.$plugins_inc[$template['title']][$i][1].
								'-'.$plugins_inc[$template['title']][$i][2].'.php"); ?>'.$this->ln();								
							}

						}

						$i++;
					}
				}

				############################
				#  END  CONSTRUCTING PAGE: #
				############################
			}

			// wwcmsv2
			if (file_exists(PATHROOT.'engine/note.php'))
				$wwcmsv2='<?php include_once(PATHROOT.\'engine/note.php\'); ?>';
			else
				$wwcmsv2='';

			// mainparts order
			if ($page_output_html_order=='')
				$page_output_html_order='1230';

			$page_output['body0'] = $page_output['box_start0'].$this->ln().'<!--INC MODULE START-->'.$this->ln().'<?php $Html->includemodule();  ?>'.$this->ln().'<!--INC MODULE END-->'.$this->ln().$page_output['box_end0'].$this->ln();

			if (file_exists(PATHROOT.'engine/_style_res/'.$config['engine_styleid'].'/stylesheet.css'))
				$stylesheet='<link rel="stylesheet" type="text/css" href="./engine/_style_res/'.$config['engine_styleid'].'/stylesheet.css">';
			else
				$stylesheet='';

			$stylesheet .= '<script src="./engine/js/jquery-1.4.2.min.js" type="text/javascript"></script>'.$this->ln().'<script src="./engine/js/power.js" type="text/javascript"></script>'.$this->ln().'<link rel="stylesheet" type="text/css" href="./engine/js/power/power.css">'.$this->ln();

			// merge all
			$this->page_output= '<?php global $Html,$user,$form; $Html->includemodule_proccess(); ?>'.$page_output['doctype'].$this->ln().
			$page_output['head'].$this->ln().$stylesheet.$this->ln().
			$page_output['bodytag'].$this->ln().$wwcmsv2.
			$page_output['header'].$this->ln().
			$page_output['body'.$page_output_html_order[0]].$this->ln(). //here belongs right left menus, boxes, etc
			$page_output['body'.$page_output_html_order[1]].$this->ln(). //here belongs right left menus, boxes, etc
			$page_output['body'.$page_output_html_order[2]].$this->ln(). //here belongs right left menus, boxes, etc
			$page_output['body'.$page_output_html_order[3]].$this->ln(). //here belongs right left menus, boxes, etc
			$page_output['body'.$page_output_html_order[4]].$this->ln(). //here belongs right left menus, boxes, etc
			$page_output['body'.$page_output_html_order[5]].$this->ln(). //here belongs right left menus, boxes, etc
			$page_output['body'.$page_output_html_order[6]].$this->ln(). //here belongs right left menus, boxes, etc
			$page_output['footer'];

			echo '<font face="Arial"><small>';

			if (!isset($_GET['noreload']))
				echo 'Caching...';
			else
				echo 'Caching done!';

			echo "<br>";

			// cache: cache_page.php
			if (!$this->cache($this->page_output,PATHROOT.'engine/_cache/cache_page.php'))
				echo "<font color=red>cache_page.php</font><br>";
			else
				echo "<font color=green>cache_page.php</font><br>";;

			// cache: config.php
			$connected = true;
			$string = "<?php" .$this->ln(). '$config=array(' . $this->ln();

			$sql1 = $db->query("SELECT * FROM ".$config['engine_web_db'].".wwc2_config") or $connected = false;

			if ($connected)
			{
				while ($sql2 = $db->fetch_array($sql1))
				{
					$string .= "'".$sql2[0]."' => '".$sql2[1]."'," . $this->ln();
				}

				$string .= ");" .  $this->ln() .  $this->ln() . "define('AXE',1);" . $this->ln() . $this->ln();
			}

			else
			{
				echo "<font color=red>config.php</font><br>";
			}

			if (!$this->cache($string,PATHROOT.'config/config.php'))
				echo "<font color=red>config.php</font><br>";
			else
				echo "<font color=green>config.php</font><br>";

			// cache: stylesheet.css
			$connected = true;
			$string = '/* Autogenerated CSS Document, this file is part of WWC v2 by AXE */' . $this->ln();

			$sql1 = $db->query("SELECT title,template FROM ".$config['engine_web_db'].".wwc2_template WHERE styleid='".$config['engine_styleid']."' AND templatetype='css'")or $connected=false;

			if($connected)
			{
				while ($sql2 = $db->fetch_array($sql1))
				{
					$string .= $sql2[0]." { ".$sql2[1]." }" . $this->ln();
				}

				if (!$this->cache($string,PATHROOT.'engine/_style_res/'.$config['engine_styleid'].'/stylesheet.css'))
					echo "<font color=red>stylesheet.css</font><br>";
				else
					echo "<font color=green>stylesheet.css</font><br>";
			}

			else
			{
				echo "<font color=red>stylesheet.css</font><br>";
			}

			// Cache menu links now
			// engine/_cache/cache_menulinks.php
			if (!$this->cache_menulinks())
				echo "<font color=red>cache_menulinks.php</font><br>";
			else
				echo "<font color=green>cache_menulinks.php</font><br>";

			// Cache vote links now
			// engine/_cache/cache_vote_loggedin.php and engine/_cache/cache_vote_loggedout.php
			if (!$this->cache_vote())
				echo "<font color=red>cache_vote_loggedoin.php<br>cache_vote_loggedout.php</font><br>";
			else
				echo "<font color=green>cache_vote_loggedoin.php<br>cache_vote_loggedout.php</font><br>";

			// End Caching
			// Here we will once more reload page using meta reload, sometimes it needs
			// few reloads until all files are cached properly, reason unknown.

			echo $lang['Caching done in']." ".round((microtime()-TIMESTART),3)."s!</small></font><br>";

			if (!isset($_GET['noreload']))
				echo '<meta http-equiv="refresh" content="0;url=./?cache=true&noreload=1" />';

			return;
		}

		else
		{
			if (file_exists(PATHROOT.'engine/_cache/cache_menulinks.php'))
			{
				include_once(PATHROOT.'engine/_cache/cache_menulinks.php');
			}

			include_once(PATHROOT.'engine/_cache/cache_page.php');
		}
	}

function lang_selection($selected) #returns
{
$out = '';
$dir=PATHROOT.'engine/lang/';
if (is_dir($dir)) {
if ($dh = opendir($dir)) {
while (($file = readdir($dh)) !== false) {
if ($file<>'..' && $file<>'.')
{
if ($file<>'index.html')
{
if (strtolower($file)==strtolower($selected))
$out.= "<option value='".$file."' selected='selected' style='font-weight:bold'>".ucwords($file)."</option>";
else
$out.= "<option value='".$file."'>".ucwords($file)."</option>";
}
}
}
closedir($dh);
}
}
return '<select id="lang" name="lang">'.$out.'</select>';
}
function cache($string,$file)
{
$error=false;
/* attempt to create file */
$fh = @fopen( $file, "w" ) or $error=true;

/* CHMOD CHECK */
if (!@is_writable($file)) {
if (!@chmod($file, 0666)) {
$error=true;
};
}
if ($string=='') $string='<?php'. $this->ln() . '/* This file is auto-generated because cache script was initiated */'. $this->ln().'/* and content was empty. ( '.$file.' ) */' . $this->ln(). '/* This file is part of Web-WoW CMS v2 all rights reserved. */' . $this->ln().'?>';
@fwrite( $fh, $string );
@fclose( $fh );
if ($error) {@chmod($file, 0664);return false;}
else {@chmod($file, 0664);return true;}
}


function cache_menulinks(){ #returns true on success else false
global $db;
/**
* Build content
*/
$out = "<?php" .$this->ln(). '/* Autogenerated file by Link Manager (WWCMSv2) */'.$this->ln().'function menulinks($grup,$sep) { global $user,$config;' . $this->ln();

$sql1 = $db->query("SELECT * FROM ".TBL_LINKS." ORDER BY linkgrup ASC, linkorder ASC")or die($db->error('error_msg'));
$grup='no_grup_defined_start';
while ($sql2=mysql_fetch_assoc($sql1))
{
/**
* If new grup is started:
*/
if ($grup<>$sql2['linkgrup']){
if ($grup<>'no_grup_defined_start') {
$out.='return $out;';
$out.= $this->ln().''.$this->ln().'}'. $this->ln();

}

$out.='if ($grup=="'.$sql2['linkgrup'].'") { '. $this->ln() . '$i=0;$out=false;'. $this->ln();
$grup=$sql2['linkgrup'];

}

/**
* print links, add premission code here
*/
if ($sql2['linkprems']=='1') //guests
{
$stringstart=' if(!$user->logged_in){'.$this->ln();
$stringend=' } ';
}
elseif($sql2['linkprems']=='2') //logged in - all
{
$stringstart=' if($user->logged_in){'.$this->ln();
$stringend=' } ';
}
elseif($sql2['linkprems']=='3') //logged in - normal users
{
$stringstart=' if(strtolower($user->userlevel)==\'0\'){'.$this->ln();
$stringend=' } ';
}
elseif($sql2['linkprems']=='4') //logged in - admin
{
$stringstart=' if(strtolower($user->userlevel)==strtolower($config[\'premission_admin\'])){'.$this->ln();
$stringend=' } ';
}
elseif($sql2['linkprems']=='5') //logged in - gms and admins
{
$stringstart=' if(strtolower($user->userlevel)==strtolower($config[\'premission_admin\']) or strtolower($user->userlevel)==strtolower($config[\'premission_gm\'])){'.$this->ln();
$stringend=' } ';
}



$out.=$stringstart.$this->ln().' if ($i==1) $out.= $sep;$i=1; '.$this->ln().'$out.= \'<span class="menulink'.$sql2['linkgrup'].'">'.$this->ln().'	<a href="'.$sql2['linkurl'].'"';
if ($sql2['linkdescr']<>'')
$out.=' onmouseout="$WowheadPower.hideTooltip();" onmousemove="$WowheadPower.moveTooltip(event)" onmouseover="$WowheadPower.showTooltip(event, \\\''.$sql2['linkdescr'].'\\\')"';
$out.='>'.$sql2['linktitle'].'</a>'.$this->ln().'</span>\';';

$out.=$stringend.$this->ln();



$stringstart='';
$stringend='';

}
$out.='return $out;';
$out.= ''.$this->ln().'}'.$this->ln().'}';

/**
* Initiate cache now:
*/
if ($this->cache($out,PATHROOT.'engine/_cache/cache_menulinks.php'))
return true;
else
return false;
}

function cache_vote(){ #returns true on success else false
global $db;
/**
* Build content
*/
$out1 = "<?php" .$this->ln(). '/* Autogenerated file by Global Caching (WWCMSv2) */'.$this->ln().'?>';
$out2 = $out1;
$sql1 = $db->query("SELECT * FROM ".TBL_CONFIG." WHERE conf_name LIKE 'vote_link_%' ORDER BY conf_name ASC")or die($db->error('error_msg'));
while ($sql2=mysql_fetch_assoc($sql1))
{
$id=preg_replace( "/vote_link_/", "", $sql2['conf_name'] );
$out1.='<span class="votelink" id="'.$sql2['conf_name'].'"><a href="'.$sql2['conf_value'].'" target="_blank" onclick="ajax_loadContent(\''.$sql2['conf_name'].'\',\'./engine/dynamic/vote_proccess.php?id='.$sql2['conf_name'].'\',\'<img src=./engine/_style_res/<?php echo $config[\'engine_styleid\'];?>/images/voteimg/'.$id.'.gif alt=[<?php echo $lang[\'Vote\'];?>]>\');"><img src="./engine/_style_res/<?php echo $config[\'engine_styleid\'];?>/images/voteimg/'.$id.'.gif" alt="[<?php echo $lang[\'Vote\'];?>]"></a></span>';
//for guests:
$out2.='<span class="votelink"><a href="'.$sql2['conf_value'].'" target="_blank"><img src="./engine/_style_res/<?php echo $config[\'engine_styleid\'];?>/images/voteimg/'.$id.'.gif" alt="[<?php echo $lang[\'Vote\'];?>]"></a></span>';


}

/**
* Initiate cache now:
*/
if ($this->cache($out1,PATHROOT.'engine/_cache/cache_vote_loggedin.php') && $this->cache($out2,PATHROOT.'engine/_cache/cache_vote_loggedout.php'))
return true;
else
return false;
}
function ln() #returns
{
# linebreak
$system = strtolower( PHP_OS );
if (strtoupper(substr($system, 0, 3)) == 'WIN' )
return "\r\n";
else if ( strtoupper(substr($system, 0, 3)) == 'MAC' )
return "\r";
else
return "\n";
}
function includeplugins() #returns array
{
$out=array();
if ($handle = opendir(PATHROOT.'engine/plugins/'))
{
while (false !== ($file = readdir($handle)))
{
$file = preg_replace( "/[^A-Za-z0-9-.]/", "", $file );
if (substr($file,-4,4)=='.php')
{
$file = preg_replace( "/.php/", "", $file );
$file= explode("-",$file); #we have array now

if ($out[$file[0]][0]==false)
$out[$file[0]][0]=$file[1].'-'.$file[2].'-'.$file[3];
else
$out[$file[0]][count($out[$file[0]])]=$file[1].'-'.$file[2].'-'.$file[3];
}
}
closedir($handle);
}
return $out;
}

function includemodule() //finish this
{
global $user;
$module = '';

if(isset($_GET['page']) && !empty($_GET['page']))
{
$module = preg_replace('/[^A-Za-z0-9_-]/', '', $_GET['page']); //only letters and numbers

if(file_exists('./engine/modules/'.$module.'.php'))
{
include('./engine/modules/' . $module . '.php');
}

else
{
echo 'CODE 404';
}
}

else
{
include('./engine/news.php');
}
}
function includemodule_proccess()
{
$module = '';$proccess = false;

if(isset($_GET['page']) && !empty($_GET['page']))
{
$module = preg_replace('/[^A-Za-z0-9_-]/', '', $_GET['page']); //only letters and numbers

if(file_exists('./engine/modules/'.$module.'.php'))
{
$proccess = true;
include('./engine/modules/' . $module . '.php');
}
}
}

	function credits($style=false) #returns
	{
		global $db,$lang,$config,$user;

		if ($style)
		{
			$style=$style.' | ';
		}

		if ($config['footer_detail'] == '1' or ($config['footer_detail'] == '2' && $user->userlevel == $config['premission_admin']))
		{
			return $lang['Page generated'].': '.round((microtime()-TIMESTART),2).' | '.$lang['Queries executed'].': '.$db->num_queries.' | '.$lang['Copyright'].' &copy; 2010-2011 | '.$style.$lang['Powered by'].': <a href="./?page=credits" title="">WWCv2</a>  | <a href="tos.php">'.$lang['Terms of Use'].'</a>';
		}

		else
		{
			return $lang['Copyright'].' &copy; 2010-2011 | '.$style.$lang['Powered by'].': <a target="_blank" href="http://www.web-wow.net/" title="">WWCv2</a>  | <a href="tos.php">'.$lang['Terms of Use'].'</a>';	
		}
	}

	function portcheck($sep,$port=false,$server=false) #returns text or returns '' if fockopen doesn't exists
	{
		global $config;

		$out = '';

		if(function_exists("fsockopen"))
		{
			if (!$port && !$server)
			{
				//loop trough realms and get statuses
				$config['engine_char_dbs'] = explode(';',$config['engine_char_dbs']);

				$i   = 0;
				$sep = explode("|",$sep);

				foreach($config['engine_char_dbs'] as $realms)
				{
					$realm_data = explode("|",$realms);

					if (!isset($realm_data[2]) || $realm_data[2] =='')
					{
						$realm_data[2]='127.0.0.1';
					}

					$out .= $sep[$i].'<span id="portcheck'.$i.'">--</span><script type="text/javascript">ajax_loadContent(\'portcheck'.$i.'\',\'./engine/dynamic/status.php?port='.$realm_data[1].'&ip='.$realm_data[2].'\',\'--\');</script>';

					$i++;
				}

				return $out;
			}

			else
			{
				return '<span id="portcheck_port'.$port.'">--</span><script type="text/javascript">ajax_loadContent(\'portcheck1\',\'./engine/dynamic/status.php?port='.$port.'&ip='.$server.'\',\'--\');</script>';
			}
		}

		else
		{
			return '';
		}
	}

	/**
	* moduleinstall()
	* Selfinstallation for modules, basically this script adds configuration variables to database, so
	* admin can easy access to module setup.
	**/
	function moduleinstall($checkkey,$variables_array,$values_array,$descriptions_array,$sql_execute)
	{
		global $config, $proccess, $user, $db;

		if (!array_key_exists($checkkey, $config) && $checkkey != '')
		{
			if (strtolower($user->userlevel) == strtolower($config['premission_admin']))
			{
				$i=0;

				foreach ($variables_array as $variables_array2)
				{
					$a = $db->query("SELECT * FROM ".TBL_CONFIG." WHERE conf_name='".$variables_array2."'")or die($db->error('error_msg'));

					if ($db->num_rows($a)=='0')
					{
						$db->query("INSERT INTO ".TBL_CONFIG." (conf_name,conf_value,conf_descr) VALUES ('".$variables_array2."','".$values_array[$i]."','".$descriptions_array[$i]."')")or die($db->error('error_msg'));	
					}

					$i++;
				}

				$db->select_db($config['engine_web_db']);

				foreach ($sql_execute as $sql_execute2)
				{
					if ($sql_execute2!='')
					{
						$db->query($sql_execute2)or print("SQL REPORT: ".$db->error('error_msg')."<br>");	
					}
				}

				echo '<div style="padding:4px;  background:white;color:black;text-align:center; border:solid 1px black">';
				echo 'This module is now installed, please go to:<br>Administration Panel &gt; Configuration Variables<br />';
				echo 'and setup variables for this module.<br />After you recache page this message will go away.</div>';
			}

			else
			{
				echo "<div style='padding:4px; background:white;color:black;text-align:center; border:solid 1px black'>Admin needs to install this module first. If you are admin, please login with your admin account and revisit this module page.</div>";
			}

			return true;
		}

		else
		{
			return false;	
		}
	}

	function credits_cms()
	{
		global $lang_admincp, $config;

		echo "<h2>".$lang_admincp['Credits']."</h2><i>".$lang_admincp['Main'].":</i><blockquote>Website is made by <strong>Axe</strong> from <strong>".WEBWOW."</strong></blockquote><i>";
		echo $lang_admincp['Used 3rd party scripts'].":</i><blockquote>Code highlight script: <strong>CodeMirror</strong> written by Franciszek Wawrzak<br>";
		echo "Tooltips: <strong>WowHead</strong>'s javascript wow item's tooltip script (www.wowhead.com)<br>";
		echo "Javascript Engine: <strong>jQuery</strong> (jquery.com)</blockquote><i>Contributers:</i><blockquote>Maverfax - debugging during the beta phase</blockquote>";
	}

	function formatmoney($query_a)
	{
		$a = false;
		$gold = substr($query_a, 0, -4);

		if ($gold >= 1)
		{
			$a = '' . number_format($gold) . ' <img src="./engine/res/money_gold.gif" /> ';
		}

		$silver  = substr($query_a, 0, -2);
		$silver2 = substr($silver, -2);

		if ($silver2 >= 1)
		{
			$a .= '' . number_format($silver2).' <img src="./engine/res/money_silver.gif" /> ';
		}

		$copper = substr($query_a, -2);

		if ($copper != '0' or $copper != '00')
		{
			$a .= ''. number_format($copper).'</span> <img src="./engine/res/money_copper.gif" /> ';
		}

		return $a;
	}
}

$Html = new Html;

function error($message, $file, $line, $db_error = false)
{
	// Empty output buffer and stop buffering
	@ob_end_clean();

	// "Restart" output buffering if we are using ob_gzhandler (since the gzip header is already sent)
	if (!empty($pun_config['o_gzip']) && extension_loaded('zlib') && (strpos($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip') !== false || strpos($_SERVER['HTTP_ACCEPT_ENCODING'], 'deflate') !== false))
	{
		ob_start('ob_gzhandler');	
	}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" dir="ltr">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>Website Error</title>
</head>
<body>
<div style="BORDER: 1px solid #B84623; font-family:Verdana, Arial, Helvetica, sans-serif; ">
<h2 style="MARGIN: 0; COLOR: #FFFFFF; BACKGROUND-COLOR: #B84623; FONT-SIZE: 1.1em; PADDING: 5px 4px;font-size:14px">An error was encountered</h2>
<div style="PADDING: 6px 5px; BACKGROUND-COLOR: #F1F1F1; color:#000000;font-size:11px">
<?php

	if (defined('PUN_DEBUG'))
	{
		echo "\t\t".'<strong>File:</strong> '.$file.'<br />'."\n\t\t".'<strong>Line:</strong> '.$line.'<br /><br />'."\n\t\t".'<strong>Website reported</strong>: '.$message."\n";

		if ($db_error)
		{
			echo "\t\t".'<br /><br /><strong>Database reported:</strong> '.pun_htmlspecialchars($db_error['error_msg']).(($db_error['error_no']) ? ' (Errno: '.$db_error['error_no'].')' : '')."\n";

			if ($db_error['error_sql'] != '')
			{
				echo "\t\t".'<br /><br /><strong>Failed query:</strong> '.pun_htmlspecialchars($db_error['error_sql'])."\n";	
			}
		}
	}

	else
	{
		echo "\t\t".'Error: <strong>'.$message.'.</strong>'."\n";
	}

?>
</div>
</div>

</body>
</html>
<?php

	// If a database connection was established (before this error) we close it
	if ($db_error)
	{
		$GLOBALS['db']->close();
	}

	exit;
}

//PHP 4.2.x Compatibility function
if( ! function_exists('file_get_contents'))
{
	function file_get_contents($filename, $incpath = false, $resource_context = null)
	{
		if (false === $fh = fopen($filename, 'rb', $incpath))
		{
			trigger_error('file_get_contents() failed to open stream: No such file or directory', E_USER_WARNING);

			return false;
		}

		clearstatcache();

		if ($fsize = @filesize($filename))
		{
			$data = fread($fh, $fsize);
		}

		else
		{
			$data = '';

			while (!feof($fh))
			{
				$data .= fread($fh, 8192);
			}
		}

		fclose($fh);

		return $data;
	}
}
?>