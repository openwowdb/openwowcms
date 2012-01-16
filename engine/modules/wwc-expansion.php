<?php
global $user,$db,$form,$lang,$config,$Html;

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
* MODULE versioncheck
**/
if (VERSION <= "1.1.1" && !isset($proccess))
{echo "This module requires CMS version 1.1.2 or higher, you are using ".VERSION.".";return;}
/**
* :END MODULE versioncheck
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
$expansion = preg_replace( "/[^0-9]/", "", $_POST['expansion'] );


/*change expansion!*/
$tel_db = $db->query( $user->CoreSQL( 4 ,$user->userinfo['guid'], $user->return_expansion($expansion)  ) ) or die ($db->error('error_msg'));
if ($tel_db) {
$_SESSION['notice'] ="<center><a href='./?page=wwc-expansion'>".$lang['OK']."</a></center>"; return;
}
else $_SESSION['notice'] ="<center>Failed!<br><br><a href='./?page=wwc-expansion'>".$lang['OK']."</a></center>";
}

if (isset($_POST['changeexp'])){
/* Initialize process */
Process();
}

/* Reinitilaze 'form' proccess with latest session data */
$form->_Form();
return;

}

$SelCla=''; $SelTBC=''; $SelWOL=''; $SelCat='';
if (isset($user->userinfo['expansion'])) {
	if ($user->userinfo['expansion']==0) $SelCla=' Selected';
	elseif ($user->userinfo['expansion']==1) $SelTBC=' Selected';
	elseif ($user->userinfo['expansion']==2) $SelWOL=' Selected';
	else $SelCAT=' Selected';
}

// Notification
if (isset($_SESSION['notice']) && $_SESSION['notice']<>''){
	echo "<div class=\"post_body_title\">NOTICE!</div>".$_SESSION['notice']."</div><div class=\"post_body\">";
	$_SESSION['notice']='';
	return;
}

?>
<!-- This element is important, must be at beginning of module output, dont change it, except module name -->
<div class="post_body_title">Expansion</div>
<center><form method="post">
<select name="expansion">
<option value="0"<?php echo $SelCla; ?>>Classic</option>
<option value="1"<?php echo $SelTBC; ?>>TBC</option>
<option value="2"<?php echo $SelWOL; ?>>WOTLK</option>
<option value="3"<?php echo $SelCAT; ?>>Cata</option>
</select>


&nbsp;&nbsp;<input name="changeexp" type="submit" value="<?php echo $lang['OK']; ?>" /></form><br />
</center>


