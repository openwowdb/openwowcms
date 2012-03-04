<?php
/************************************************************************
*													engine/modules/userpanel.php
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

global $user,$db,$lang,$config;
/**
* Access premission:
**/
if(!$user->logged_in){ if (!isset($proccess)) echo "<a href='index.php?page=loginout'>".$lang['Login']."</a>"; return; }
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
	function Process(){
		 global $user;



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
	Form::_Form();
	return;

}


?>
<!-- This element is important, must be at beginning of module output, dont change it, except module name -->
<div class="post_body_title"><?php echo $lang['Userpanel']; ?></div>
<span class="module_profile">
<?php
$exclude_modulenames=explode('|',$config['module_userpanel']);
//loop trough modules
$folder = "engine/modules/";
$handle = opendir($folder);
# Making an array containing the files in the current directory:
while ($file = readdir($handle))
{
	$files[] = $file;
}
closedir($handle);
$cont2=false;
#echo the files
foreach ($files as $file)
{


	if (strstr($file, ".php"))
	{
		if (!in_array($file,$exclude_modulenames))
		{
		$file2=substr($file, 0,-4); //without .php
		$file3=str_replace('_',' ',$file2); //replace "_" with " "
		$file4=explode("-",$file3);//extract its module name
		if (!isset($file4[1])) $nameshow=$file4[0]; else $nameshow=$file4[1];
		$cont2.= '<span class="each_modulelist"><span class="modulelist_'.$file2.'"><a style="font-size:14px" href="index.php?page='.$file2.'">'.ucwords($nameshow).'</a></span></span><br>';
		}
	}
}
echo $cont2;
//********************************************
?></span>