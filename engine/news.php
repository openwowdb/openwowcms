<?php
/************************************************************************
*														 	 engine/news.php
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

global $lang,$db,$config;
include_once("./engine/func/parser.php");
include_once("./engine/func/nicetime.php");
?>
<!-- This element is important, must be at beginning of module output, dont change it, except module name -->
<div class="post_body_title"><?php echo $lang['News']; if ($user->isAdmin()) echo '<span style="float: right;margin-right:30px"><a href="javascript:void(0);" onclick="createnews();">Create</a></span>';?></div>
<?php
$news_sql = $db->query("SELECT * FROM ".$config['engine_web_db'].".wwc2_news WHERE hidden='0' ORDER BY stickied DESC,id DESC LIMIT 20") or die($db->getLastError());
$lastid = 0;
while ($news = $db->getRow($news_sql))
{
	//get comment count
	$comments_count_sql=$db->query("SELECT count(*) FROM ".$config['engine_web_db'].".wwc2_news_c WHERE newsid='".$news['id']."'") or die($db->getLastError());
	$comments_count = $db->getRow($comments_count_sql);
	//print news structure
	echo '
	<div class="newstitle" id="newstitle'.$news['id'].'">'.$news['title'].'</div>
	<div class="newscontent">';
	$message = parse_message($news['content']);
	echo ($user->isAdmin() ? '<span id="newscontent'.$news['id'].'" ondblclick="dy_edit_news(\''.$news['id'].'\');">'.$message.'</span>' : $message);
	echo '</div>';
	echo '<span class="newsbottom"><span>(<a href="javascript:void(0);" onclick="get_comments(\''.$news['id'].'\', \'0\');$(this).attr(\'onclick\', \'hide_comments('.$news['id'].');\');return false;">'.$comments_count[0].' '.$lang['comments'].'</a>)</span>'.$lang['Posted'].' '.nicetime($news['timepost']).'</span><div style="height:10px"></div>';

	//for dynamic news content
	echo '<div id="comments_'.$news['id'].'" class="comments_box"></div><hr /><div style="clear:both;height:20px"></div>';
	$lastid = $news['id'];
}
?>
<script type="text/javascript" src="./engine/js/bbcode_buttons.js"></script>
<script type="text/javascript">
	function hide_comments(newsid) {
		$('#comments_'+newsid).toggle("slow");
	}

	<?php if ($user->logged_in) { ?>
	function remove_comment(newsid, commentid)
	{
		$.get('./engine/dynamic/news_comments.php', {newsid: commentid, 'delete': true},
		function(data) {
			var a = $('#comments_more_'+newsid+'>div>a');
			var onclick = $(a).attr('onclick');
			if (onclick)
				$(a).attr('onclick', onclick.replace(/(,\d+)/i, "$1-1"));
		});
	}

	function post_comment(newsid)
	{
		$.post('./engine/dynamic/news_comments.php?newsid=' + newsid,
		{ comment: $('#comment' + newsid).val() },
		function(data) {
			$($(data).get()).each(
				function(){
					$(this).hide();
					$(this).prependTo('#comments_'+newsid).delay(300).slideDown('slow');
			});
			$("#newcomment" + newsid).prependTo('#comments_'+newsid);
			var a = $('#comments_more_'+newsid+'>div>a');
			var onclick = $(a).attr('onclick');
			if (onclick)
				$(a).attr('onclick', onclick.replace(/(,\d+)/i, "$1+1"));
		});
	}
	<?php } ?>

	function get_comments(newsid, start)
	{
		<?php if ($user->logged_in) { ?>
		// Create textarea
		if (start == 0)
		{
			$('<div id="newcomment'+newsid+'"><textarea name="comment'+newsid+'" id="comment'+newsid+'" style="width:95%"></textarea>'+
			'<span class="news_comment_post"><a href="javascript:void(0);" onclick="post_comment('+newsid+');"><?php echo $lang['Post comment'] ?></a></span></div>'
			).appendTo('#comments_'+newsid);
		}
		<?php } ?>
		$('#comments_more_'+newsid).remove();
		$.get('./engine/dynamic/news_comments.php', {newsid: newsid, start: start},
			function(data) {
				$($(data).get()).each(
				function()
				{
					$(this).hide();
					$(this).appendTo('#comments_'+newsid).delay(300).slideDown('slow');
				});
				$('#comments_more_'+newsid).appendTo('#comments_'+newsid);
			});
	}
</script>
<?php
if (!$user->isAdmin())
	return;
?>
<script type="text/javascript">
function dy_edit_news(id)
{
	var b = $('#newscontent'+id).html();
	$('#newscontent'+id).html('<textarea name="newscontent'+id+'" id="textarea_newscontent'+id+'" style="width:99%; height:200px"><?php echo $lang['Loading']; ?>...</textarea><span style="float:right"><a href="javascript:void();" onclick="insert_text(\'[code]\',\'[/code]\',\'textarea_newscontent'+id+'\');return false;"><img src="./engine/res/CODE.png"></a>&nbsp;<a href="javascript:void();" onclick="insert_text(\'[item=30312]\',\'[/item]\',\'textarea_newscontent'+id+'\');return false;"><img src="./engine/res/ITEM.png"></a>&nbsp;<a href="javascript:void();" onclick="insert_text(\'[img]\',\'[/img]\',\'textarea_newscontent'+id+'\');return false;"><img src="./engine/res/IMG.png"></a>&nbsp;<a href="javascript:void();" onclick="insert_text(\'[url]\',\'[/url]\',\'textarea_newscontent'+id+'\');return false;"><img src="./engine/res/URL.png"></a>&nbsp;<a href="javascript:void();" onclick="insert_text(\'[u]\',\'[/u]\',\'textarea_newscontent'+id+'\');return false;"><img src="./engine/res/U.png"></a>&nbsp;<a href="javascript:void();" onclick="insert_text(\'[i]\',\'[/i]\',\'textarea_newscontent'+id+'\');return false;"><img src="./engine/res/I.png"></a>&nbsp;<a href="javascript:void();" onclick="insert_text(\'[b]\',\'[/b]\',\'textarea_newscontent'+id+'\');return false;"><img src="./engine/res/B.png"></a>&nbsp;<img src="./engine/res/spacer.png">&nbsp;<a href="javascript:void();" onclick="dy_edit_save('+id+');return false;"><img src="./engine/res/OK.png"></a>&nbsp;<a href="javascript:void();" onclick="dy_edit_cancel('+id+');return false;"><img src="./engine/res/CANCEL.png"></a></span><div style="height:30px"></div>');
	$('#textarea_newscontent'+id).load("./engine/dynamic/news_save.php?getbody&id="+id, function() {$('#newscontent'+id).attr("ondblclick", "");});
}

function dy_edit_cancel(id,b)
{
	$('#newscontent'+id).load("./engine/dynamic/news_save.php?getbodyparsed&id="+id, function() {$('#newscontent'+id).attr("ondblclick", "dy_edit_news(" + id + ")");});
}

function dy_edit_save(id)
{
	$.post("./engine/dynamic/news_save.php?save&id="+id,
		{message: $("#textarea_newscontent"+id).val()},
		function(data) {$('#newscontent'+id).html(data); $('#newscontent'+id).attr("ondblclick", "dy_edit_news(" + id + ")");}
	);
}

function dy_new_save()
{
	$.post("./engine/dynamic/news_save.php?save&new=true",
		{title: $("#newstitle").val(), message: $("#textarea_newscontent").val()},
		function(data) {
			$(location).attr('href','./index.php');
		}
	);
}

function dy_new_cancel()
{
	$('#createnews').remove();
}

function createnews()
{
	$('#newstitle<?php echo $lastid;?>').parent().prepend(
		'<div id="createnews"><input type="text" value="Title" id="newstitle" />' +
		'<textarea name="newscontent" id="textarea_newscontent" style="width:99%; height:200px"></textarea>' +
		'<span style="float:right"><a href="javascript:void(0);" onclick="insert_text(\'[code]\',\'[/code]\',\'textarea_newscontent\');return false;"><img src="./engine/res/CODE.png"></a>&nbsp;<a href="javascript:void();" onclick="insert_text(\'[item=30312]\',\'[/item]\',\'textarea_newscontent\');return false;"><img src="./engine/res/ITEM.png"></a>&nbsp;<a href="javascript:void();" onclick="insert_text(\'[img]\',\'[/img]\',\'textarea_newscontent\');return false;"><img src="./engine/res/IMG.png"></a>&nbsp;<a href="javascript:void();" onclick="insert_text(\'[url]\',\'[/url]\',\'textarea_newscontent\');return false;"><img src="./engine/res/URL.png"></a>&nbsp;<a href="javascript:void();" onclick="insert_text(\'[u]\',\'[/u]\',\'textarea_newscontent\');return false;"><img src="./engine/res/U.png"></a>&nbsp;<a href="javascript:void();" onclick="insert_text(\'[i]\',\'[/i]\',\'textarea_newscontent\');return false;"><img src="./engine/res/I.png"></a>&nbsp;<a href="javascript:void();" onclick="insert_text(\'[b]\',\'[/b]\',\'textarea_newscontent\');return false;"><img src="./engine/res/B.png"></a>&nbsp;<img src="./engine/res/spacer.png">&nbsp;<a href="javascript:void(0);" onclick="dy_new_save();return false;"><img src="./engine/res/OK.png"></a>&nbsp;<a href="javascript:void();" onclick="dy_new_cancel();return false;"><img src="./engine/res/CANCEL.png"></a></span><div style="height:30px"></div></div>'
	);
}
</script>