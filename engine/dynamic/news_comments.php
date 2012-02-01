<?php
###################################################################
# This file is a part of OpenWoW CMS by www.openwow.com
#
#   Project Owner    : OpenWoW CMS (http://www.openwow.com)
#   Copyright        : (c) www.openwow.com, 2010
#   Credits          : Based on work done by AXE and Maverfax
#   License          : GPLv3
##################################################################

/**
* Configuration:
*/
$comments_per_page=5;
/**
* End Configuration
*/





define('PATHROOT', '../../');
include (PATHROOT.'engine/init.php');
include_once(PATHROOT."engine/func/parser.php");
include_once(PATHROOT."engine/func/nicetime.php");

if ($_GET['newsid'])
{
	$newsid = preg_replace( "/[^0-9]/", "", $_GET['newsid'] );
	$start = isset($_GET['start']) ? preg_replace( "/[^0-9]/", "", $_GET['start'] ) : '';
	if ($start=='') $start=0;	
	
		
	if (!isset($_GET['nobox']) && $user->logged_in)
	{
	?><textarea name="comment<?php echo $newsid; ?>" id="comment<?php echo $newsid; ?>" style="width:95%"></textarea>
	<span class="news_comment_post"><a href="#" onclick="$.post('./engine/dynamic/news_comments.php?newsid=<?php echo $newsid; ?>', {comment<?php echo $newsid; ?>:document.getElementById('comment<?php echo $newsid; ?>').value}, function(data) {ajax_loadContent('comments_first<?php echo $newsid.'_'.$start; ?>','./engine/dynamic/news_comments.php?newsid=<?php echo $newsid; ?>&latest=true&nobox&nomore&nocache=<?php echo rand(1,9999999); ?>','false')});return false;
	"><?php echo $lang['Post comment'] ?></a></span>
	<?php
	echo '<div id="comments_first'.$newsid.'_'.$start.'"></div>';
	}	
		
	
		
		
		
	
	if (isset($_POST['comment'.$newsid]))
	{
		if($user->logged_in && trim($_POST['comment'.$newsid])<>'')
		$db->query("INSERT INTO ".$config['engine_web_db'].".wwc2_news_c (poster,content,newsid,timepost,datepost) VALUES ('".$db->escape($user->username)."','".$db->escape($_POST['comment'.$newsid])."','".$newsid."','".date("U")."', '')") or die(mysql_error());
		exit;
	}
	if (isset($_GET['latest']))
	{
		//just a quick display fix... if there is no last comment by user (he posted empty box) then just exit
		// this still will display double post if latest poster were user itself.. oh well who cares...
		$comments_sql=$db->query("SELECT * FROM ".$config['engine_web_db'].".wwc2_news_c WHERE newsid='".$newsid."' ORDER by id DESC LIMIT 1") or die(mysql_error());
		$comments=$db->fetch_array($comments_sql);
		if (strtoupper($comments['poster']) <> strtoupper($user->username))
		exit;
		$start='0';$comments_per_page='1';
	}	
	elseif(isset($_GET['delete']))
	{
		//we have to get comment with his id and check poster.... oh god help us all...
		$comments_sql=$db->query("SELECT * FROM ".$config['engine_web_db'].".wwc2_news_c WHERE id='".$newsid."' LIMIT 1") or die(mysql_error());
		$comments=$db->fetch_array($comments_sql);
		if($user->logged_in && strtoupper($comments['poster']) == strtoupper($user->username) or (strtolower($user->userlevel)==strtolower($config['premission_admin']) or strtolower($user->userlevel)==strtolower($config['premission_gm']))){
		$db->query("DELETE FROM ".$config['engine_web_db'].".wwc2_news_c WHERE id='".$newsid."' LIMIT 1") or die(mysql_error());
		
		}
		exit;
	}
	$comments_sql=$db->query("SELECT * FROM ".$config['engine_web_db'].".wwc2_news_c WHERE newsid='".$newsid."' ORDER BY id DESC LIMIT ".$start." , ".$comments_per_page) or die(mysql_error());
	//echo "SELECT * FROM ".$config['engine_web_db'].".wwc2_news_c WHERE newsid='".$newsid."' ORDER BY id DESC LIMIT ".$start." , ".$comments_per_page;
	$start=$start+$comments_per_page;
	//if no commments
	if ($db->num_rows($comments_sql)<>'0')
	{
		//if yes comments
		while ($comments=$db->fetch_array($comments_sql))
		{
			//get poster ID
			$userinfo=$user->getUserInfo($comments['poster']);
			if (!file_exists(PATHROOT.'/engine/res/avatars/'.$userinfo['avatar'].'.gif'))
				$avatarurl='./engine/res/avatars/default.gif';
			else
				$avatarurl='./engine/res/avatars/'.$userinfo['avatar'].'.gif';
			echo '<div id="singlecomment'.$comments['id'].'">';
			if($user->logged_in && strtoupper($comments['poster']) == strtoupper($user->username) or (strtolower($user->userlevel)==strtolower($config['premission_admin']) or strtolower($user->userlevel)==strtolower($config['premission_gm'])))
			echo '<span style="float:right"><a href="#" onclick="ajax_loadContent(\'singlecomment'.$comments['id'].'\',\'./engine/dynamic/news_comments.php?newsid='.$comments['id'].'&start='.$start.'&nobox&nomore&delete\',\'false\');return false;">[x]</a></span>';
			
	echo '<table width="100%" border="0" cellspacing="3px">
	  <tr>
		<td width="64px"><div class="avatar"><img src="'.$avatarurl.'" /></div></td>
		<td><div class="comments_poster"><a href="./?page=profile&id='.$userinfo['guid'].'">'.$comments['poster'].'</a> ('.nicetime($comments['timepost']).')</div><div class="comments_body">'.do_bbcode($comments['content']).'</div></td>
	  </tr>
	</table></div>';
		}
	}
}
echo '</div>';
if (!isset($_GET['nobox']) or !isset($_GET['nomore']))
echo '<div id="comments_more'.$newsid.'_'.$start.'">
		<div class="more_comments">
			<a href="#" onclick="ajax_loadContent(\'comments_more'.$newsid.'_'.$start.'\',\'./engine/dynamic/news_comments.php?newsid='.$newsid.'&start='.$start.'&nobox\',\'false\');return false;">'.$lang['More comments'].'</a>
		</div>
	</div>';
