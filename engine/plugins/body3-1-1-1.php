<?php
// variables passed to plugins -> $isplugin
if (!class_exists("shoutbox"))
{
	require "./engine/modules/shoutbox.php";
}
else
{
	$shoutbox = new shoutbox();
	$shoutbox->plugin();
}
?>
