<center><strong>[Shoutbox]</strong></center><br>
<?php
if(!isset($config['shoutbox_refresh_time']))
{
	echo 'Shoutbox has not been setup, go to: <a href="?page=shoutbox">?page=shoutbox</a>';
	return;
}
?>
<div id="shoutbox">
<?php
if ($user->logged_in)
	echo '<center><input type="text" value="" id="shoutout" /><br><input type="button" onclick="shoutbox.addshout()" value="Shout it!" /></center>';
  else
    echo '<center><span style="color: red">Login to make a shoutout!</span></center>';
?>
  <div id="idle" style="display: none;"></div>
  <div id="shoutcontent" style="height:150px;overflow:auto;">
  </div>
</div>
<script type="text/javascript" src="./engine/js/shoutbox.js"></script>
<script type="text/javascript">
shoutbox.lang = <?php echo json_encode($lang); ?>;
shoutbox.refresh_time = <?php echo $config['shoutbox_refresh_time'] * 1000; ?>;
shoutbox.idle_time = <?php echo $config['shoutbox_idle_time'] * 1000; ?>;
</script>
