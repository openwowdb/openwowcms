<center><strong>[Shoutbox]</strong></center><br>
<div id="shoutbox">
<?php
  if ($user->logged_in)
    echo '<center><input type="text" value="" id="shoutout" /><br><input type="button" onclick="addshout()" value="Shout it!" /></center>';
  else
    echo '<center><span style="color: red">Login to make a shoutout!</span></center>';
?>
  <div id="idle" style="display: none;"></div>
  <div id="shoutcontent" style="height:150px;overflow:auto;">
  </div>
</div>
<script type="text/javascript">
function addshout()
{
  var message = $('#shoutout').val();

  $.post("./engine/modules/shoutbox.php",
    { message: message },
    function (data) {
      updateshouts();
      $('#shoutout').val("");
    }
  );
  runtime = 0;
}

var runtime = 0;

function updateshouts()
{
  var nextid = $('div[id^="shoutmsg"]').size() + 1;
  $.post("./engine/modules/shoutbox.php",
    { latest: nextid },
    function (data) {
      $('#shoutcontent').prepend(data);
    }
  );
  if (<?php echo $config['shoutbox_idle_time'];?> == 0 || runtime < <?php echo $config['shoutbox_idle_time'];?>)
  {
    setTimeout("updateshouts()", <?php echo ($config['shoutbox_refresh_time']*1000);?>);
    runtime += <?php echo $config['shoutbox_refresh_time'];?>;
  }
  else
  {
    $('#idle').html('You have been idle for <?php echo $config['shoutbox_idle_time'];?> secs <a href="javascript:void();" onclick="resetShoutbox();">click here to reload</a>');
    $('#idle').fadeIn('slow');
  }
}

function resetShoutbox()
{
  runtime = 0;
  updateshouts();
  updatetimes();
  $("#idle").fadeOut('slow');
}

function updatetimes()
{
  if (<?php echo $config['shoutbox_update_old_time']; ?> == 0) return;
  var times = $('var[id^="time_"]');
  var post = "";
  for (var i = 0; i < times.size(); i++)
  {
    var obj = $(times[i]);
    post += obj.attr('id') + "|" + obj.html() + ";";
  }
  $.post("./engine/modules/shoutbox.php", {data: post, dotimeupdate: true}, function(data) {$('#shoutcontent').append(data);});
  setTimeout("updatetimes()", <?php echo ($config['shoutbox_update_old_time']*1000);?>);
}
resetShoutbox();
</script>