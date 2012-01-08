<?php
############################################################
# This file is part of Web-WoW CMS V2 @ web-wow.net
# Do not redistribute it without premission from AXE.
# Copyright (c) AXE, zg_20102 at hotmail dot com
#
# INCLUDES:
# - initialization script
############################################################
require("defines.php");
@set_time_limit(0);

/* Initilaze stuff */
require(PATHROOT."engine/init.php");

/* If no premission redirect to main page */
if(!$user->logged_in)
	header('Location: ../index.php');
if (strtolower($user->userlevel)<>strtolower($config['premission_admin']))
	header('Location: ../index.php');
	
/* Include admin functions */
require_once(PATHROOT.'engine/func/admin_update.php');

function redirect( $url ) {

    echo "<script type=\"text/javascript\">
            setTimeout('location = \'" . $url . "\'', (100));
        </script>
        <noscript><meta http-equiv=\"refresh\" content=\"0;url=" . $url . "\" /></noscript>";
}

if (!isset($_GET['i'])) $i=0;
else $i= preg_replace( "/[^0-9]/", "", $_GET['i'] ); //only letters and numbers
$v= preg_replace( "/[^0-9.]/", "", $_GET['v'] ); //only letters and numbers
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>Iframe WWCMS v2 Updater (eng)</title>
<style type="text/css">
<!--
body {font-family: Arial, Helvetica, sans-serif; font-size:12px; background-color:#FFFFFF}
a { color:#000000; text-decoration:none}
a:visited { color:#000000; text-decoration:none}
a:hover { color:#000000; text-decoration:underline}
-->
</style>
<?php
$num_totalfiles=count($_SESSION['update_files']);


/* Make loop here */
if ($_SESSION['update_files'][$i]<>''){
	/* file.php?test=true strip ?test=true */
	$pure_filename=explode("?",$_SESSION['update_files'][$i]);
	//$license= preg_replace( "/[^A-Za-z0-9]/", "", $_POST['username'] );
	if ($updateclass->Update_file($pure_filename[0])=='1'){
	/* file is updated, print output: */
		$tbody2=$_SESSION['update_files'][$i];
		redirect('./iframe_update.php?v='.$v.'&i='.($i+1)); 
		//echo 'iframe_update.php?i='.($i+1);
		$tobody = '<span style="float:right"><a href="./iframe_update.php?v='.$v.'&i='.($i+1).'">File ( '.$_SESSION['update_files'][$i].' ) updated! Force next file...</a> </span>
		<script type="text/javascript">parent.document.getElementById("filelist'.$i.'").style.color="green"</script>';
		}
	else
		{
			$tbody2=$_SESSION['update_files'][$i];
			$tobody = "Writting failed there might be problem with update server try again later.<br>File: ".$_SESSION['update_files'][$i];
		}
}
else{
	if ($v<>'' && $v<>'0'){
		$version_str='<?php'.$Html->ln().'define(\'VERSION\',\''.$v.'\');'.$Html->ln().'define(\'LASTUPDATE\',\''.date("m/j/Y").'\');'.$Html->ln().'?>';
		if ($Html->cache($version_str,PATHROOT.'engine/version.php'))
		{
			$tbody2='&nbsp;';
			$tobody= "<br>All files are updated.".'<script type="text/javascript">parent.document.getElementById("filelist'.($num_totalfiles-2).'").style.color="green"</script>';
		}
		else
		{
			$tbody2='&nbsp;';
			$tobody= "<br>All files are updated, but <strong>engine/version.php</strong> is not, edit that file and type in:<br><pre>".htmlspecialchars($version_str)."</pre>".'<script type="text/javascript">parent.document.getElementById("filelist'.($num_totalfiles-2).'").style.color="green"</script>';
		}
	}
	else{
		$tbody2='&nbsp;';
		$tobody= "<br>All files are downloaded.".'<script type="text/javascript">parent.document.getElementById("filelist'.($num_totalfiles-2).'").style.color="green"</script>';}
	
	unset($_SESSION['update_files']);
	$percent=100;
}
if (!$percent)
{
	if ($i=='0' or $num_totalfiles=='0') 
		$percent=1;
	else
		$percent=((($i)*100)/$num_totalfiles);
}

?>

</head>
<body>
<?php echo $tbody2; ?><div style="position:absolute; width:90%; padding-top:2px; overflow:hidden; text-align:center; z-index:5; font-size:12px; line-height:12px"><?php echo ceil($percent); ?>%</div>
<div style="border:solid 1px black"><div style=" background-color:#006633; background-image:url(res/buttonbg.gif); background-position:bottom; height:14px; width:<?php echo $percent; ?>%"></div></div>
<?php echo $tobody;
 ?>

</body>
</html>