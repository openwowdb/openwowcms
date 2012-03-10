<?php
/************************************************************************
*                        engine/modules/teleport.php
*                            -------------------
* 	 Copyright (C) 2011
*
* 	 This package is free software: you can redistribute it and/or modify
*    it under the terms of the GNU General Public License as published by
*    the Free Software Foundation, either version 3 of the License, or
*    (at your option) any later version.
*
* 	 Updated: 2012/03/04 14:00
*
************************************************************************/

// * This part of website is executed before any output is given
// * so every post data is processed here then using Header(Location:)
// * we simply call normal site and display errors

if (!defined('PATHROOT'))
	define('PATHROOT', '../../');

if (!class_exists("module_base"))
	include PATHROOT."library/classes/modules/modules.php";

if (!class_exists("teleporter"))
{
	class teleporter extends module_base {
		
		
		function teleporter($proccess) {
			global $config, $db;
			$this->proccess = $proccess;
			$this->configFields = array(
				'module_teleporter_cost' => array('0', 'How much cost per teleport.')
				);
			$this->sqlQueries = array('');
			$this->showInUserpanel = true;
		}

		function process() {
			global $config, $db, $user, $lang;
			if(!isset($user) || !$user->logged_in) { if (!$this->proccess) echo "<a href='index.php?page=loginout'>".$lang['Login']."</a>"; return; }
			$config['title'] = $lang['Teleporter']. ' - ' .$config['title'];
			if ($this->DoInstall()) return;
			if ($this->proccess == true) {
				if (isset($_POST['teleport'])){
					// * do something
			
					$location = preg_replace( "/[^0-9]/", "", $_POST['location'] );
					$charinfo = preg_replace( "/[^0-9-]/", "", $_POST['character'] );
					$charinfo = explode("-", $charinfo );
					$realmid=$charinfo[0];
					$charguid=$charinfo[1];
					/* Get character info */
					$db_realm = connect_realm($realmid);
					$char_info0 = $db_realm->query( $user->CoreSQL(1 ,$charguid, $realmid ) ) or die ($db->error('error_msg'));
					$char_info = $db_realm->getRow( $char_info0 );

					$map = "";
					$x = "";
					$y = "";
					$z = "";
					$place = "";

					switch($location) {
						//stormwind
						case 1:
							$map = "0";
							$zone = '1519';
							$x = "-8835.49";
							$y = "619.23";
							$z = "93.3324";
							$place = "Stormwind City";
							break;
						//ironforge
						case 2:
							$map = "0";
							$zone = '1537';
							$x = "-4929.001";
							$y = "-945.859";
							$z = "501.594";
							$place = "Ironforge";
							break;
						//darnassus
						case 3:
							$map = "1";
							$zone = '1657';
							$x = "9951.52";
							$y = "2280.32";
							$z = "1341.39";
							$place = "Darnassus";
							break;
						//exodar
						case 4:
							$map = "530";
							$zone = '3557';
							$x = "-3987.29";
							$y = "-11846.6";
							$z = "-2.01903";
							$place = "The Exodar";
							break;
						//orgrimmar
						case 5:
							$map = "1";
							$zone = '1637';
							$x = "1571.58";
							$y = "-4396.92";
							$z = "20.1049";
							$place = "Orgrimmar";
							break;
						//thunderbluff
						case 6:
							$map = "1";
							$zone = '1638';
							$x = "-1266.07";
							$y = "71.1610";
							$z = "127.5487";
							$place = "Thunder Bluff";
							break;
						//undercity
						case 7:
							$map = "0";
							$zone = '1497';
							$x = "1631.220";
							$y = "240.126";
							$z = "-43.102";
							$place = "The Undercity";
							break;
						//silvermoon
						case 8:
							$map = "530";
							$zone = '3487';
							$x = "9473.03";
							$y = "-7279.67";
							$z = "14.2285";
							$place = "Silvermoon City";
							break;
						//shattrath
						case 9:
							$map = "530";
							$zone = '3703';
							$x = "-1863.03";
							$y = "4998.05";
							$z = "-21.1847";
							$place = "Shattrath";
							break;
						//dalaran
						case 10:
							$map = "571";
							$zone = '4395';
							$x = "5804,62";
							$y = "619,803";
							$z = "649";
							$place = "Dalaran";
							break;
						//for unknowness -> error msg
						default:
							$_SESSION['notice'].="<center>Invalid location!<br><br><a href='./?page=wwc_simple-teleporter'>".$lang['OK']."</a></center>";
							return;
							break;
					}

					//disallows factions to use enemy portals
					switch($char_info[2]) {
						//alliance
						case 1:
						case 3:
						case 4:
						case 7:
						case 11:
							if((($location >=5) && ($location <=8)) && ($location != 9))
							{
							$_SESSION['notice'].="<center>".$lang['TELEPORT_1']."!<br><br><a href='./?page=wwc_simple-teleporter'>".$lang['OK']."</a></center>";
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
							$_SESSION['notice'].="<center>".$lang['TELEPORT_2']."!<br><br><a href='./?page=wwc_simple-teleporter'>".$lang['OK']."</a></center>";
							return;
							}
							break;
						default:
						//$_SESSION['notice'].="<center>That is not a valid race!<br><br><a href='./?page=wwc_simple-teleporter'>".$lang['OK']."</a></center>";return;
							break;
					}

					/* if($char_info['level'] < 58 && $location == 9) {
						$_SESSION['notice'].="<center>Require at least lvl 58!</center><br><br><a href='./?page=wwc_simple-teleporter'>".$lang['OK']."</a>";
						return;
					}*/

					$newGold = $char_info[6] - ($config['module_teleporter_cost']);
					if ($newGold>='0') {
						//teleport the sucker!
						$q = $user->CoreSQL( 2 ,$map, $realmid, $x, $y, $z, $zone, $newGold, $charguid );
						$tel_db = $db_realm->query( $q ) or die ($db->error('error_msg'));
						if ($tel_db) { $_SESSION['notice'].="<center>".$lang['Teleported']."! ". $place.".<br><br><a href='./?page=wwc_simple-teleporter'>".$lang['OK']."</a></center>"; return; }
					}
					else $_SESSION['notice'].="<center>".$lang['TELEPORT_3']."!</center><br><br><a href='./?page=wwc_simple-teleporter'>".$lang['OK']."</a>";

				}
				return parent::process();
			}
			//	CheckNotice();
			//* Notification
			if (isset($_SESSION['notice']) && $_SESSION['notice']<>''){
				echo $_SESSION['notice'];
				$_SESSION['notice']='';
				return;
			}			
			?>
			
			<!-- This element is important, must be at beginning of module output, dont change it, except module name -->
			<div class="post_body_title"><?php echo $lang['Teleporter']; ?></div>
			
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
//echo $lang['Cost'].' '.$Html->formatmoney($config['module_teleporter_cost']);
?>
</center>
			<?php
			
		}
	}
}

$teleporter = new teleporter(isset($proccess));
// * Accessed via ?page=teleporter

return $teleporter->process();
?>
