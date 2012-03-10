<?php
/************************************************************************
*													engine/installer/install.php
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

if (!defined('INSTALL_AXE')) die();

/*******************************************************************************
*				PRELIMINARY LOADING
*******************************************************************************/

@session_start();

// Include common functions
include PATHROOT."engine/func/required.php";

/*******************************************************************************
*				LOAD LANGUAGE
*******************************************************************************/

// By default, Web-WoW will run using English
$lang = 'English';

// Do we have a requested language through URL or POST?
$requested_lang = Html::sanitize(
	isset($_GET['lang']) ? $_GET['lang'] :
	(isset($_POST['lang']) ? $_POST['lang'] : $lang)
	);

if (Html::is_valid_lang($requested_lang, "installer")) $lang = $requested_lang;

// Load the language file
include PATHROOT.'engine/lang/' . strtolower($lang) . '/installer.php';

/*******************************************************************************
*				INSTALLER
*******************************************************************************/
restore_error_handler();

/**
* Install
*
* @author AXE (creator), maverfax (debugger)
*/
class Install {

	/**
	* Go
	*
	* @return void
	* @access public
	*
	*/
	function Go() {
		global $lang, $installer_lang;

		// Store data to session
		if ( isset( $_POST ) ) {
			foreach ( $_POST as $a => $a2 )
				$_SESSION['wwcmsv2install'][$a] = $a2;
		}

		// linebreak
		$ln = Html::ln();

		// other vars
		$stop = false;

		if (isset($_SESSION['wwcmsv2install']['core']))
		{
			if ($_SESSION['wwcmsv2install']['core'] == 'ArcEmu') $p_db = array(0=>"accounts",1=>"accounts",2=>"characters",3=>"mailbox_insert_queue");
			elseif ($_SESSION['wwcmsv2install']['core'] == 'MaNGOS') $p_db = array(0=>"account",1=>"account",2=>"characters",3=>"characters");
			elseif ($_SESSION['wwcmsv2install']['core'] == 'Trinity') $p_db = array(0=>"account",1=>"account_access",2=>"characters",3=>"character_inventory");
			elseif ($_SESSION['wwcmsv2install']['core'] == 'Trinitysoap') $p_db = array(0=>"account",1=>"account_access",2=>"characters",3=>"character_inventory");
		}
		else
			$p_db = array(0=>"unknown_core",1=>"unknown_core",2=>"unknown_core",3=>"unknown_core");

		//only letters and numbers
		$step = (isset($_GET['step']) && strlen($_GET['step']) == 1 ? preg_replace( "/[^0-9]/", "", $_GET['step'] ) : '');
		if ($step == '')
		{
			$step = '1';
			$_SESSION['wwcmsv2install'] = array();
		}


		echo '<form action="index.php?step='.($step+1).'&lang='.$lang.'" method="post">';
		if ($step == '1')
		{
			//
			// Language selection
			//
			echo Html::lang_selection('English');
		}
		elseif ($step=='2')
		{
			//
			// Check directory chmod permissions
			//
			echo $installer_lang['Files and Directories'].':';

			$directorys = array('./errorlogs/','./engine/_cache/');
			foreach ($directorys as $dir)
			{
				$chmod = substr(sprintf('%o', fileperms($dir)), -4);
				if ($chmod != '0777')
				{
					if (!@chmod($dir, 0777))
					{
						echo '<div id="tworows"><span style="color:red">'.$installer_lang['Not Writable'].'</span>'.$dir.'</div>';
						$stop = true;
					}
					else
					{
						echo '<div id="tworows"><span style="color:green">'.$installer_lang['Writable'].'</span>'.$dir.'</div>';
					}
				}
				else
				{
					echo '<div id="tworows"><span style="color:green">'.$installer_lang['Writable'].'</span>'.$dir.'</div>';
				}
			}

			//
			// Check file chmod permissions
			//
			$files = array("./config/config.php", "./config/config_db.php");
			foreach ($files as $file)
			{
				if (!is_writable($file))
				{
					if (!file_exists($file))
					{
						// Create File
						if ($fh = fopen($file, "w"))
						{
							fwrite($fh, "<?php" . $ln . "?>");
							fclose($fh);
						}
					}
					// Chmod file
					@chmod($file, 0777);

					// Recheck writability
					if (!is_writable($file))
					{
						echo '<div id="tworows"><span style="color:red">'.$installer_lang['Not Writable'].' ('.$installer_lang['please chmod this file to 777'].')</span>'.$file.'</div>';
						$stop = true;
					}
					else
					{
						echo '<div id="tworows"><span style="color:green">'.$installer_lang['Writable'].'</span>'.$file.'</div>';
					}
				}
				else
				{
					echo '<div id="tworows"><span style="color:green">'.$installer_lang['Writable'].'</span>'.$file.'</div>';
				}
			}

			echo "<br>".$installer_lang['Functions'].":";
			$functions = array("fsockopen");
			foreach ($functions as $func)
			{
				if (function_exists($func))
					echo '<div id="tworows"><span style="color:green">'.ucwords($installer_lang['enabled']).'</span> '.$func.'()</div>';
				else
					echo '<div id="tworows"><span style="color:red">'.ucwords($installer_lang['disabled']).'</span> '.$func.'()</div>';
			}
		}
		elseif ($step=='3')
		{
			echo $installer_lang['WoW Server Core'].":<br>";
			$cores = array("ArcEmu", "MaNGOS", "Trinity");
			echo "<select id='core' name='core'>";
			foreach ($cores as $core)
			{
				echo "<option value='" . $core . "' ";
				if (isset($_SESSION['wwcmsv2install']['core']) && $_SESSION['wwcmsv2install']['core'] == $core) echo "selected = 'selected'";
				echo ">" . $core . "</option>";
			}
			echo "</select>";
		}
		elseif ($step=='4') // Database Connection
		{
			echo "<script src=\"./engine/js/install.js\"></script>";
			echo $installer_lang['Database Type'].":<br>";
			$dbtypes = library::supported_databases();
			echo "<select id='db_type' name='db_type'>";
			foreach ($dbtypes as $dbtype)
			{
				echo "<option value='" . $dbtype . "' ";
				if (isset($_SESSION['wwcmsv2install']['db_type']) && $_SESSION['wwcmsv2install']['db_type'] == $dbtype) echo "selected = 'selected'";
				echo ">" . $dbtype . "</option>";
			}
			echo "</select><br><br>";
			echo $installer_lang['Database Host'].":";
			$this->Input("db_host",'localhost');echo '<br>';
			echo $installer_lang['Database Username'].":";
			$this->Input("db_user",'root');echo '<br>';
			echo $installer_lang['Database Password'].":";
			$this->Input("db_pass");echo '<br>';
			echo "<br><span class='innerlinks'><a href='javascript:void();' onclick='db_con(\"".$installer_lang['Connecting']."\",\"".$installer_lang['Next Step']."\",\"".$installer_lang['Connection Failed']."\",\"".$installer_lang['Connection Successful']."\");return false'>".$installer_lang['Click Here to Test Connection']."</a></span>";
			echo "<span id='db_con' style='display:none'></span>";
			$stop = true;
		}
		elseif ($step=='5')
		{
			echo "<script src=\"./engine/js/install.js\"></script>";
			// Check $_SESSION values
			$db_host = isset($_SESSION['wwcmsv2install']['db_host']) ? $_SESSION['wwcmsv2install']['db_host'] : "";
			$db_user = isset($_SESSION['wwcmsv2install']['db_user']) ? $_SESSION['wwcmsv2install']['db_user'] : "";
			$db_pass = isset($_SESSION['wwcmsv2install']['db_pass']) ? $_SESSION['wwcmsv2install']['db_pass'] : "";
			$db_type = isset($_SESSION['wwcmsv2install']['db_type']) ? $_SESSION['wwcmsv2install']['db_type'] : "";
			if ($db_host && $db_user && library::supported_databases($db_type))
			{
				library::create_dblink($connect, $db_type);
				if ($connect->init($db_host, $db_user, $db_pass))
				{
					// Get all database names and store into $databases
					$dbquery = $connect->query("SHOW DATABASES");
					$databases = $connect->getArray();

					#
					# ACC DB DETECTION:
					#
					echo '<div id="tworows"><span style="margin-left:80px; font-weight:normal">'.$installer_lang['Accounts database'].':<br>';
					$i = 0;

					foreach ($databases as $database)
					{
						if ($this->checkTable($connect, $database['Database'].'.'.$p_db[0]) && $this->checkTable($connect, $database['Database'].'.'.$p_db[1]))
						{
							$i++;
							$curr_db = $database['Database'];
						}
					}

					$this->Input("logon_db", (isset($curr_db) ? $curr_db : ''));

					echo '<small style=" font-size:10px; color:gray">('.$installer_lang['Compatible Database is Autodetected'].', '.$i.' '.$installer_lang['found'].')</small>';
					echo '</span><img src="engine/installer/res/db.png" alt="db.png"></div><br>';

					//
					// REALM DATABASE X
					//
					#this is RA or SOAP info:
					echo '<div id="tworows2"><div id="tworows3">';
					/*print soap and ra input forms:*/
					if (isset($_SESSION['wwcmsv2install']['core']))
					{
						if ($_SESSION['wwcmsv2install']['core']=='Trinity'){
							echo $installer_lang['Mail sending'].':<br>';
							$this->Input("char_rasoap_user",'"',"&nbsp;&nbsp;&nbsp;".$installer_lang['Remote Access User'].": ",' ('.$installer_lang['required'].')','char_rasoap_user'.$i);
							$this->Input("char_rasoap_pass",'',"&nbsp;&nbsp;&nbsp;".$installer_lang['Remote Access Pass'].": ",' ('.$installer_lang['required'].')','char_rasoap_pass'.$i);
						}
						else if ($_SESSION['wwcmsv2install']['core']=='MaNGOS' or $_SESSION['wwcmsv2install']['core']=='Trinitysoap'){
							echo $installer_lang['Mail sending'].':<br>';
							$this->Input("char_rasoap_user",'',"&nbsp;&nbsp;&nbsp;".$installer_lang['SOAP User'].": ",' ('.$installer_lang['required'].')','char_rasoap_user'.$i);
							$this->Input("char_rasoap_pass",'',"&nbsp;&nbsp;&nbsp;".$installer_lang['SOAP Pass'].": ",' ('.$installer_lang['required'].')','char_rasoap_pass'.$i);
						}
					}

					#
					#REALM DB DETECTION:
					#
					echo "<br>".$installer_lang['Realm database(s)'].':<br>';
					#some vars:
					$j=1;
					echo '<div id="addmore"><script type="text/javascript">';
					echo "core = '" . $_SESSION['wwcmsv2install']['core'] . "';";
					echo "lang = " . json_encode($installer_lang).";";

					foreach ($databases as $database)
					{
						if ($this->checkTable($connect, $database['Database'].'.'.$p_db[2]) && $this->checkTable($connect, $database['Database'].'.'.$p_db[3]))
						{
							echo "addfromdb('".$database['Database']."', '3306');";
							$j++;
						}
					}

					echo '</script><a id="addmorebtn" href="javascript:void();" onclick="javascript:addmore();return false;">[+'.$installer_lang['add more'].']</a></div>';
					echo '<small style=" font-size:10px; color:gray">('.$installer_lang['Compatible Database is Autodetected'].', '.($j-1).' '.$installer_lang['found'].')</small>';
					echo '</div></div><br>';

					#
					#WEBSITE DB DETECTION:
					#
					echo '<div id="tworows2" ><div id="tworows3">'.$installer_lang['Website database'].':<br>';
					#some vars:
					$j = 0;$curr_db = false;

					#do loop:
					foreach ($databases as $database)
					{
						if ($this->checkForEmptyDB($connect, $database['Database']))
						{
							$j++;
							$curr_db = $database['Database'];
						}
					}
					$this->Input("web_db", $curr_db, false, '<small> '.$installer_lang['If DB does not exists, it will be created'] . '</small>');
					$connect->close();

					echo '<small style="font-size:10px; color:gray">('.$installer_lang['Compatible Database is Autodetected'].', '.$j.' '.$installer_lang['found'].')</small>';
					echo '</div></div><br><br>';
				}
				else
				{
					echo $installer_lang['Connection Failed'].' ('.$connect->getLastError().')';
					$stop = true;
				}
			}
			else
			{
				echo $installer_lang['Connection Failed'].' (host/user/database type)';
				$stop = true;
			}
		}
		elseif ($step=='6')
		{
			if ($_SESSION['wwcmsv2install']['web_db']=='')
			{
				echo $installer_lang['You did not enter website database, please go step back.'];
				echo "</form>";
				return;
			}

			echo "<script src=\"./engine/js/install.js\"></script>";
			echo '<script type="text/javascript">';
			echo "lang = " . json_encode($installer_lang).";";
			echo '</script>';
			echo "<br><span class='innerlinks' id='db_install'><br><br><a href='#' onclick='db_install(\"".$installer_lang['Connecting']."\");$(\"#db_install\").hide();return false'>".$installer_lang['Click Here to Install Database']."</a></span>";
			echo '<br><div id="db_process"></div>';
			$stop = true;
		}
		elseif ($step=='7')
		{
			// Check $_SESSION values
			$db_host = isset($_SESSION['wwcmsv2install']['db_host']) ? $_SESSION['wwcmsv2install']['db_host'] : "";
			$db_user = isset($_SESSION['wwcmsv2install']['db_user']) ? $_SESSION['wwcmsv2install']['db_user'] : "";
			$db_pass = isset($_SESSION['wwcmsv2install']['db_pass']) ? $_SESSION['wwcmsv2install']['db_pass'] : "";
			$db_type = isset($_SESSION['wwcmsv2install']['db_type']) ? $_SESSION['wwcmsv2install']['db_type'] : "";
			if ($db_host && $db_user && library::supported_databases($db_type))
			{
				library::create_dblink($connect, $db_type);
				if ($connect->init($db_host, $db_user, $db_pass))
				{
					$jsonLang = json_encode($installer_lang);
					echo <<<EOB
					<script src="./engine/js/install.js"></script>
					<script type="text/javascript">
					lang = {$jsonLang};
					</script>

					<input name="host" id="host" type="hidden" value="{$db_host}">
					<input name="user" id="user" type="hidden" value="{$db_user}">
					<input name="pass" id="pass" type="hidden" value="{$db_pass}">
EOB;
					echo $installer_lang['Admin Username'].':';
					$this->Input("admin_username",'');
					echo '<br>';
					echo $installer_lang['Admin Password'].':';
					$this->Input("admin_password",'');
					echo '<br>';
					echo "<br><span id='checkadmin'><input type='button' onclick='checkadmin();return false' value='".$installer_lang['Save']."'><br></span>";
					$stop = true;
				}
			}
		}
		elseif ($step=='8')
		{
			$db_host = isset($_SESSION['wwcmsv2install']['db_host']) ? $_SESSION['wwcmsv2install']['db_host'] : "localhost";
			$db_user = isset($_SESSION['wwcmsv2install']['db_user']) ? $_SESSION['wwcmsv2install']['db_user'] : "";
			$db_pass = isset($_SESSION['wwcmsv2install']['db_pass']) ? $_SESSION['wwcmsv2install']['db_pass'] : "";
			$db_type = isset($_SESSION['wwcmsv2install']['db_type']) ? $_SESSION['wwcmsv2install']['db_type'] : "";
			library::create_dblink($connect, $db_type);
			if ($connect->init($db_host, $db_user, $db_pass))
			{
				$string = "<?php" . $ln. '$config=array(' . $ln;
				$result = $connect->query("SELECT * FROM ".$_SESSION['wwcmsv2install']['web_db'].".wwc2_config");
				$array = $connect->getArray();
				foreach ($array as $row)
				{
					$string .= "'".$row['conf_name']."' => '".$row['conf_value']."'," . $ln;
				}

				$string .= ");" . $ln . $ln . "define('AXE',1);" . $ln . $ln;
				$this->writefile($string,'./config/config.php');
				echo "<br><br>";
				$string = "<?php" . $ln. '$db_host="'.$_SESSION['wwcmsv2install']['db_host'].'";' . $ln;
				$string .= '$db_user="'.$_SESSION['wwcmsv2install']['db_user'].'";' . $ln;
				$string .= '$db_pass="'.$_SESSION['wwcmsv2install']['db_pass'].'";' . $ln;
				$string .= "define('AXE_db',1);" . $ln . $ln;
				$this->writefile($string,'./config/config_db.php');
				$connect->close();
			}
			else
			{
				echo $installer_lang['Go to']." '".$installer_lang['Database Connection']."'.";
				$stop=true;
			}
		}
		elseif ($step=='9')
		{
			echo "Whoops, we are sorry but script could not create following files: config/config.php and config/config_db.php<br>probably due CHMOD file premissions, try using your ftp program and chmod them to be writtable, also if you are on windowsnavigate to www/config/ folder, right click propreties, uncheck 'Read Only'. If files does not exists, create two empty files(config.php and config_db.php).<br><br>Click on last step on the left.";
			return;
		}

		if ($stop) { echo "</form>"; return; }
		if ($step=='8')
		{
			echo '<br><br><input name="next" type="submit" value="'.$installer_lang['Start using the site'].'"></form>';
			return;
		}
		echo '<br><br><input name="next" type="submit" value="'.$installer_lang['Next Step'].' ('.$step.'/8)"></form>';
	}

