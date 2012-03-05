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
* MODULE SELF INSTALLATION:
**/
if (!isset($proccess))
{
if(module_base::DoInstall('module_simple_teleporter_cost',
array(
'module_simple_teleporter_cost',
),
array(
'0',
),
array(
'How much cost per teleport.',
),
//sql's to execute:
array('')
))
return;
}
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
global $user, $config,$db,$lang;

$location = preg_replace( "/[^0-9]/", "", $_POST['location'] );
$charinfo = preg_replace( "/[^0-9-]/", "", $_POST['character'] );
$charinfo = explode("-", $charinfo );
$realmid=$charinfo[0];
$charguid=$charinfo[1];
/* Get character info */
$db_realm = connect_realm($realmid);
$char_info0 = $db_realm->query( $user->CoreSQL(1 ,$charguid, $realmid ) ) or die ($db->error('error_msg'));
$char_info = $db_realm->fetch_array( $char_info0 );

$map = "";
$x = "";
$y = "";
$z = "";
$place = "";

switch($location)
{
//stormwind
case 1:
$map = "0";
$x = "-8913.23";
$y = "554.633";
$z = "93.7944";
$place = "Stormwind City";
break;
//ironforge
case 2:
$map = "0";
$x = "-4981.25";
$y = "-881.542";
$z = "501.66";
$place = "Ironforge";
break;
//darnassus
case 3:
$map = "1";
$x = "9951.52";
$y = "2280.32";
$z = "1341.39";
$place = "Darnassus";
break;
//exodar
case 4:
$map = "530";
$x = "-3987.29";
$y = "-11846.6";
$z = "-2.01903";
$place = "The Exodar";
break;
//orgrimmar
case 5:
$map = "1";
$x = "1676.21";
$y = "-4315.29";
$z = "61.5293";
$place = "Orgrimmar";
break;
//thunderbluff
case 6:
$map = "1";
$x = "-1196.22";
$y = "29.0941";
$z = "176.949";
$place = "Thunder Bluff";
break;
//undercity
case 7:
$map = "0";
$x = "1586.48";
$y = "239.562";
$z = "-52.149";
$place = "The Undercity";
break;
//silvermoon
case 8:
$map = "530";
$x = "9473.03";
$y = "-7279.67";
$z = "14.2285";
$place = "Silvermoon City";
break;
//shattrath
case 9:
$map = "530";
$x = "-1863.03";
$y = "4998.05";
$z = "-21.1847";
$place = "Shattrath";
break;
//dalaran
case 10:
$map = "571";
$x = "5804,62";
$y = "619,803";
$z = "649";
$place = "Dalaran";
break;
//for unknowness -> error msg
default:
$_SESSION['notice'] ="<center>Invalid location!<br><br><a href='./?page=wwc_simple-teleporter'>".$lang['OK']."</a></center>";
return;
break;
}

//disallows factions to use enemy portals
switch($char_info['race'])
{
//alliance
case 1:
case 3:
case 4:
case 7:
case 11:
if((($location >=5) && ($location <=8)) && ($location != 9))
{
$_SESSION['notice'] ="<center>".$lang['TELEPORT_1']."!<br><br><a href='./?page=wwc_simple-teleporter'>".$lang['OK']."</a></center>";
return;
}
break;
//horde
case 2:
case 5:
case 6:
case 8:
case 10:
if ((($location >=1) && ($location <=4)) && ($location != 9))
{
$_SESSION['notice'] ="<center>".$lang['TELEPORT_2']."!<br><br><a href='./?page=wwc_simple-teleporter'>".$lang['OK']."</a></center>";
return;
}
break;
default:
//$_SESSION['notice']="<center>That is not a valid race!<br><br><a href='./?page=wwc_simple-teleporter'>".$lang['OK']."</a></center>";return;
break;
}

/*if($char_info['level'] < 58 && $location == 9)
{
$_SESSION['notice'] ="<center>Require at least lvl 58!</center><br><br><a href='./?page=wwc_simple-teleporter'>".$lang['OK']."</a>";
return;
}*/

$newGold = $char_info['money'] - ($config['module_simple_teleporter_cost']);
if ($newGold>='0')
{
//teleport the sucker!
$tel_db = $db_realm->query( $user->CoreSQL( 2 ,$map, $realmid, $x, $y, $z, $newGold, $char_info['guid'] ) ) or die ($db->error('error_msg'));
if ($tel_db) { $_SESSION['notice'] ="<center>".$lang['Teleported']."! ". $place.".<br><br><a href='./?page=wwc_simple-teleporter'>".$lang['OK']."</a></center>"; return; }
}
else $_SESSION['notice'] ="<center>".$lang['TELEPORT_3']."!</center><br><br><a href='./?page=wwc_simple-teleporter'>".$lang['OK']."</a>";



}

if (isset($_POST['teleport'])){
/* Initialize process */
Process();
}

/* Reinitilaze 'form' proccess with latest session data */
Form::_Form();
return;

}


?>
<!-- This element is important, must be at beginning of module output, dont change it, except module name -->
<div class="post_body_title"><?php echo $lang['Teleporter']; ?></div>
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
echo  "<select name='location'>";
echo  "<option value='1'>Stormwind</option>";
echo  "<option value='2'>Ironforge</option>";
echo  "<option value='3'>Darnassus</option>";
echo  "<option value='4'>Exodar</option>";
echo  "<option value='---------'>------------------</option>";
echo  "<option value='5'>Orgrimmar</option>";
echo  "<option value='6'>Thunder Bluff</option>";
echo  "<option value='7'>Undercity</option>";
echo  "<option value='8'>Silvermoon</option>";
echo  "<option value='---------'>------------------</option>";
echo  "<option value='9'>Shattrath</option>";
echo  "<option value='10'>Dalaran</option>";
echo  "</select>&nbsp;&nbsp;";
$user->print_Char_Dropdown($user->userinfo['guid']);
?>&nbsp;&nbsp;<input name="teleport" type="submit" value="<?php echo $lang['OK']; ?>" /></form><br />
<?php
echo $lang['Cost'].' '.$Html->formatmoney($config['module_simple_teleporter_cost']);
?>
</center>
