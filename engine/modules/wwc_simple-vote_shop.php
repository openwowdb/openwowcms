<?php
global $user,$db,$lang,$config,$Html;
$limit=100;
$dorv=0;
$color_array = array (
'0'=>'gray',
'1'=>'white',
'2'=>'#25FF16',
'3'=>'#0070AC',
'4'=>'#A335EE',
'5'=>'#FF8000',
);
/**
* Access premission:
**/
if(!$user->logged_in){ if (!isset($proccess)) echo "<a href='./?page=loginout'>".$lang['Login']."</a>"; return; }

if (isset($config['module_simple_vote_shop_accessprems']))
{
if ($config['module_simple_vote_shop_accessprems']!='' && $config['module_simple_vote_shop_accessprems']!='|')
{
$ac_prems=explode("|",$config['module_simple_vote_shop_accessprems']);
foreach($ac_prems as $ac_prems_value)
{
if (strtolower($user->userlevel)==strtolower($ac_prems_value))
{
if (!isset($proccess))
echo $lang['No premission'].'.'; return;
}
}
}
}

/**
* MODULE SELF INSTALLATION:
**/
if (!isset($proccess))
{
if($Html->moduleinstall('module_simple_vote_shop_pointscale',
array(
'module_simple_vote_shop_pointscale',
'module_simple_vote_shop_accessprems',
),
array(
'1',
'|',
),
array(
'Vote shop points multiplier, usefull if you want to have discount day.',
'Leave | to allow access to all, enter GM level premissions which to allow access, seperate with |.',
),
//sql's to execute:
array(
"CREATE TABLE `wwc2_shop` (
`id` int(11) NOT NULL AUTO_INCREMENT,
`sep` varchar(3) COLLATE latin1_general_ci NOT NULL DEFAULT '0',
`name` varchar(200) COLLATE latin1_general_ci NOT NULL DEFAULT 'Unknown Item',
`itemid` varchar(20) COLLATE latin1_general_ci DEFAULT NULL,
`color` varchar(20) COLLATE latin1_general_ci NOT NULL DEFAULT '0',
`cat` varchar(20) COLLATE latin1_general_ci NOT NULL DEFAULT '0',
`sort` varchar(10) COLLATE latin1_general_ci NOT NULL DEFAULT '0',
`cost` varchar(11) COLLATE latin1_general_ci NOT NULL DEFAULT '0',
`charges` varchar(11) COLLATE latin1_general_ci NOT NULL DEFAULT '0',
`donateorvote` int(5) NOT NULL DEFAULT '0' COMMENT '0 is vote 1 is donation item',
`description` varchar(255) COLLATE latin1_general_ci DEFAULT 'No Description',
`description2` varchar(255) COLLATE latin1_general_ci DEFAULT '',
`allowedrealms` varchar(20) COLLATE latin1_general_ci DEFAULT '0' COMMENT '0-1-2',
`custom` varchar(3) COLLATE latin1_general_ci NOT NULL DEFAULT '0',
PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;
"
)
))
return;
}
/**
* :END MODULE SELF INSTALLATION
**/