	// --------------------------------------------------------------------

	/**
	* Write File
	*
	* @access	public
	* @param	string
	* @param	string
	* @return	void
	*/
	function writefile($string,$file) {
		global $lang,$installer_lang;
		filehandler::write($file, $string);

		echo $file . ' <font color=\'green\'><b>' . $installer_lang['written successfully']. '</b></font>';

		/**
		*We will leave this file writtable becouse administrator will want to recache config.php
		*but we will chmod file config_db.php becouse it no longer needs changing.
		**/
		if (preg_match("/config_db.php/",$file))
		{
			filehandler::checkpermission($file, 0644);

			if (is_writable($file))
			{
				echo '<br>' . $installer_lang['We suggest that you CHMOD'] . ' <b>' . $file . '</b> ' . $installer_lang['to'] . ' 0664.';
			}
		}
	}


	// --------------------------------------------------------------------

	/**
	* Check table
	*
	* @access public
	* @param string
	* @return boolean
	*/
	function checkTable(&$con, $table) {
		$result = $con->query("SELECT * FROM $table");

		// I could just cast this, but I feel as if this is safer approach
		return (!$result) ? FALSE : TRUE;
	}

	// --------------------------------------------------------------------

	/**
	* Check For Empty Database
	*
	* @access public
	* @param string
	* @return boolean
	*/
	function checkForEmptyDB(&$con, $database) {
		$query = 'SELECT count(*) TABLES, table_schema ';
		$query .= 'FROM information_schema.TABLES ';
		$query .= 'WHERE table_schema= \'' . $database . '\' ';
		$query .= 'GROUP BY table_schema';

		$result = $con->query($query);
		$result = $con->getRow();

		return ($result == '0') ? TRUE : FALSE;
	}

