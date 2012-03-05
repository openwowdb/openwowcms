<?php
global $user,$db,$lang,$config,$Html;

/**
* This part of website is executed before any output is given
* so every post data is processed here then using Header(Location:)
* we simply call normal site and display errors
**/

/**
* Access premission:
**/
if(!$user->logged_in){ if (!isset($proccess)) echo "<a href='./?page=loginout'>".$lang['Login']."</a>"; return; }

/**
* MODULE SELF INSTALLATION (none for this module):
**/
/*if (!isset($proccess))
{
if($Html->moduleinstall('',array(),array(),array(),array()))
return;
}*/
/**
* :END MODULE SELF INSTALLATION
**/

if(isset($proccess) && $proccess == TRUE){
/**
* Processes the user submitted login form, if errors
* are found, the user is redirected to correct the information,
* if not, the user is effectively logged in to the system.
* If user is logged in, he will be logged out and redirected to
* index.php page.
*/
function Process(){
global $user, $form, $config,$db,$lang;

$charinfo = preg_replace( "/[^0-9-]/", "", $_POST['character'] );
$charinfo = explode("-", $charinfo );
$realmid=$charinfo[0];
$charguid=$charinfo[1];

/* Get character info */
$db_realm = connect_realm($realmid);
$char_info0 = $db_realm->query( $user->CoreSQL(1 ,$charguid, $realmid ) ) or die ($db->error('error_msg'));
$char_info = $db_realm->fetch_array( $char_info0 );

//unstuck!
//echo  $user->CoreSQL( 3 ,$char_info['guid'], $realmid  );exit;
$tel_db = $db_realm->query( $user->CoreSQL( 3 ,$char_info['guid'], $realmid  ) ) or die ($db->error('error_msg'));
if ($tel_db) {
$_SESSION['notice'] ="<center><a href='./?page=wwc-unstucker'>".$lang['OK']."</a></center>"; return;
}
else $_SESSION['notice'] ="<center>Failed!<br><br><a href='./?page=wwc-unstucker'>".$lang['OK']."</a></center>";
}

if (isset($_POST['unstuck'])){
/* Initialize process */
Process();
}

/* Reinitilaze 'form' proccess with latest session data */
Form::_Form();
return;

}

?>
<!-- This element is important, must be at beginning of module output, dont change it, except module name -->
<div class="post_body_title"><?php echo $lang['Unstucker']; ?></div>
<?php
/**
* Notification
**/
if (isset($_SESSION['notice'])){
echo $_SESSION['notice'];
unset($_SESSION['notice']);
return;
}
?>
<center><form method="post">
<?php
$user->print_Char_Dropdown($user->userinfo['guid']);
?>&nbsp;&nbsp;<input name="unstuck" type="submit" value="<?php echo $lang['OK']; ?>" /></form><br />
</center>