$notice=false;
/**
* This part of website is executed before any output is given
* so every post data is processed here then using Header(Location:)
* we simply call normal site and display errors
**/
if(isset($proccess) && $proccess == TRUE){

if(!$user->logged_in) header("Location: index.php");
/**
* Processes the user submitted login form, if errors
* are found, the user is redirected to correct the information,
* if not, the user is effectively logged in to the system.
* If user is logged in, he will be logged out and redirected to
* index.php page.
*/
function SendVoteItem(){

global $user, $db, $config,$lang;
$dorv=0;
$ref=rand(1,999999);

if (!isset($_POST['itemsgrup'])) {$_SESSION['notice']='Please select item.<br><br><a href="./?page=wwc_simple-vote_shop">Go to Shop</a>'; return; }
/* filters */
$char_data = explode("-",preg_replace( "/[^0-9-]/", "", $_POST['character'] )); //[0]=> realm id (0->); [1]=> char guid
$shopid = preg_replace( "/[^0-9]/", "", $_POST['itemsgrup'] );
$subject = 'VoteShopREF'.$ref;


/* Get character and item info */
$db_realm = connect_realm($char_data[0]);
$char_info0 = $db_realm->query( $user->CoreSQL(1 ,$char_data[1], $char_data[0] ) ) or die ($db->error('error_msg'));
$char_info = $db_realm->fetch_array( $char_info0 );
$q="SELECT itemid,charges,cost,allowedrealms,sep FROM ".$config['engine_web_db'].".wwc2_shop WHERE id='".$shopid."' AND donateorvote='".'0'."' LIMIT 1";
$item_info0 = $db->query( $q ) or die($db->error('error_msg'));
$item_info = $db->fetch_array( $item_info0 );

/* Point check and realm premission check */
$allowedrealms = explode("-",$item_info['3']);
if ( in_array($char_data[0],$allowedrealms) )
{
if (($user->userinfo['vp']-($item_info[2]*$config['module_simple_vote_shop_pointscale']))>='0')//pass
{

/* sendmail item */
if ($item_info[4]=='0')
$_SESSION['notice'] = $user->sendmail($char_info[0], $char_data[1], $subject, $item_info[0], $char_data[0], $item_info[1]);

/* sendmail money */
elseif($item_info[4]=='2')
$_SESSION['notice'] = $user->sendmail($char_info[0], $char_data[1], $subject, '', $char_data[0], '1', $item_info[0]);
//sendmail($playername, $playerguid, $subject, $item, $realmid=0, $stack=1, $money=0, $externaltext=false)
//sendmail($playername, $playerguid, $subject, $item, $realmid=0, $stack=1, $money=0, $externaltext=false)

//check if mail is success <!-- success -->
if (substr($_SESSION['notice'],0,16)=='<!-- success -->')
$db->updateUserField($user->username,'vp', ($user->userinfo['vp']-($item_info[2]*$config['module_simple_vote_shop_pointscale'])));

if (strtolower($user->userlevel)==strtolower($config['premission_admin']))
$_SESSION['notice'] .= '<script type="text/javascript">function showhidecontent(){document.getElementById("dinfo").style.display=\'block\';} </script><br><a href="#" onclick="javascript:showhidecontent(); return false;">Info</a><div id="dinfo" style="display:none">-- '.$lang['For admins only'].' --<br>'.$lang['Char name'].': '.$char_info[0].'<br>'.$lang['Char guid'].': '.$char_data[1].'<br>'.$lang['Subject'].': '.$subject.'<br>'.$lang['Item'].': '.$item_info[0].'<br>'.$lang['Realm'].': '. $char_data[0].'<br>'.$lang['Stack'].': '.$item_info[1].'<br>SQL1: '.$user->CoreSQL(1 ,$char_data[1], $char_data[0] ).'<br>SQL2: '.$q.'</div><br><br><a href="./?page=wwc_simple-vote_shop">'.$lang['Go to Shop'].'</a>';
}
}
else
$_SESSION['notice'] = $lang['This item can not be traded in realm your character is in'].'.<br><br><a href="./?page=wwc_simple-vote_shop">Go to Shop</a>';

}
function CreateVoteItem(){
global $user, $db, $config,$lang;
$dorv=0;
/* access protection */
if (strtolower($user->userlevel)!=strtolower($config['premission_admin'])) return;
/* if editing item, $_POST['id'] exists, delete current item and just insert new */
if (isset($_POST['id']))
{

$db->query( "DELETE FROM ".$config['engine_web_db'].".wwc2_shop WHERE id='".preg_replace( "/[^0-9]/", "", $_POST['id'] )."' AND donateorvote='0'" ) or die($db->error('error_msg'));
}
if ($_POST['name']=='' ) $_POST['name']='Unknown Item';
if ($_POST['points']=='' ) $_POST['points']='0';
if ($_POST['charges']=='' ) $_POST['charges']='1';
/* add item to shop */
$q="INSERT INTO ".$config['engine_web_db'].".wwc2_shop (sep,name,itemid,color,cat,sort,cost,charges,donateorvote,description,description2,allowedrealms,custom) VALUES (
'".preg_replace( "/[^0-9]/", "", $_POST['sep'] )."',
'".$db->escape(htmlspecialchars( $_POST['name'] ))."',
'".preg_replace( "/[^0-9]/", "", $_POST['itemid'] )."',
'".preg_replace( "/[^0-9]/", "", $_POST['color'] )."',
'".preg_replace( "/[^A-Za-z0-9]/", "", $_POST['cat'] )."',
'".preg_replace( "/[^A-Za-z0-9]/", "", $_POST['sort'] )."',
'".preg_replace( "/[^0-9]/", "", $_POST['points'] )."',
'".preg_replace( "/[^0-9]/", "", $_POST['charges'] )."',
'0',
'".$db->escape(htmlspecialchars($_POST['description']))."',
'".$db->escape($_POST['description2'])."',
'".preg_replace( "/[^0-9-]/", "", $_POST['allowedrealms'] )."',
'".preg_replace( "/[^0-9]/", "", $_POST['custom'] )."'
)";
$q2 = $db->query( $q ) or die('ERROR EDITING: '.$db->error('error_msg'));
if ($q2 ) $_SESSION['notice'] = $lang['Item'].' '.$lang['is saved'].'.<br><br><a href="./?page=wwc_simple-vote_shop">'.$lang['Go to Shop'].'</a>';

}