	// --------------------------------------------------------------------

	/**
	* Input
	*
	* @access public
	* @param string
	* @param string
	* @param string
	* @param string
	* @param string
	* @param string
	* @return void
	*/
	function Input($name, $value=false,$text=false, $text2=false,$id=false, $more=false) {
		if (!$value or $value == '')
		{
			if(isset($_SESSION['wwcmsv2install'][$name]))
			{
				$value = $_SESSION['wwcmsv2install'][$name];
			}
		}

		if ($id == FALSE)
		{
			$id = $name;
		}

		echo '<div>' . $text . '<input name="' . $name . '" id="' . $id . '" type="text" value="' . $value . '" style="width:250px" ' . $more . '>' . $text2 . '</div>';
	}

	// --------------------------------------------------------------------

	/**
	* Tree
	*
	* @access public
	* @param string
	* @return boolean
	*/
	function Tree() {
		global $installer_lang, $lang;

		$current_step = '1';

		if(isset($_GET['step']) && !empty($_GET['step']))
		{
			$current_step = Html::sanitize($_GET['step']);
		}

		$steps = array(
			1 => $installer_lang["Language selection"],
			2 => $installer_lang["Requirements"],
			3 => $installer_lang["Server Core"],
			4 => $installer_lang["Database Connection"],
			5 => $installer_lang["Database Setup"],
			6 => $installer_lang["Import to DB"],
			7 => $installer_lang["Admin"],
			8 => $installer_lang["Generate Configs"]
			);

		$i = '1';
		$color = 'black';

		foreach ($steps as $step)
		{
			if ($current_step == $i)
			{
				echo '<strong><a href="index.php?step=' . $i . '&lang=' . $lang . '">' . $step . '</a></strong><br>';
				$color = 'gray';
			}

			else
			{
				echo '<font color='.$color.'><a href="index.php?step='.$i.'&lang='.$lang.'">'.$step.'</a></font><br>';
			}

			$i++;
		}
	}

