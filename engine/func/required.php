<?php
/************************************************************************
*													  engine/func/required.php
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

############################################################
# GET's
#	- cache ->initiates all files caching (only if admin)
############################################################

class Html {

	static $page_output;

	static function _construct() {
		global $user, $lang, $config, $db;
		if (isset($_GET['cache']) && $user->isAdmin()) return Html::recache_all();
		if (cachehandler::isCached("cache_page.php"))
		{
			cachehandler::loadCache("cache_menulinks.php");
			cachehandler::loadCache("cache_page.php");
		}
		else
			Html::recache_all();
	}

	static function recache_all() {
		global $lang, $db, $config, $user, $error_reporting_cache;
			// CACHE PAGE START
			//error_reporting($error_reporting_cache);

			// construct string (Html::$page_output), clear it with false first:
			// include plugins:
			$plugins_inc = Html::includeplugins();

			// load page structure from database (boxes only) -> precache them:
			$boxes_sql = $db->query("SELECT * FROM ".$config['engine_web_db'].".wwc2_template WHERE styleid='".$config['engine_styleid']."' AND ( title='box_start0' OR title='box_end0' OR title='box_start1' OR title='box_end1' OR  title='box_start2' OR title='box_end2' OR title='box_start3' OR title='box_end3' OR title='html_order')")or die($db->getLastError());

			while ($template_boxes = $db->getRow($boxes_sql))
			{
				$page_output_boxes[$template_boxes['title']] = $template_boxes['template'];
				if ($template_boxes['title'] == 'html_order')
				{
					$page_output_html_order = $template_boxes['template'];
				}
			}

			// load page structure from database (everything):
			$template_sql = $db->query("SELECT * FROM ".$config['engine_web_db'].".wwc2_template WHERE styleid='".$config['engine_styleid']."'")or die($db->getLastError());

			while ($template = $db->getRow($template_sql))
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

				if (isset($plugins_inc[$template['title']][0]) && $plugins_inc[$template['title']][0] != false) // yes plugin
				{
					$i=0;

					while (isset($plugins_inc[$template['title']][$i]) && $plugins_inc[$template['title']][$i] != false)// make loop trough plugins in same block
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
								$page_output[$template['title']] = $thisbox['start'].'<?php $isplugin = true; @include("./engine/plugins/'
								. $template['title'].'-'.$plugins_inc[$template['title']][$i][0]
								. '-'.$plugins_inc[$template['title']][$i][1].'-'
								. $plugins_inc[$template['title']][$i][2].'.php"); ?>'.Html::ln()
								 . $thisbox['end'] . $page_output[$template['title']];
							}
							else//pure plugin without template box around it
							{
								$page_output[$template['title']] = '<?php $isplugin = true; @include("./engine/plugins/'.$template['title']
								. '-'.$plugins_inc[$template['title']][$i][0]
								. '-'.$plugins_inc[$template['title']][$i][1]
								. '-'.$plugins_inc[$template['title']][$i][2].'.php"); ?>'
								. Html::ln() . $page_output[$template['title']];
							}
						}
						else//else after content
						{
							if ($plugins_inc[$template['title']][$i][2]=='1')//if template boxed
							{
								$page_output[$template['title']]=$page_output[$template['title']].$thisbox['start'].'<?php $isplugin = true; @include("./engine/plugins/'.
								$template['title'].
								'-'.$plugins_inc[$template['title']][$i][0].
								'-'.$plugins_inc[$template['title']][$i][1].
								'-'.$plugins_inc[$template['title']][$i][2].'.php"); ?>'.Html::ln().$thisbox['end'];
							}
							else
							{
								$page_output[$template['title']]=$page_output[$template['title']].'<?php $isplugin = true; @include("./engine/plugins/'.
								$template['title'].
								'-'.$plugins_inc[$template['title']][$i][0].
								'-'.$plugins_inc[$template['title']][$i][1].
								'-'.$plugins_inc[$template['title']][$i][2].'.php"); ?>'.Html::ln();
							}
						}

						$i++;
					}
				}

				############################
				#  END  CONSTRUCTING PAGE: #
				############################
			}

			// mainparts order
			if ($page_output_html_order == '') $page_output_html_order = '1230';

			$page_output['body0'] = $page_output['box_start0'].Html::ln().'<!--INC MODULE START-->'.Html::ln().'<?php Html::includemodule();  ?>'.Html::ln().'<!--INC MODULE END-->'.Html::ln().$page_output['box_end0'].Html::ln();

			$stylesheet = "";
			if (filehandler::isExists('stylesheet.css', 'engine/_style_res/'.$config['engine_styleid']))
				$stylesheet .= '<link rel="stylesheet" type="text/css" href="./engine/_style_res/'.$config['engine_styleid'].'/stylesheet.css">'.Html::ln();

			$stylesheet .=
			'<script type="text/javascript" src="./engine/js/jquery-1.7.1.min.js"></script>'.Html::ln().
			'<script type="text/javascript" src="./engine/js/jquery-ui-1.8.min.js"></script>'.Html::ln().
			'<script src="./engine/js/power.js" type="text/javascript"></script>'.Html::ln().
			'<link rel="stylesheet" type="text/css" href="./engine/js/power/power.css">'.Html::ln();

			// merge all
			Html::$page_output= '<?php global $user, $lang, $db, $config; Html::includemodule_proccess(); ?>'.$page_output['doctype'].Html::ln().
			$page_output['head'].Html::ln().$stylesheet.Html::ln().
			$page_output['bodytag'].Html::ln().
			$page_output['header'].Html::ln().
			$page_output['body'.$page_output_html_order[0]].Html::ln(). //here belongs right left menus, boxes, etc
			$page_output['body'.$page_output_html_order[1]].Html::ln(). //here belongs right left menus, boxes, etc
			$page_output['body'.$page_output_html_order[2]].Html::ln(). //here belongs right left menus, boxes, etc
			$page_output['body'.$page_output_html_order[3]].Html::ln(). //here belongs right left menus, boxes, etc
			$page_output['body'.$page_output_html_order[4]].Html::ln(). //here belongs right left menus, boxes, etc
			$page_output['body'.$page_output_html_order[5]].Html::ln(). //here belongs right left menus, boxes, etc
			$page_output['body'.$page_output_html_order[6]].Html::ln(). //here belongs right left menus, boxes, etc
			$page_output['footer'];

			echo '<font face="Arial"><small>';

			if (!isset($_GET['noreload']))
				echo 'Caching...';
			else
				echo 'Caching done!';

			echo "<br>";

			// cache: cache_page.php
			if (!Html::cache(Html::$page_output,PATHROOT.'engine/_cache/cache_page.php'))
				echo "<font color=red>cache_page.php</font><br>";
			else
				echo "<font color=green>cache_page.php</font><br>";

			if (!Html::cache_configfile())
				echo "<font color=red>config.php</font><br>";
			else
				echo "<font color=green>config.php</font><br>";


			// cache: stylesheet.css
			$string = '/* Autogenerated CSS Document, this file is part of WWC v2 by AXE */' . Html::ln();
			$db->query("SELECT title,template FROM ".$config['engine_web_db'].".wwc2_template WHERE styleid='".$config['engine_styleid']."' AND templatetype='css'");
			if($db->numRows() > 0)
			{
				while ($row = $db->getRow())
				{
					$string .= $row['title']." { ".$row['template']." }" . Html::ln();
				}

				if (!Html::cache($string,PATHROOT.'engine/_style_res/'.$config['engine_styleid'].'/stylesheet.css'))
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
			if (!Html::cache_menulinks())
				echo "<font color=red>cache_menulinks.php</font><br>";
			else
				echo "<font color=green>cache_menulinks.php</font><br>";

			// Cache vote links now
			// engine/_cache/cache_vote_loggedin.php and engine/_cache/cache_vote_loggedout.php
			if (!Html::cache_vote())
				echo "<font color=red>cache_vote_loggedin.php<br>cache_vote_loggedout.php</font><br>";
			else
				echo "<font color=green>cache_vote_loggedin.php<br>cache_vote_loggedout.php</font><br>";

			// End Caching
			// Here we will once more reload page using meta reload, sometimes it needs
			// few reloads until all files are cached properly, reason unknown.

			echo $lang['Caching done in']." ".round((microtime()-TIMESTART),3)."s!</small></font><br>";

			if (!isset($_GET['noreload']))
			{
				echo '<meta http-equiv="refresh" content="0;url=index.php?cache=true&noreload=1" />';
			}
			return;
	}

	static function lang_selection($selected) {
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

	static function cache_configfile() {
		global $db, $config;
		// cache: config.php
		$string = "<?php" .Html::ln(). '$config=array(' . Html::ln();

		$sql1 = $db->query("SELECT * FROM ".$config['engine_web_db'].".wwc2_config");
		while ($sql2 = $db->getRow())
		{
			$string .= "'".$sql2['conf_name']."' => '".$sql2['conf_value']."'," . Html::ln();
		}
		$string .= ");" .  Html::ln() .  Html::ln() . "define('AXE',1);" . Html::ln() . Html::ln();
		// Recache config
		return Html::cache($string, PATHROOT.'config/config.php');
	}

	static function cache($string, $file) {
		$fh = @fopen( $file, "w" );
		@fclose( $fh );
		if ($string=='') $string='<?php'. Html::ln() . '/* This file is auto-generated because cache script was initiated */'. Html::ln().'/* and content was empty. ( '.$file.' ) */' . Html::ln(). '/* This file is part of Web-WoW CMS v2 all rights reserved. */' . Html::ln().'?>';
		// Fix fhis later....
		//filehandler::checkpermission($file, 0666);
		return filehandler::write($file, $string);
		//return filehandler::checkpermission($file, 0664);
	}

	static function cache_menulinks() { #returns true on success else false
		global $db;
		/**
		* Build content
		*/
		$out = "<?php" .Html::ln(). '/* Autogenerated file by Link Manager (WWCMSv2) */'.Html::ln().'function menulinks($grup,$sep) { global $user,$config;' . Html::ln();

		$sql1 = $db->query("SELECT * FROM ".TBL_LINKS." ORDER BY linkgrup ASC, linkorder ASC, linktitle")or die($db->getLastError());
		$grup='no_grup_defined_start';
		while ($sql2=$db->getRow($sql1))
		{
			/**
			* If new grup is started:
			*/
			if ($grup<>$sql2['linkgrup']){
				if ($grup<>'no_grup_defined_start') {
					$out.='return $out;';
					$out.= Html::ln().''.Html::ln().'}'. Html::ln();
				}

				$out.='if ($grup=="'.$sql2['linkgrup'].'") { '. Html::ln() . '$i=0;$out=false;'. Html::ln();
				$grup=$sql2['linkgrup'];
			}

			/**
			* print links, add premission code here
			*/
			if ($sql2['linkprems']=='1') //guests
			{
				$stringstart=' if(!$user->logged_in){'.Html::ln();
				$stringend=' } ';
			}
			elseif($sql2['linkprems']=='2') //logged in - all
			{
				$stringstart=' if($user->logged_in){'.Html::ln();
				$stringend=' } ';
			}
			elseif($sql2['linkprems']=='3') //logged in - normal users
			{
				$stringstart=' if(strtolower($user->userlevel)==\'0\'){'.Html::ln();
				$stringend=' } ';
			}
			elseif($sql2['linkprems']=='4') //logged in - admin
			{
				$stringstart=' if($user->isAdmin()){'.Html::ln();
				$stringend=' } ';
			}
			elseif($sql2['linkprems']=='5') //logged in - gms and admins
			{
				$stringstart=' if($user->isAdmin() or $user->isGM()){'.Html::ln();
				$stringend=' } ';
			}
			else
			{
				$stringstart = '';
				$stringend = '';
			}

			$out.=$stringstart.Html::ln().' if ($i==1) $out.= $sep;$i=1; '.Html::ln().'$out.= \'<span class="menulink'.$sql2['linkgrup'].'">'.Html::ln().'	<a href="'.$sql2['linkurl'].'"';
			if ($sql2['linkdescr']<>'')
				$out.=' onmouseout="$WowheadPower.hideTooltip();" onmousemove="$WowheadPower.moveTooltip(event)" onmouseover="$WowheadPower.showTooltip(event, \\\''.$sql2['linkdescr'].'\\\')"';
			$out.='>'.$sql2['linktitle'].'</a>'.Html::ln().'</span>\';';
			$out.=$stringend.Html::ln();
			$stringstart='';
			$stringend='';
		}

		$out.='return $out;';
		$out.= ''.Html::ln().'}'.Html::ln().'}';
		/**
		* Initiate cache now:
		*/
		return Html::cache($out,PATHROOT.'engine/_cache/cache_menulinks.php');
	}

	static function cache_vote(){ #returns true on success else false
		global $db;
		/**
		* Build content
		*/
		$out1 = "<?php" .Html::ln(). '/* Autogenerated file by Global Caching (WWCMSv2) */'.Html::ln().'?>';
		$out2 = $out1;
		$sql1 = $db->query("SELECT * FROM ".TBL_CONFIG." WHERE conf_name LIKE 'vote_link_%' ORDER BY conf_name ASC")or die($db->getLastError());
		while ($sql2=$db->getRow($sql1))
		{
			$id = preg_replace("/vote_link_/", "", $sql2['conf_name']);
			$out1 .= '<span class="votelink" id="'.$sql2['conf_name'].'">';
			$out1 .= '<?php global $user;
			$voted = $user->hasVoted('.$id.');
			if(!$voted) {
			?>
			<a href="'.$sql2['conf_value'].'" target="_blank" onclick="ajax_loadContent(\''.$sql2['conf_name'].'\',\'./engine/dynamic/vote_proccess.php?id='.$sql2['conf_name'].'\',\'<img src=./engine/_style_res/<?php echo $config[\'engine_styleid\'];?>/images/voteimg/'.$id.'.gif alt=[<?php echo $lang[\'Vote\'];?>]>\');">
			<?php } ?>
				<img <?php if($voted) { ?> onload="$(this).fadeTo(\'fast\', \'0.1\');" <?php } ?> src="./engine/_style_res/<?php echo $config[\'engine_styleid\'];?>/images/voteimg/'.$id.'.gif" alt="[<?php echo $lang[\'Vote\'];?>]">
			<?php if (!$voted) { ?></a><?php } ?></span>';
			//for guests:
			$out2 .= '<span class="votelink"><a href="'.$sql2['conf_value'].'" target="_blank"><img src="./engine/_style_res/<?php echo $config[\'engine_styleid\'];?>/images/voteimg/'.$id.'.gif" alt="[<?php echo $lang[\'Vote\'];?>]"></a></span>';
		}

		/**
		* Initiate cache now:
		*/
		if (Html::cache($out1,PATHROOT.'engine/_cache/cache_vote_loggedin.php'))  $fin=true;  else $fin=false;
		if (Html::cache($out2,PATHROOT.'engine/_cache/cache_vote_loggedout.php')) $fout=true; else $fout=false;
		if (($fin) && ($fout))
			return true;
		else
			return false;
	}

	/**
	* ln - Returns the Line Ending Characters based on Operating System
	*
	* @access public
	* @return string Line Ending Characters
	*
	*/
	static function ln() {
		$server = strtolower(
			function_exists("php_uname") ? php_uname("s") :
			(isset($_SERVER['OS']) ? $_SERVER['OS'] : "")
			);

		// Windows
		if (strstr($server, 'windows')) return "\r\n";

		// Mac
		if(strstr($server, 'mac')) return "\r";

		return "\n";
	}

	static function includeplugins() {
		$out=array();
		// Get files in format [location0-0-0-0.php]
		$plugins = filehandler::getDir('engine/plugins', false, '/[A-Z][0-9]-[0-9]-[0-9]-[0-9]\.php$/i');
		foreach( $plugins as $plugin )
		{
			// Remove file extension (.PHP or .php)
			$plugin = preg_replace("/.php/i", "", $plugin);
			// Find plugin location by file name
			$plugin = explode("-", $plugin);

			if (!isset($out[$plugin[0]])) $out[$plugin[0]] = array();
			array_push($out[$plugin[0]], $plugin[1].'-'.$plugin[2].'-'.$plugin[3]);
		}
		return $out;
	}

	static function includemodule() {
		global $user;
		if(isset($_GET['page']) && !empty($_GET['page']))
		{
			$module = preg_replace('/[^A-Za-z0-9_-]/', '', $_GET['page']); //only letters and numbers
			if ($module && filehandler::isExists($module.'.php', 'engine/modules'))
			{
				include(PATHROOT . 'engine/modules/' . $module . '.php');
			}
			else
			{
				echo 'CODE 404';
			}
		}
		else
		{
			include(PATHROOT . 'engine/news.php');
		}
	}

	static function includemodule_proccess() {
		$proccess = false;
		if(isset($_GET['page']) && !empty($_GET['page']))
		{
			$module = preg_replace('/[^A-Za-z0-9_-]/', '', $_GET['page']); //only letters and numbers
			if ($module && filehandler::isExists($module.'.php', 'engine/modules'))
			{
				$proccess = true;
				include(PATHROOT . 'engine/modules/' . $module . '.php');
			}
		}
	}

	static function credits($style = false) {
		global $db, $lang, $config, $user;
		if ($style)
		{
			$style = $style.' | ';
		}

		if ($config['footer_detail'] == '1' or ($config['footer_detail'] == '2' && $user->isAdmin()))
		{
			return $lang['Page generated'].': '.round((microtime()-TIMESTART),2).' | '.$lang['Queries executed'].': '.database::$num_queries.' | '.$lang['Copyright'].' &copy; 2010-2011 | '.$style.$lang['Powered by'].': <a href="index.php?page=credits" title="">WWCv2</a>  | <a href="tos.php">'.$lang['Terms of Use'].'</a>';
		}
		else
		{
			return $lang['Copyright'].' &copy; 2010-2011 | '.$style.$lang['Powered by'].': <a target="_blank" href="http://www.web-wow.net/" title="">WWCv2</a>  | <a href="tos.php">'.$lang['Terms of Use'].'</a>';
		}
	}

	static function portcheck($sep, $port = false, $server = false) {
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
					if (!isset($realm_data) || count($realm_data) == 1) continue;

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

	static function credits_cms() {
		global $lang_admincp;

		echo "<h2>".$lang_admincp['Credits']."</h2><i>".$lang_admincp['Main'].":</i><blockquote>Website is made by <strong>Axe</strong> from <strong>".WEBWOW."</strong></blockquote><i>";
		echo $lang_admincp['Used 3rd party scripts'].":</i><blockquote>Code highlight script: <strong>CodeMirror</strong> written by Franciszek Wawrzak<br>";
		echo "Tooltips: <strong>WowHead</strong>'s javascript wow item's tooltip script (www.wowhead.com)<br>";
		echo "Javascript Engine: <strong>jQuery</strong> (jquery.com)</blockquote><i>Contributers:</i><blockquote>Maverfax - debugging during the beta phase</blockquote>";
	}

	static function formatmoney($query_a) {
		$money = false;
		$gold = substr($query_a, 0, -4);

		if ($gold >= 1)
		{
			$money = '' . number_format($gold) . ' <img src="./engine/res/money_gold.gif" /> ';
		}

		$silver  = substr($query_a, 0, -2);
		$silver2 = substr($silver, -2);

		if ($silver2 >= 1)
		{
			$money .= '' . number_format($silver2).' <img src="./engine/res/money_silver.gif" /> ';
		}

		$copper = substr($query_a, -2);

		if ($copper != '0' or $copper != '00')
		{
			$money .= ''. number_format($copper).'</span> <img src="./engine/res/money_copper.gif" /> ';
		}

		return $money;
	}

	/**
	* Determines wether a language is valid or not
	*
	* @param string
	* @return boolean
	*/
	static function is_valid_lang($language = '', $location = '') {
		if(!empty($language))
		{
			if(file_exists(PATHROOT.'engine/lang/' . $language . '/' . $location . '.php'))
			{
				return TRUE;
			}
		}
		return FALSE;
	}

	/**
	* Removes all characters that are not alphabetical nor numerical
	*
	* @param string
	* @return string
	*/
	static function sanitize($string = '') {
		return preg_replace('/[^a-zA-Z0-9]/', '', $string);
	}
}

//PHP 4.2.x Compatibility function
if(!function_exists('file_get_contents')) {
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