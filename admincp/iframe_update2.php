<?php
/************************************************************************
*													admincp/iframe_update2.php
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
include "defines.php";
//@set_time_limit(0);
/* Initilaze stuff */
include PATHROOT."engine/init.php";

/* If no premission redirect to main page */
if(!$user->logged_in){
	header('Location: ../index.php');
	exit;
}
if (!$user->isAdmin()){
	header('Location: ../index.php');
	exit;
}

/* Include admin functions */
include PATHROOT.'engine/func/admin_update.php';
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>Iframe WWCMS v2 Updater (eng)</title>
<style type="text/css">
<!--
body {font-family: Arial, Helvetica, sans-serif; font-size:12px; background-color:#f1c87b}
a { color:#000000; text-decoration:none}
a:visited { color:#000000; text-decoration:none}
a:hover { color:#000000; text-decoration:underline}
-->
</style></head><body>
<?php
$num_totalfiles=count($_SESSION['update_files']);
if ($updateclass->Update_file('{admincp}/iframe_update.php')=='1')
	echo "File is updated! (".$config['engine_acp_folder'].'iframe_update.php)';
else
	echo "Writting failed there might be problem with update server try again later, maybe this file can't be created on this server, navigate to corresponding folder and create empty file with name stated below.<br>File: ".$config['engine_acp_folder']."iframe_update.php";
?>
</body>
</html>
