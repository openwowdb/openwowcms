<?php

###################################################################
#   Project Owner    : OpenWoW CMS (http://www.openwow.com)
#   Copyright        : (c) www.openwow.com, 2010
#   Credits          : Based on work done by AXE and Maverfax
#   License          : GPLv3
##################################################################

# INCLUDES:
# - initialization script
require("defines.php");
@set_time_limit(0);
/* Initilaze stuff */
require(PATHROOT."engine/init.php");

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
require_once(PATHROOT.'engine/func/admin_update.php');
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
