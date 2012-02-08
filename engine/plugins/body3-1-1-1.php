<?php
// variables passed to plugins -> $isplugin
if (!class_exists("shoutbox"))
{
	include "./engine/modules/shoutbox.php";
}
else
{
	$shoutbox = new shoutbox();
	$shoutbox->plugin();
}
?>