function DeleteItem()
{
global $user, $db, $config,$lang;
$dorv=0;
/* access protection */
if (strtolower($user->userlevel)!=strtolower($config['premission_admin'])) return;
/* delete item id */

$delid=preg_replace( "/[^0-9]/", "", $_GET['delid'] );


if (isset($_GET['confirm']) == 'yes')
{
$db->query("DELETE FROM ".$config['engine_web_db'].".wwc2_shop WHERE id='".$delid."' LIMIT 1") or die (mysql_error());
$_SESSION['notice'] ="<center>Item deleted!<br><br><a href='./?page=wwc_simple-vote_shop'>".$lang['Go to Shop']."</a></center>";
}
else
{
$_SESSION['notice'] = "<center>Are you sure you want delete this item?<br><br><a href='./?page=wwc_simple-vote_shop&delid=".$delid."&confirm=yes'>".$lang['Yes']."</a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href='./?page=wwc_simple-vote_shop'>".$lang['No']."</a></center>";
}

}

if (isset($_POST['action'])){
/* Initialize process */
SendVoteItem();
}
else if (isset($_POST['additem'])){
/* Initialize process */
CreateVoteItem();
}
else if (!isset($_POST['additem']) && !isset($_POST['action']) && isset($_GET['delid'])){
/* Initialize process */
DeleteItem();
}

/* Reinitilaze 'form' proccess with latest session data */
Form::_Form();
return;

}


?>
<!-- This element is important, must be at beginning of module output, dont change it, except module name -->
<div class="post_body_title"><?php echo $lang['Vote']; ?></div>