	// --------------------------------------------------------------------
}

$Install = new Install;
$title = $installer_lang["WWC v2 Installer"] . (isset($_GET['step']) && strlen($_GET['step']) == 1 ? ' - Step '.preg_replace( "/[^0-9]/", "", $_GET['step'] ) : '');
?>

<html>
	<head>
		<title><?php echo $title;?></title>
		<meta http-equiv = "Content-Type" content = "text/html;charset=utf-8" />
		<meta name="Description" content="OpenWoW CMS v2 - <?php echo $title;?>" />
		<link href = "./engine/installer/res/style.css" rel = "stylesheet" type = "text/css"/>
		<script type="text/javascript" src="./engine/js/jquery-1.7.1.min.js"></script>
		<script type="text/javascript" src="./engine/js/jquery-ui-1.8.min.js"></script>
	</head>

	<body>
		<div id = "container">
			<div id = "header">
				<table width="100%" height="100px" cellpadding="0" cellspacing="0" border="0" >
					<tr>
						<td width="200px" valign="top">
							<h1><img src="engine/installer/res/logo.png" alt="logo.png"><span><strong><?php echo $installer_lang["WebWoW CMS v2 Install Script"]; ?></strong></span></h1>
						</td>
						<td><div id = "footer">OpenWoW CMS v2 &copy; 2012<br/>Powered by <a href = "http://www.openwow.net" title="OpenWoW CMS">OpenWoW</a></div></td>
					</tr>
				</table>
			</div>
			<div id = "content"><br/>
				<table width="100%" height="97%" border="0" >
					<tr>
						<td width="200px" id="listmenu" valign="top">
