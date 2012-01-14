<?php
###################################################################
# This file is a part of OpenWoW CMS by www.openwow.com
#
#   Project Owner    : OpenWoW CMS (http://www.openwow.com)
#   Copyright        : (c) www.openwow.com, 2010
#   Credits          : Based on work done by AXE and Maverfax
#   License          : GPLv3
##################################################################

global $lang,$db,$config;
include_once("./engine/func/parser.php");
include_once("./engine/func/nicetime.php");
?><!-- This element is important, must be at beginning of module output, dont change it, except module name -->
<div class="post_body_title"><?php echo $lang['News']; ?></div>
<?php 
//Ok this is wierd operator "==" but i fixed it by adding strtolower!
if (strtolower($config['premission_admin'])==strtolower($user->userlevel)) {  ?>
<script type="text/javascript" src="./engine/js/bbcode_buttons.js"></script>
<script type="text/javascript">
function dy_edit_news(id)
{
	var b=document.getElementById('newscontent'+id).innerHTML;
	
	document.getElementById('newscontent'+id).innerHTML='<textarea name="newscontent'+id+'" id="textarea_newscontent'+id+'" style="width:99%; height:200px"><?php echo $lang['Loading']; ?>...</textarea><span style="float:right"><a href="#" onclick="insert_text(\'[code]\',\'[/code]\',\'textarea_newscontent'+id+'\');return false;"><img src="./engine/res/CODE.png"></a>&nbsp;<a href="#" onclick="insert_text(\'[item=30312]\',\'[/item]\',\'textarea_newscontent'+id+'\');return false;"><img src="./engine/res/ITEM.png"></a>&nbsp;<a href="#" onclick="insert_text(\'[img]\',\'[/img]\',\'textarea_newscontent'+id+'\');return false;"><img src="./engine/res/IMG.png"></a>&nbsp;<a href="#" onclick="insert_text(\'[url]\',\'[/url]\',\'textarea_newscontent'+id+'\');return false;"><img src="./engine/res/URL.png"></a>&nbsp;<a href="#" onclick="insert_text(\'[u]\',\'[/u]\',\'textarea_newscontent'+id+'\');return false;"><img src="./engine/res/U.png"></a>&nbsp;<a href="#" onclick="insert_text(\'[i]\',\'[/i]\',\'textarea_newscontent'+id+'\');return false;"><img src="./engine/res/I.png"></a>&nbsp;<a href="#" onclick="insert_text(\'[b]\',\'[/b]\',\'textarea_newscontent'+id+'\');return false;"><img src="./engine/res/B.png"></a>&nbsp;<img src="./engine/res/spacer.png">&nbsp;<a href="#" onclick="dy_edit_save('+id+');return false;"><img src="./engine/res/OK.png"></a>&nbsp;<a href="#" onclick="dy_edit_cancel('+id+');return false;"><img src="./engine/res/CANCEL.png"></a></span><div style="height:30px"></div>';
	jQuery(function($) {  $('#textarea_newscontent'+id).load("./engine/dynamic/news_save.php?getbody&id="+id);});
}
function dy_edit_cancel(id,b)
{
	jQuery(function($) {  $('#newscontent'+id).load("./engine/dynamic/news_save.php?getbodyparsed&id="+id);});
}
function dy_edit_save(id)
{
	$.post("./engine/dynamic/news_save.php?save&id="+id,{  
             message: $("#textarea_newscontent"+id).val(),  
           }, function(data) {  
         document.getElementById('newscontent'+id).innerHTML = data;  
       });
}
</script>
<?php 
}

$news_sql=$db->query("SELECT * FROM ".$config['engine_web_db'].".wwc2_news WHERE hidden='0' ORDER BY stickied DESC,id DESC LIMIT 20") or die( $db->error('error_msg') );

while ($news=$db->fetch_assoc($news_sql))
{
	//get comment count
	$comments_count_sql=$db->query("SELECT count(*) FROM ".$config['engine_web_db'].".wwc2_news_c WHERE newsid='".$news['id']."'") or die(mysql_error());
	$comments_count=$db->fetch_array($comments_count_sql);
	//print news structure
	echo '<div class="newstitle" id="newstitle'.$news['id'].'"><a href="#" onclick="ajax_loadContent(\'comments'.$news['id'].'\',\'./engine/dynamic/news_comments.php?newsid='.$news['id'].'\',\'...\');return false;">'.$news['title'].'</a></div><div class="newscontent" >';
	if ($user->userlevel==$config['premission_admin'])
		echo '<span id="newscontent'.$news['id'].'" ondblclick="dy_edit_news(\''.$news['id'].'\');">';
	echo parse_message($news['content']);
	if ($user->userlevel==$config['premission_admin'])
		echo '</span>';
	echo '</div>';
	echo '<span class="newsbottom"><span>(<a href="#" onclick="ajax_loadContent(\'comments'.$news['id'].'\',\'./engine/dynamic/news_comments.php?newsid='.$news['id'].'&nocache='.rand(1,999999).'\',\'...\');return false;">'.$comments_count[0].' '.$lang['comments'].'</a>)</span>'.$lang['Posted'].' '.nicetime(date("j M Y",$news['timepost'])).'</span><div style="height:10px"></div>';
	
	//for dynamic news content
	echo '<div id="comments'.$news['id'].'" class="comments_box"></div>';
	
}
?>
