<?php
/************************************************************************
*													engine/func/admin_update.php
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


class Update {

	/**
	* GetPatchInfo(){ - initiates in admin cp
	* Prints whole Update text and iframe.
	*/
	function GetPatchInfo(){
		global $lang_admincp,$config;
		unset($_SESSION['update_files']);
		/**
		* GET the latest patch information, also include the old patch, this file contains
		* access limitator (for license), and all patch version file lists, it returns that file
		* list and organise it, after that we do extracting of files one by one here in this file using
		* fsockopen() method.
		*/
		$servername = preg_replace( "/[^a-zA-Z0-9]/", "", $config['title'] );
		$file = $this->getUpdatedFile('projects/webwow_creator_v2/upgrade/update_core.php?license='.LICENSE.'&enginever='.VERSION.'&domain='.$_SERVER["SERVER_ADDR"].'&servername='.$servername.'&nocache='.rand(1,1000000000));
		$file[0]=explode(Html::ln(),$file[0]);//FIX
		$_SESSION['update_files']=$file[0];
		echo '<h2>'.$lang_admincp['Update CMS'].'</h2> ';

		if (!isset($_GET['start_cms'])){
			if (file_exists(PATHROOT.$config['engine_acp_folder'].'/iframe_update2.php'))
			{
				echo '<div style="border:solid 1px orange;background-color:#f1c87b; padding:4px"><i>Updating updater itself (please wait a moment):</i><br><iframe src ="./iframe_update2.php" width="100%" height="50" frameborder="0" style="background: #f1c87b;"><p>Your browser does not support iframes.</p></iframe></div><br>';
			}
		}

		if (VERSION>=$file[1])
			{echo '<span style="font-weight:normal;color:green">CMS is up to date, you are using v'.$file[1].'.</span><br>';return;}
		else
			echo '<big><font color=green>'.$lang_admincp['Update is available'].' (<small>'.VERSION.'</small> to '.$file[1].')</font></big><br>';

		echo $lang_admincp['File list in this update'].':<br>';
		//make session so we can pass the filenames to iframe
		foreach ($file[0] as $key => $value)
		{
			if ($value<>'')
				echo "&nbsp;&nbsp;www/<span style='color:darkgray' id='filelist".$key."'>".$value.'</span><br>';
		}

		if (isset($_GET['start_cms'])){
			echo '<iframe src ="./iframe_update.php?i=0&v='.$file[1].'" width="100%" height="150" frameborder="0" style="background: white"><p>Your browser does not support iframes.</p></iframe>';
		}
		else
		{
			echo '<br><span class="buttonlink"><a href="?f=main&updatecms=true&start_cms=true">'.$lang_admincp['Start Update Now'].'</a></span>';
		}
	}

	/**
	* GetModuleInfo() - initiates in admin cp
	* Prints whole Update text and iframe, prints form, with module selection, then sets $_SESSION[''] and transfers it to iframe.
	*/
	function GetModuleInfo(){
		global $lang_admincp,$lang_admincphelp;

		unset($_SESSION['update_files']);

		$file = $this->getUpdatedFile('projects/webwow_creator_v2/upgrade/update_modules.php?license='.LICENSE.'&enginever='.VERSION.'&domain='.$_SERVER["SERVER_ADDR"]);
		$file[0]=explode(Html::ln(),$file[0]);
		$_SESSION['update_files']=array();
		echo '<h2>'.$lang_admincp['Update/Install Modules'].'</h2> ';

		echo '<font color=green><big>'.$lang_admincp['Module selection'].':</big></font><br><form method="post" action="?f=main&updatecms=true&start_modules=true">';

		$keyforupdate=0;
		//make session so we can pass the filenames to iframe
		foreach ($file[0] as $key => $value)
		{

			if ($value<>'')
			{
				$value2=explode("|",$value);
				/* file.php?test=true strip ?test=true, just for show */
				$pure_filename=explode("?",$value2[0]);


				if ($value2[1]!=$lastmodule_name)//new module, new row
				{
					if (isset($_POST['submit_module']) && $_POST[$value2[1]]=='1')
					{
						$_SESSION['update_files'][$keyforupdate]=$pure_filename[0];
						//echo $_SESSION['update_files'][$keyforupdate].' ('.$keyforupdate.')';

						$filelist_id=$keyforupdate;
						$keyforupdate++;

					}


					echo '<input type="checkbox" name="'.$value2[1].'" value="1"';
					if (file_exists(PATHROOT.$pure_filename[0]) or (isset($_POST['submit_module']) && $_POST[$value2[1]]=='1'))
						echo ' checked="checked"';
					echo ' /> ';
				}
				else//additional files for same module
				{
					echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
					echo '<input name="'.$value2[1].$key.'" type="hidden" value="1" />';
					if (isset($_POST['submit_module']) && $_POST[$value2[1]]=='1')
					{$_SESSION['update_files'][$keyforupdate]=$pure_filename[0].'?'.$pure_filename[1];
						$filelist_id=$keyforupdate;
						$keyforupdate++;
					}

				}

				$propername= preg_replace( "/_/", " ", $value2[1] );
				echo ucwords($propername)."&nbsp;&nbsp;www/<span style='color:darkgray' id='filelist".$filelist_id."'>".$pure_filename[0].'</span><br>';
				$filelist_id=false;
				$lastmodule_name = $value2[1];
			}
		}
		echo "<br>".$lang_admincphelp[35].".<br>";
		if (isset($_GET['start_modules']))
			echo '<iframe src ="./iframe_update.php?i=0&v='.VERSION.'" width="100%" height="150" frameborder="0" style="background: white"><p>Your browser does not support iframes.</p></iframe>';
		else
			echo '<br><input name="submit_module" type="submit" class="buttonlink_submit" value="'.$lang_admincp['Start Update Now'].'" /></form>';
	}

	/**
	* GetStylesInfo(){ - initiates in admin cp
	* Prints all available styles links - drawn from webwow.
	*/
	function GetStylesInfo(){

		global $lang_admincp,$lang_admincphelp;
		unset($_SESSION['update_files']);

		$file = $this->getUpdatedFile('projects/webwow_creator_v2/upgrade/update_styles.php?license='.LICENSE.'&enginever='.VERSION.'&domain='.$_SERVER["SERVER_ADDR"]);
		$file[0]=explode(Html::ln(),$file[0]);

		//make loop for filenames
		foreach ($file[0] as $key => $value){ if ($value<>'')
			{

				echo $value;

		}}
	}

	/**
	* GetStyle($id){ - initiates in admin cp
	* Prints whole Update text and iframe, prints form, with module selection, then sets $_SESSION[''] and transfers it to iframe.
	* $id -> webwow style id (ww_templateid) to indentify which style to download.
	*/
	function GetStyle($id,$confirm){ //$id is already filtered from other function

		global $lang_admincp,$lang_admincphelp,$db,$config;
		unset($_SESSION['update_files']);

		if ($confirm=='' or !$confirm) $confirm=false; else $confirm=true;


		$file = $this->getUpdatedFile('projects/webwow_creator_v2/upgrade/update_styles.php?webwowid='.$id.'&license='.LICENSE.'&enginever='.VERSION.'&domain='.$_SERVER["SERVER_ADDR"]);

		$file[0]=explode(Html::ln(),$file[0]);
		$_SESSION['update_files']=array();

		//get max styleid and make unique id (+1);
		$getstyle_sql1=$db->query("SELECT max(styleid) as 'maxid' FROM ".$config['engine_web_db'].".wwc2_template LIMIT 1") or die('CMS: '.$db->getLastError());
		$getstyle_sql2=$db->getRow($getstyle_sql1);
		$style_gotten_id=$getstyle_sql2['maxid']+1;



		$query_string="INSERT INTO ".$config['engine_web_db'].".wwc2_template (styleid,title,template,template_un,templatetype,username,version) VALUES ";

		//confirmation screen:
		if (!$confirm)
		{
			echo 'You are about to install new style from WebWoW server.';
			if (is_dir($file=PATHROOT.'engine/_style_res/'.$style_gotten_id))
				echo '<br><font color=orange>Note:</font> Style folder already exists, by installing this style you will overwritte files contained inside "<strong> '.PATHROOT.'engine/_style_res/'.$style_gotten_id.'/ </strong>" folder.';
			echo '<br><br>Your <strong>StyleID</strong> for this style will be "'.$style_gotten_id.'".
<br>Script will apply this style to configuration. Please recache after installation is done (<i>you might need to recache few times until website take effect</i>).<br><br><span class="buttonlink">
<a href="?f=stylemanager&getmorestyles=true&webwowid='.$id.'&confirm=1">Yes, install this style now!</a></span>';
			return;
		}

		//import to db and add files
		$sql_section_counter=0; //6 lines per part
		$key_for_update=0;
		foreach ($file[0] as $key => $value)
		{
			//build query parts now:
			#start
			if ($sql_section_counter=='0') {
				if ($value=='' or strstr($value,'FILE:')) {
					//at this part we're probably comming to file list, so just make sessions now:
					if (strstr($value,'FILE:'))
					{
						//file root is already inside styles folder:
						$_SESSION['update_files'][$key_for_update]='engine/_style_res/'.$id.'/'.substr($value,5).'?sinstallpath='.$style_gotten_id;
						//echo 'engine/_style_res/'.$id.'/'.substr($value,5).'?sinstallpath='.$style_gotten_id;

						$key_for_update++;
					}


					continue;
				};

				$query_string.= $count.'(\''.$style_gotten_id.'\',';
				//echo '('.$count.')<br>';
			}
			#middle
			$query_string.= '\''.$value.'\'';
			//echo '<font color=red>'.$sql_section_counter.'(</font>'.htmlspecialchars($value).'<font color=red>)</font><br>';

			$sql_section_counter++;

			#end
			if ($sql_section_counter=='6')
			{
				$sql_section_counter='0';
				$query_string.= '),';

			}
			else{
				$query_string.= ',';

			}
		}
		//remove ',' leftover at end of the string.
		$query_string=substr($query_string, 0, -1);
		//echo '<br>'.htmlspecialchars($query_string).'<br>';

		$db->query($query_string) or die($db->getLastError().'<br>Style might not be allowed for you.');

		$db->query("UPDATE ".$config['engine_web_db'].".wwc2_config set conf_value='".$style_gotten_id."' where conf_name='engine_styleid' LIMIT 1") or die($db->getLastError());
		echo 'Style is imported to database. Do not forget to recache after file installation.<br><br>Installing style files:<br>';

		echo '<iframe src ="./iframe_update.php?i=0&v='.VERSION.'" width="100%" height="150" frameborder="0" style="background: white"><p>Your browser does not support iframes.</p></iframe>';

	}

	/**
	* Update_file($file) - $file is full file path from root.
	* example: $file = engine/init.php or {admincp}/test.php
	* Data is gotten from webwow - core_files/.
	* This function initiates in IFRAME, every reload different file (loop).
	* Returns 1 if everything is success, 0 if file is not cached.
	* php and other files must be correctly formatted (without "_"):
	[_latestversion_]1.0.1
	[_filestart_]
	content
	[_fileend_]
	*/
	function Update_file($file,$stylepath=false) {

		global $config,$lang_admincp;
		$code = 0;
		$file_remote=$file;
		$file=PATHROOT.$file;
		#parse {admincp} path variable (if its there)

		$acpfolder=$this->rstrtrim($config['engine_acp_folder'], '/');
		$file = preg_replace('|{admincp}|',$acpfolder, $file);
		#modify style path folder
		if ($stylepath<>'')
		{
			//we should have this format now:
			// ../engine/_style_res/2/download.jpg
			//change 2 to $stylepath
			echo $file.'<br>';
			$file_expl=explode("/",$file);
			//make new path:
			//additional folder levels:
			$levels='';
			$levels_i=6;

			while ($levels_i<=count($file_expl))
			{
				$levels.='/'.$file_expl[$levels_i-1];
				$levels_i++;
			}

			/*dir business*/
			if (!is_dir('../engine/_style_res/'.$stylepath))
				mkdir('../engine/_style_res/'.$stylepath, 0777);
			if (!is_dir('../engine/_style_res/'.$stylepath.'/images'))
				mkdir('../engine/_style_res/'.$stylepath.'/images', 0777);
			if (!is_dir('../engine/_style_res/'.$stylepath.'/images/voteimg'))
				mkdir('../engine/_style_res/'.$stylepath.'/images/voteimg', 0777);


			$file='../engine/_style_res/'.$stylepath.'/'.$file_expl[4].$levels;

		}
		#seperate dir from file

		$path_parts = pathinfo($file);
		$filename = $path_parts['basename'];
		$dir = $path_parts['dirname'];// with "/" at end i think

		#make dir if doesn't exists, check for read/writte premissions
		if (!is_dir($dir))
			mkdir($dir, 0777);
		if(!is_readable($dir)) {
			@chmod($dir, 0777);
			if (!is_readable($dir))
				exit('Path (' . $dir . ') can\'t be read, CHMOD it to 0777.');
		}
		$content = $this->getUpdatedFile('projects/webwow_creator_v2/upgrade/core_files/'.$file_remote);
		/*
		* stop proccess if there is returned false
		* this means sql is executed, no caching needed
		*/
		if (!$content) return;

		#no need for this anymore
		//$content[0] = preg_replace( "/\[\|\]/", Html::ln(), $content[0] ); //only letters and numbers

		#replace {_LICENSE_} inside content to make file unique and working -> its done on webwow side
		//$content[0]= preg_replace('|{_LICENSE_}|',$config['license'], $content[0]);

		#make working dir:
		//chdir($dir);

		/* writte $content[0] */
		if (Html::cache($content[0],trim($file)))
			$code = 1;
		else
			$code = 0;
		/* save to logs */
		//ADD SCRIPT

		return $code;

	}

	/**
	* getUpdatedFile($fileurl) - Returns array of remote file
	* on web-wow.net/$fileurl. Arrays:
	* 0 -> file content {admincp} is variable that needs to parsed
	* 1 -> file version
	*/
	function getUpdatedFile($fileurl) {
		global $db,$config;

		$content=array();
		$content[0]='';
		$path_parts=pathinfo($fileurl);
		/*
		* Here is where we split paths, depending of file formatting
		* we need to use RAW one for images and css,js (jpg,jpeg,png,gif,css,js)
		* and formatted one for all other files.
		*/
		if ($path_parts['extension']=='jpg' || $path_parts['extension']=='jpeg' || $path_parts['extension']=='gif' || $path_parts['extension']=='png' || $path_parts['extension']=='css' || $path_parts['extension']=='js' || $path_parts['extension']=='html')
		{
			/* open image (this seems to be hackable and if you want to exploit web-wow.net.. you can't, it does not work that way) */
			$img = file_get_contents('http://'.WEBWOW.'/'.$fileurl);
			$content[0]=$img;
			$content[1]='';
			unset($img);
			return $content;
		}
		//ADD script for SQL files, this is not used or needed in webwow cms
		else
		{

			/* CONNECTION START */
			$fp = @fsockopen(WEBWOW, 80, $errno, $errstr, 30);
			if (!$fp) {
				echo "$errstr ($errno)<br />\n";
			} else {
				$lic = preg_replace( "/[^A-Za-z0-9]/", "", $config['license'] );
				if (strstr($fileurl,'?')==true)
					$out = "GET /".$fileurl."&license=".$lic." HTTP/1.0\r\n";
				else
					$out = "GET /".$fileurl."?license=".$lic." HTTP/1.0\r\n";
				$out .= "Host: ".WEBWOW."\r\n";
				$out .= "Connection: Close\r\n\r\n";

				fwrite($fp, $out);
				$start=false;$stop=false;
				while (!feof($fp)) {
					$temp=trim(fgets($fp));
					/**
					* There is some additional variables before [_filestart_]
					* Those variables are:
					* [latestversion]x.x.x (we get this as $content[1])
					*
					* Inside [filestart_] [fileend_] there is list of required files (or file content)
					* that needs to be updates in relative paths souch as:
					* ./index.php ; ./engine/init.php etc. (we get them as $content[0]
					* a string seperated by linebreak )
					**/

					if (preg_match("/\[latestversion\]/i", $temp))
						$content[1] = preg_replace( "/\[latestversion\]/i", "", $temp );

					if (trim($temp)=='[fileend]') //stop parsing the file from here
						$stop=true;
					if ($start && !$stop){
						$content[0] .= $temp.Html::ln();//FIXED from
					}
					if (trim($temp)=='[filestart]')//start parsing the file from here
						$start=true;

					/**
					* NOTE: about licenses, we will add license number in web-wow.net side
					* there will be no need for method: replace {_LICENSE_} variable as in old
					* wow cms.
					**/


				}
				fclose($fp);
				return $content;
			}
			/* CONNECTION END */
		}

	}

	function rstrtrim($str, $remove=null) {
		$str    = (string)$str;
		$remove = (string)$remove;

		if(empty($remove))
		{
			return rtrim($str);
		}

		$len = strlen($remove);
		$offset = strlen($str)-$len;
		while($offset > 0 && $offset == strpos($str, $remove, $offset))
		{
			$str = substr($str, 0, $offset);
			$offset = strlen($str)-$len;
		}

		return rtrim($str);

	}
}
$updateclass= new Update;