<?php
/**
* Notification
**/
if (isset($_SESSION['notice'])){
echo $_SESSION['notice'];
unset($_SESSION['notice']);
return;
}
/**
* PRINT SHOP
**/
$cont2=false;
$q="SELECT * FROM ".$config['engine_web_db'].".wwc2_shop WHERE donateorvote='".'0'."' ORDER BY cat, sort ASC LIMIT ".$limit."";
$items0 = $db->query($q)or die($db->error('error_msg'));
while ($items=$db->fetch_assoc($items0))
{
/* its seperator */
if ($items['sep']=='1')
{
$cont2.= "<tr><td colspan='4'>";
$cont2.= "<strong><i>".$items['name']."</i></strong>";
if (strtolower($user->userlevel)==strtolower($config['premission_admin']))
{
$cont2.= '<br><a href="./?page=wwc_simple-vote_shop&delid='.$items['id'].'" title="'.$lang['Delete'].'">[x]</a> <a href="./?page=wwc_simple-vote_shop&edit='.$items['id'].'#hredit"  title="Edit">[E]</a> &laquo;'.$items['cat'].'-'.$items['sort'].'&raquo; ';
}
$cont2.= "</td></tr>";
}
/* its money */
elseif($items['sep']=='2')
{
$cont2.= '<tr onmouseover="this.style.backgroundImage = \'url(./engine/res/transp-green.png)\';" onmouseout="this.style.backgroundImage = \'none\'; ">';
$cont2.= "<td>";

$cont2.= "".$Html->formatmoney($items['itemid'])."";


if (strtolower($user->userlevel)==strtolower($config['premission_admin']))
{
$cont2.= '<br><a href="./?page=wwc_simple-vote_shop&delid='.$items['id'].'" title="'.$lang['Delete'].'">[x]</a> <a href="./?page=wwc_simple-vote_shop&edit='.$items['id'].'#hredit">[E]</a> &laquo;'.$items['cat'].'-'.$items['sort'].'&raquo;';
}
$cont2 .='</td>';

$cont2.= "<td></td>";
$cont2.= "<td>".$items['description']."</td>";

$cont2.= "<td>".($items['cost']*$config['module_simple_vote_shop_pointscale'])."</td>";
$cont2.= '<td><input type="radio" name="itemsgrup" value="'.$items['id'].'" />';

$cont2.='</td> </tr>';
}
/* its item */
else
{
$cont2.= '<tr onmouseover="this.style.backgroundImage = \'url(./engine/res/transp-green.png)\';" onmouseout="this.style.backgroundImage = \'none\'; ">';
$cont2.= "<td>";

if ($items['custom']=='1')
$cont2.= '<span style="color:'.$color_array[$items['color']].'" onmouseover="$WowheadPower.showTooltip(event, \'<font color='.$color_array[$items['color']].'>'.$items['name'].'</font><br>'.$items['description2'].'\')" onmousemove="$WowheadPower.moveTooltip(event)" onmouseout="$WowheadPower.hideTooltip();">['.$items['name'].']</span>';
else
$cont2.= "<a class='q".$items['color']."' href='http://www.wowhead.com/?item=".$items['itemid']."'>[".$items['name']."]</a>";


if (strtolower($user->userlevel)==strtolower($config['premission_admin']))
{
$cont2.= '<br><a href="./?page=wwc_simple-vote_shop&delid='.$items['id'].'">[x]</a> <a href="./?page=wwc_simple-vote_shop&edit='.$items['id'].'#hredit">[E]</a> &laquo;'.$items['cat'].'-'.$items['sort'].'&raquo;';
}
$cont2 .='</td>';
if ($items['charges']=='0' || $items['charges']=='1')
{
$charges='';
}
else
{
$charges='x'.$items['charges'];
}
$cont2.= "<td>".$charges."</td>";
$cont2.= "<td>".$items['description']."</td>";

$cont2.= "<td>".($items['cost']*$config['module_simple_vote_shop_pointscale'])."</td>";
$cont2.= '<td><input type="radio" name="itemsgrup" value="'.$items['id'].'" />';

$cont2.='</td> </tr>';
}
}
?>
<form method="post" action="">
<table border="0" width="99%" align="center" cellpadding="3" cellspacing="0">
<tr style=" background-image:url(./engine/res/transp-white.png)">
<td colspan="2"></td>
<td><?php echo $lang['Description']; ?></td>
<td><?php echo $lang['Cost']; ?></td>
<td><?php echo $lang['Buy?']; ?></td>
</tr>
<?php
echo $cont2;
?><tr><td colspan="4"><br />
<?php echo $lang['Select Your Chracter']; ?>: <?php $user->print_Char_Dropdown($user->userinfo['guid']); ?>&nbsp;&nbsp;<input name="action" type="submit" value="Purchase!" />
<br /><br />
<?php echo $lang['DONATION_1']; ?>
</tr></td>
</table></form>


