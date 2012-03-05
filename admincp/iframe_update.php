<?php
/************************************************************************
*													admincp/iframe_update.php
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
include "defines.php";
//@set_time_limit(0);
/* Initilaze stuff */
include PATHROOT."engine/init.php";

/* If no premission redirect to main page */
if(!$user->logged_in || !$user->isAdmin()){
	header('Location: ../index.php');
	exit;
}

/* Include admin functions */
include PATHROOT.'engine/func/admin_update.php';

function redirect( $url ) {
	echo "<script type=\"text/javascript\">
	setTimeout('location = \'" . $url . "\'', (100));
	</script>
	<noscript><meta http-equiv=\"refresh\" content=\"0;url=" . $url . "\" /></noscript>";
}

$i = isset($_GET['i']) ? preg_replace("/[^0-9]/", "", $_GET['i']) : 0;
$v = isset($_GET['v']) ? preg_replace("/[^0-9a-z.]/", "", $_GET['v']) : "";
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
	<title>OpenWoWCMS v2 Updater</title>
	<style type="text/css">
		<!--
		body {font-family: Arial, Helvetica, sans-serif; font-size:12px; background-color:#FFFFFF}
		a { color:#000000; text-decoration:none}
		a:visited { color:#000000; text-decoration:none}
		a:hover { color:#000000; text-decoration:underline}
		-->
	</style>
	<?php
		$num_totalfiles = isset($_SESSION['update_shas']) ? sizeof($_SESSION['update_shas']) : 0;

		/* Make loop here */
		if ($num_totalfiles > 0 && isset($_SESSION['update_shas'][$i]) && $_SESSION['update_shas'][$i] <> '') {
			/* file.php?test=true strip ?test=true
			* also will strip style changer variable "?sinstallpath=8" (will not mix with others, so this is final format)
			*/

			$sha = $_SESSION['update_shas'][$i];
			$github = new github();
			$commit_data = $github->get_commit($sha);
			$files = $commit_data->files;

			$tobody = "";
			$tbody2 = $sha;

			foreach ($files as $file)
			{
				if ($file->status == "modified" or $file->status == "created" or $file->status == "added")
				{
					// DO MOD/CREATE
					if ($github->get_file($sha, $file->filename))
					{
						$tobody .= "Applied update for file " . $file->filename . "<br>";
						continue;
					}

					$tbody2 .= "File: " . $file->filename;
					$tobody .= "Writing failed there might be problem with update server try again later, maybe this file can't be created on this server, navigate to corresponding folder and create empty file with name stated below. File: ".$file->filename."<br>";
				}
				else if ($file->status == "removed" or $file->status == "deleted")
					filehandler::delete($file->filename);
			}
			if ($tbody2 != $sha)
			{
				// Failed to update a file?
			}
			else
			{
			/*if ($updateclass->Update_file($pure_filename[0],$pure_filename[12]) == '1') {
				/* file is updated, print output: */
				$tbody2 = $sha;
				//redirect('./iframe_update.php?v='.$v.'&i='.($i+1));
				//echo 'iframe_update.php?i='.($i+1);
				$tobody .= '<span style="float:right"><a href="./iframe_update.php?v='.$v.'&i='.($i+1).'">SHA ( '.$sha.' ) updated! Force next update...</a> </span>
				<script type="text/javascript">
					$("#filelist'.$sha.'").slideUp("slow");
					setTimeout("do_update('.($i+1).')", 5000);
				</script>';
			}
		}
		else {
			if ($v <> '' && $v <> '0') {
				$version_str = '<?php'.Html::ln().
				'/************************************************************************'.Html::ln().
				'*														  engine/version.php'.Html::ln().
				'*                            -------------------'.Html::ln().
				'* 	 Copyright (C) 2011'.Html::ln().'*'.Html::ln().
				'* 	 This package is free software: you can redistribute it and/or modify'.Html::ln().
				'*    it under the terms of the GNU General Public License as published by'.Html::ln().
				'*    the Free Software Foundation, either version 3 of the License, or'.Html::ln().
				'*    (at your option) any later version.'.Html::ln().'*'.Html::ln().
				'*  	 This package is based on the work of the web-wow.net and openwow.com'.Html::ln().
				'* 	 team during 2007-2010.'.Html::ln().'*'.Html::ln().
				'* 	 Updated: $Date 2012/02/08 14:00 $'.Html::ln().'*'.Html::ln().
				'************************************************************************/'.Html::ln().
				'define(\'VERSION\',\''.VERSION.'\');'.Html::ln().
				'define(\'LASTUPDATE\',\''.date("m/j/Y").'\');'.Html::ln().
				'define(\'SHA_VERSION\', \''.$_SESSION['update_shas'][count($_SESSION['update_shas'])-1].'\');'.Html::ln().
				'?>';
				if (filehandler::write('version.php', $version_str, 'engine'))
				{
					$tbody2='&nbsp;';
					$tobody= "<br>All files are updated.";
				}
				else
				{
					$tbody2='&nbsp;';
					$tobody= "<br>All files are updated, but <strong>engine/version.php</strong> is not, edit that file and type in:<br><pre>".htmlspecialchars($version_str)."</pre>";
				}
			}
			else {
				$tbody2 = '&nbsp;';
				$tobody = "<br>All files are downloaded.";
			}

			unset($_SESSION['update_shas']);
			$percent = 100;
		}
		if (!isset($percent) || !$percent) {
			$y = $i;
			$y++;
			if ($num_totalfiles == '0') $percent = 1;
			else $percent = ((($y)*100)/($num_totalfiles+1)); // +1 for version.php
		}

	?>

</head>
<body>
	<?php echo $tbody2; ?><div style="position:absolute; width:90%; padding-top:2px; overflow:hidden; text-align:center; z-index:5; font-size:12px; line-height:12px"><?php echo ceil($percent); ?>%</div>
	<div style="border:solid 1px black"><div style=" background-color:#006633; background-image:url(res/buttonbg.gif); background-position:bottom; height:14px; width:<?php echo $percent; ?>%"></div></div>
	<?php echo $tobody; ?>

</body>
</html>