<?php
$Install->Tree();
echo '<br><i>'.$installer_lang["Overview"].':</i><br><textarea style="height:300px">';
if (isset($_SESSION['wwcmsv2install']))
{
	foreach ($_SESSION['wwcmsv2install'] as $key => $storeddata)
	{
		if (!isset($noparsemore) || $noparsemore == FALSE)
		{
			if ($key<>'next')//dont show next buttons
			{
				if ($key=='char_db')
				{
					echo 'realm_databases = '.Html::ln();
					foreach ($_SESSION['wwcmsv2install']['char_db'] as $key2=>$sess_chardb)
					{
						if($sess_chardb=='')
							unset($_SESSION['wwcmsv2install']['char_db'][$key2]);
						else
							echo ' '.$sess_chardb.Html::ln();
						$noparsemore=true;
					}
				}
				else
					echo $key.' = '.htmlspecialchars(trim($storeddata)). Html::ln(). '------------------'. Html::ln();
			}
		}

	}
}

if (isset($_SESSION['wwcmsv2install']['web_db']) && trim($_SESSION['wwcmsv2install']['web_db'])<>'')
	echo '------------------'. Html::ln().'web_db = '.htmlspecialchars(trim($_SESSION['wwcmsv2install']['web_db']));
echo "</textarea>";
?>
						</td>
						<td style="padding-left:28px" valign="top">
<?php
$Install->Go();
?>
						</td>
					</tr>
				</table>
			</div>
		</div>
	</body>
</html>
<?php
set_error_handler("errorhandler::error", -1);
exit;
?>