<form action="" method="post">
<?php if (strtolower($user->userlevel)==strtolower($config['premission_admin'])) {
if (isset($_GET['edit']))
{

$q="SELECT * FROM ".$config['engine_web_db'].".wwc2_shop WHERE id='".preg_replace( "/[^0-9]/", "", $_GET['edit'] )."' AND donateorvote='".'0'."' LIMIT 1";
$item_info0 = $db->query( $q ) or die($db->error('error_msg'));
$edit = $db->fetch_array( $item_info0 );
echo '<input name="id" type="hidden" value="'.$edit['id'].'" /><hr id="hredit" /><strong>'.$lang['Editing shop ID'].': '.$edit['id'].'</strong>  <a href="./?page=wwc_simple-vote_shop#hredit">['.$lang['clear'].']</a>';
}
else {
$edit=false;
echo '<hr id="hredit" />';
}

?>
<table  border="0" align="center" cellpadding="3">
<tr>
<td><?php echo $lang['Type']; ?>:<br /></td>
<td><select name="sep">
<option value="0" <?php if ($edit['sep']=='0') echo 'selected="selected"'; ?>><?php echo $lang['Item']; ?></option>
<option value="1" <?php if ($edit['sep']=='1') echo 'selected="selected"'; ?>><?php echo $lang['Seperator *']; ?></option>
<option value="2" <?php if ($edit['sep']=='2') echo 'selected="selected"'; ?>><?php echo $lang['Money **']; ?></option>
</select></td>
</tr>
<tr>
<td><?php echo $lang['Is custom item?']; ?></td>
<td><select name="custom">
<option value="0"  <?php if ($edit['custom']=='0') echo 'selected="selected"'; ?>><?php echo $lang['No']; ?></option>
<option value="1" <?php if ($edit['custom']=='1') echo 'selected="selected"'; ?>><?php echo $lang['Yes']; ?></option>
</select></td>
</tr>
<tr>
<td><?php echo $lang['Available on']; ?>: </td>
<td><input name="allowedrealms" type="text" value="<?php echo $edit['allowedrealms']; ?>" /><br /><?php echo $lang['DONATION_2']; ?></td>
</tr>
<tr>
<td><?php echo $lang['Item'].' ID/'.$lang['Money **']; ?>:</td>
<td><input name="itemid" type="text"  value="<?php echo $edit['itemid']; ?>" /> <a href='http://www.wowhead.com' target="_blank"><strong>[WowHead]</strong></a></td>
</tr>
<tr>
<td><?php echo $lang['Item'].' '.$lang['name']; ?>:</td>
<td><input name="name" type="text"  value="<?php echo $edit['name']; ?>" /> *</td>
</tr>
<tr>
<td><?php echo $lang['Item'].' '.$lang['color']; ?>:</td>
<td><select name="color">
<option value="0" <?php if ($edit['color']=='0') echo 'selected="selected"'; ?>><?php echo $lang['Poor (gray)']; ?></option>
<option value="1" <?php if ($edit['color']=='1') echo 'selected="selected"'; ?>><?php echo $lang['Common (white)']; ?></option>
<option value="2" <?php if ($edit['color']=='2') echo 'selected="selected"'; ?>><?php echo $lang['Uncommon (green)']; ?></option>
<option value="3" <?php if ($edit['color']=='3') echo 'selected="selected"'; ?>><?php echo $lang['Rare (blue)']; ?></option>
<option value="4" <?php if ($edit['color']=='4') echo 'selected="selected"'; ?>><?php echo $lang['Epic (purple)']; ?></option>
<option value="5" <?php if ($edit['color']=='5') echo 'selected="selected"'; ?>><?php echo $lang['Legendary (orange)']; ?></option>
<option value="6" <?php if ($edit['color']=='6') echo 'selected="selected"'; ?>><?php echo $lang['Artifact']; ?></option>
</select></td>
</tr>
<tr>
<td><?php echo $lang['Description']; ?>:</td>
<td><input name="description" type="text"  value="<?php echo $edit['description']; ?>" /></td>
</tr>
<tr>
<td colspan="2"><?php echo $lang['Description'].' '.$lang['in custom item tooltip']; ?>:</td>
</tr>
<tr>
<td></td>
<td><input name="description2" type="text"  value="<?php echo $edit['description2']; ?>" /></td>
</tr>
<tr>
<td><?php echo $lang['Cost Points']; ?>:</td>
<td><input name="points" type="text"  value="<?php echo $edit['cost']; ?>" /></td>
</tr>
<tr>
<td><?php echo $lang['Item'].' '.$lang['Stack']; ?>:</td>
<td><input name="charges" type="text"  value="<?php echo $edit['charges']; ?>" /></td>
</tr>
<tr>
<td><?php echo $lang['Cat'].' '. $lang['sort']; ?>:</td>
<td><input name="cat" type="text" value="<?php echo $edit['cat']; ?>" />
&laquo;<strong>X</strong>-x&raquo;</td>
</tr>
<tr>
<td><?php echo ucwords($lang['sort']).' '.$lang['within'].' '.$lang['Cat']; ?>:</td>
<td><input name="sort" type="text"  value="<?php echo $edit['sort']; ?>" />
&laquo;x-<strong>X</strong>&raquo;</td>
</tr>
</table>
<center>
<br />
<br />
<input name="additem" type="submit" value="<?php echo $lang['Add/Edit']; ?>" />
</center>

</form>
<?php } ?>
