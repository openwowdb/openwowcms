<?php
/************************************************************************
*												 admincp/iframe_fetchplugin.php
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
# INCLUDES:
# - initialization script
############################################################

include "defines.php";
//@set_time_limit(0);
error_reporting(~E_NOTICE);
/* Initilaze stuff */
include PATHROOT."engine/init.php";

/* If no premission redirect to main page */
if(!$user->logged_in)
{
	header('Location: ../index.php');
	exit;
}

if (!$user->isAdmin())
{
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
<title>Plugin list fetcher</title>
<script type="text/javascript" src="<?php echo PATHROOT; ?>engine/js/power.js"></script>
<link rel="stylesheet" type="text/css" href="<?php echo PATHROOT; ?>engine/js/power/power.css" />

<style type="text/css">
<!--
body{background:#D9ECFF url(res/icon_plugin.png) center no-repeat;}
body,td,th { color:#333333; font-family:Geneva, Arial, Helvetica, sans-serif;
}
a { color:#000000; font-family:Arial, Helvetica, sans-serif; font-size:12px; text-decoration:none; text-shadow:1px 1px 1px #666666 }
a:visited { color:#333333; text-shadow:none }
a:hover{ color:#FF9900; }
a:active{ color:#FF9900 }
.title{ border-bottom:solid 1px #006699; text-align:right; font-weight:bold; }
/* -webkit-border-radius: 2px;
-moz-border-radius: 2px;*/
.floatright{ float:right}
-->
</style>
</head>
<body>

<?php
if (isset($_GET['id'])) {
	$id = preg_replace( "/[^A-Za-z0-9-_.]/", "", $_GET['id'] ); //only letters and numbers
	$id = '&id='.$id;
}
else {echo '<div class="title">Plugin list</div>';$id='';}
if (isset($_GET['files'])) {
	$files = preg_replace( "/[^A-Za-z0-9-\/_.\[\]]/", "", $_GET['files'] ); //only letters and numbers
	$files = preg_replace( "/\./", "[dot]", $files  ); //only letters and numbers
	$files = '&files='.$files;
}
else
	$files='';
?>
<table border="0" width="100%">
<?php
$pluginlist0 = $updateclass->getUpdatedFile('projects/webwow_creator_v2/outputer_plugins.php?domain='.$_SERVER["SERVER_ADDR"].$id.$files);
$pluginlist = explode("[|]",$pluginlist0[0]);
foreach ($pluginlist as $pluginlist)
{
	echo $pluginlist;//print fetched line(s)
}
?>
</table>
</body>
</html>
