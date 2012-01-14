<?php
###################################################################
# This file is a part of OpenWoW CMS by www.openwow.com
#
#   Project Owner    : OpenWoW CMS (http://www.openwow.com)
#   Copyright        : (c) www.openwow.com, 2010
#   Credits          : Based on work done by AXE and Maverfax
#   License          : GPLv3
##################################################################


if (!defined('INSTALL_AXE')) die();

error_reporting(0);

/*******************************************************************************
*                              PRELIMINARY LOADING
*******************************************************************************/

@session_start();

// Include common functions
require_once ("./engine/func/required.php");

/**
* Removes all characters that are not alphabetical nor numerical
*
* @param string
* @return string
*/
function sanitize($string = '')
{
return preg_replace('/[^a-zA-Z0-9]/', '', $string);
}

/*******************************************************************************
*                               LOAD LANGUAGE
*******************************************************************************/

/**
* Determines wether a language is valid or not
*
* @param string
* @return boolean
*/
function is_valid_lang($language = '')
{
if(!empty($language))
{
if(file_exists('./engine/lang/' . $language . '/installer.php'))
{
return TRUE;
}
}

return FALSE;
}

// By default, Web-WoW will run using English
$lang = 'English';

// Do we have a requested language through the URL?
if(isset($_GET['lang']))
{
$requested_lang = sanitize($_GET['lang']);

if (is_valid_lang($requested_lang))
{
$lang = $requested_lang;
}
}

// Do we have a requested language through a post?
if(isset($_POST['lang']))
{
$requested_lang = sanitize($_POST['lang']);

if (is_valid_lang($requested_lang))
{
$lang = $requested_lang;
}
}

// Load the language file
require ('./engine/lang/' . strtolower($lang) . '/installer.php');

/*******************************************************************************
*                               INSTALLER
*******************************************************************************/

/**
* Install
*
* @package		Web-WoW
* @author		AXE (creator), maverfax (debugger)
*/
class Install {

/**
* Line
*
* @access	public
* @return	string
*/
function ln() #returns
{
$return = "\n";

if(isset($_SERVER['OS']))
{
$system = strtolower( $_SERVER['OS'] );

if (strstr( $system, 'windows'))
{
$return = "\r\n";
}

else
{
if(strstr($system, 'mac'))
{
$return = "\r";
}
}
}

return $return;
}

// --------------------------------------------------------------------

/**
* Go
*
* @access	public
* @return	void
*/
function Go()
{
global $Html, $lang, $installer_lang;

# Store data to session
if ( isset( $_POST ) ) {
foreach ( $_POST as $a => $a2 )
$_SESSION['wwcmsv2install'][$a] = $a2;
}

# linebreak
$ln = $this->ln();

#other vars
$stop=false;

if(isset($_SESSION['wwcmsv2install']['core']))
{
if ($_SESSION['wwcmsv2install']['core']=='ArcEmu') $p_db=array(0=>"accounts",1=>"accounts",2=>"characters",3=>"mailbox_insert_queue");
elseif($_SESSION['wwcmsv2install']['core']=='MaNGOS') $p_db=array(0=>"account",1=>"account",2=>"characters",3=>"characters");
elseif($_SESSION['wwcmsv2install']['core']=='Trinity') $p_db=array(0=>"account",1=>"account_access",2=>"characters",3=>"character_inventory");
elseif($_SESSION['wwcmsv2install']['core']=='Trinitysoap') $p_db=array(0=>"account",1=>"account_access",2=>"characters",3=>"character_inventory");
}
else
$p_db=array(0=>"unknown_core",1=>"unknown_core",2=>"unknown_core",3=>"unknown_core");

//
//
//
$step = '';
if(isset($_GET['step']))
{
$step = preg_replace( "/[^0-9]/", "", $_GET['step'] ); //only letters and numbers
}

if ($step == '')
{
$step='1';
$_SESSION['wwcmsv2install'] = array();
}


echo '<form action="./?step='.($step+1).'&lang='.$lang.'" method="post">';
if ($step=='1' or $step=='')
{
//
// Language selection
//
echo $Html->lang_selection('English');
}
elseif ($step=='2')
{
//
// Check for file chmod premissions etc
//

echo $installer_lang['Files and Directories'].':';


$chmod = substr( sprintf( '%o', fileperms( "./engine/_cache/" ) ), -4 );
if ($chmod != '0777')//not chmodded
{
//trying to chmod _cache:
if ( !@chmod( "./engine/_cache/", 0777 ) )
{
echo '<div id="tworows"><span style="color:red">'.$installer_lang['Not Writable'].'</span>./engine/_cache/</div>';
}
else
echo '<div id="tworows"><span style="color:green">'.$installer_lang['Writable'].'</span>./engine/_cache/</div>';
}
else
echo '<div id="tworows"><span style="color:green">'.$installer_lang['Writable'].'</span>./engine/_cache/</div>';

//trying to chmod config.php:
if ( !is_writable( "./config/config.php" ) )
{
if ( !file_exists("./config/config.php" ) )
{
$fh = fopen( "./config/config.php", "w" );
fwrite( $fh, '<?php'.$ln.'?>');
fclose( $fh );
@chmod( "./config/config.php", 0777 );

}
if ( !is_writable( "./config/config.php" ) )
{
echo '<div id="tworows"><span style="color:red">'.$installer_lang['Not Writable'].' ('.$installer_lang['please chmod this file to 777'].')</span>./config/config.php</div>';
$stop=true;
}
else
{
echo '<div id="tworows"><span style="color:green">'.$installer_lang['Writable'].'</span>./config/config.php</div>';
}
}
else
echo '<div id="tworows"><span style="color:green">'.$installer_lang['Writable'].'</span>./config/config.php</div>';

//trying to chmod config_db.php:
if ( !is_writable( "./config/config_db.php" ) )
{
if ( !file_exists("./config/config_db.php" ) )
{
$fh = fopen( "./config/config_db.php", "w" );
fwrite( $fh, '<?php'.$ln.'?>');
fclose( $fh );
@chmod( "./config/config_db.php", 0777 );

}
if ( !is_writable( "./config/config_db.php" ) )
{
echo '<div id="tworows"><span style="color:red">'.$installer_lang['Not Writable'].' ('.$installer_lang['please chmod this file to 777'].')</span>./config/config_db.php test</div>';
$stop=true;
}
else
{
echo '<div id="tworows"><span style="color:green">'.$installer_lang['Writable'].'</span>./config/config_db.php</div>';
}
}
else
echo '<div id="tworows"><span style="color:green">'.$installer_lang['Writable'].'</span>./config/config_db.php</div>';

echo "<br>".$installer_lang['Functions'].":";
if(function_exists("fsockopen"))
echo '<div id="tworows"><span style="color:green">'.ucwords($installer_lang['enabled']).'</span> fshockopen()</div>';
else
echo '<div id="tworows"><span style="color:red">'.ucwords($installer_lang['disabled']).'</span> fshockopen()</div>';

}
elseif ($step=='3')
{
if(!isset($_SESSION['wwcmsv2install']['core']))
{
$_SESSION['wwcmsv2install']['core'] = 'ArcEmu';
}

echo $installer_lang['WoW Server Core'].":<br>";
echo "<select id='core' name='core'>
<option value='ArcEmu'";
if ($_SESSION['wwcmsv2install']['core']=='ArcEmu') echo "selected='selected'";
echo ">ArcEmu</option>
<option value='MaNGOS'";
if ($_SESSION['wwcmsv2install']['core']=='MaNGOS') echo "selected='selected'";
echo ">MaNGOS</option>
<option value='Trinity'";
if ($_SESSION['wwcmsv2install']['core']=='Trinity') echo "selected='selected'";
echo ">Trinity</option>";
//echo "	<option value='Trinitysoap'";
//if ($_SESSION['wwcmsv2install']['core']=='Trinitysoap') echo "selected='selected'";
//echo ">Trinity (SOAP) &ge; 3.3.5a</option>";

echo "</select>";
}
elseif ($step=='4')
{
//
// Database connection
//
?>
<script type="text/javascript">
function db_con()
{
var host = document.getElementById("db_host").value;
var user = document.getElementById("db_user").value;
var pass = document.getElementById("db_pass").value;
$('#db_con').fadeIn('slow', function() {});
document.getElementById("db_con").innerHTML="<?php echo $installer_lang['Connecting']; ?>...";
$.post("./engine/installer/dynamic/db_con.php?l=<?php echo $installer_lang['Next Step']; ?>&f=<?php echo $installer_lang['Connection Failed']; ?>&s=<?php echo $installer_lang['Connection Successful']; ?>", {host:host, user: user,pass: pass },function(data)
{
document.getElementById("db_con").innerHTML="" + data;
}
);
}
</script>
<?php
echo $installer_lang['Database Host'].":";
$this->Input("db_host",'localhost');echo '<br>';
echo $installer_lang['Database Username'].":";
$this->Input("db_user",'root');echo '<br>';
echo $installer_lang['Database Password'].":";
$this->Input("db_pass");echo '<br>';
echo "<br><span class='innerlinks'><a href='#' onclick='javascript:db_con();return false' >".$installer_lang['Click Here to Test Connection']."</a></span><span id='db_con'></span>";
$stop=true;

}
elseif ($step=='5')
{
?>
<script type="text/javascript">
function pastetext(text,where)
{
document.getElementById(where).value=text;
document.getElementById('charcontent'+where).style.display="none";

}
function addmore(id)
{
id2=id+1;
document.getElementById('addmore'+id).innerHTML='<b><?php echo $installer_lang['No.']; ?>'+id+'</b> <input name="char_db[]" id="char_db'+id+'" value="" style="width: 250px;" type="text" onkeypress="javascript:document.getElementById(\'charcontentchar_db'+id+'\').style.display=\'block\'"> <a href="#" onclick="javascript:pastetext(\'\',\'char_db'+id+'\')">[-<?php echo $installer_lang['remove']; ?>]</a><div id="charcontentchar_db'+id+'">&nbsp;&nbsp;&nbsp;<strong><?php echo $installer_lang['Port']; ?></strong>: <input name="char_port[]" id="char_port'+id+'" value="1234" style="width: 250px;" type="text"> (<?php echo $installer_lang['required']; ?>)<br>&nbsp;&nbsp;&nbsp;<?php echo $installer_lang['Host']; ?>: <input name="char_host[]" id="char_host'+id+'" value="" style="width: 250px;" type="text"> (<?php echo $installer_lang['optional']; ?>)<br>&nbsp;&nbsp;&nbsp;<?php echo $installer_lang['DB user']; ?>: <input name="char_dbuser[]" id="char_dbuser'+id+'" value="" style="width: 250px;" type="text"> (<?php echo $installer_lang['optional']; ?>)<br>&nbsp;&nbsp;&nbsp;<?php echo $installer_lang['DB pass']; ?>: <input name="char_dbpass[]" id="char_dbpass'+id+'" value="" style="width: 250px;" type="text"> (<?php echo $installer_lang['optional']; ?>)<br>&nbsp;&nbsp;&nbsp;<?php

if ($_SESSION['wwcmsv2install']['core']=='Trinity'){
echo $installer_lang['Remote Access Port']; ?>: <input name="char_rasoap[]" id="char_rasoap'+id+'" value="" style="width: 250px;" type="text"> (<?php echo $installer_lang['optional']; ?>)<br>&nbsp;&nbsp;&nbsp;<?php

}
else if ($_SESSION['wwcmsv2install']['core']=='MaNGOS' or $_SESSION['wwcmsv2install']['core']=='Trinitysoap'){
echo $installer_lang['SOAP Port']; ?>: <input name="char_rasoap[]" id="char_rasoap'+id+'" value="" style="width: 250px;" type="text"> (<?php echo $installer_lang['optional']; ?>)<br>&nbsp;&nbsp;&nbsp;<?php

}


?><strong><?php echo $installer_lang['Name']; ?></strong>: <input name="char_names[]" id="char_names'+id+'" value="<?php echo $installer_lang['Realm'].' '.$installer_lang['Name']; ?>" style="width: 250px;" type="text"> (<?php echo $installer_lang['required']; ?>)</div><div id="addmore'+id2+'"><a href="#" onclick="javascript:addmore('+id2+');return false;">[+<?php echo $installer_lang['add more']; ?>]</a>';
}
</script>
<?php
$connected=true;
$connect = @mysql_connect($_SESSION['wwcmsv2install']['db_host'], $_SESSION['wwcmsv2install']['db_user'], $_SESSION['wwcmsv2install']['db_pass']) or $connected=false;
if ($connected)
{
//
// accounts database
//
echo '<div id="tworows"><span style="margin-left:80px; font-weight:normal">'.$installer_lang['Accounts database'].':<br>';

#
#ACC DB DETECTION:
#
$dbquery = mysql_query("SHOW DATABASES");

#some vars:
$i = 0;$j=0;

#do loop:
while ($row = mysql_fetch_assoc($dbquery)) {
$arr[$i] = $row['Database'];

if ($this->checkTable($arr[$i].'.'.$p_db[0]) && $this->checkTable($arr[$i].'.'.$p_db[1]))
{

$j++;$curr_db=$arr[$i];

}
$i++;
}
$this->Input("logon_db",(isset($curr_db) ? $curr_db : ''));



#
#
#
echo '<small style=" font-size:10px; color:gray">('.$installer_lang['Compatible Database is Autodetected'].', '.$j.' '.$installer_lang['found'].')</small>';

echo '
</span><img src="engine/installer/res/db.png"></div><br>';



//
// REALM DATABASE X
//
#this is RA or SOAP info:
echo '<div id="tworows2"><div id="tworows3">
';
/*print soap and ra input forms:*/
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

echo $installer_lang['Realm database(s)'].':<br>';
#
#REALM DB DETECTION:
#
$dbquery = mysql_query("SHOW DATABASES");

#some vars:
$i = 0;$j=1;$curr_db=false;

#do loop:
while ($row = mysql_fetch_assoc($dbquery)) {
$arr[$i] = $row['Database'];


if ($this->checkTable($arr[$i].'.'.$p_db[2]) && $this->checkTable($arr[$i].'.'.$p_db[3]))
{

$curr_db=$arr[$i];
$this->Input("char_db[]",$curr_db.'',"<strong>".$installer_lang['No.'].$j."</strong> ",' <a href="#" onclick="javascript:pastetext(\'\',\'char_db'.$i.'\')">[-'.$installer_lang['remove'].']</a>','char_db'.$i,'onkeydown="javascript:document.getElementById(\'charcontentchar_db'.$i.'\').style.display=\'block\';"');
echo "<div id='charcontentchar_db".$i."'>";
$this->Input("char_port[]",'1234',"&nbsp;&nbsp;&nbsp;<strong>".$installer_lang['Port']."</strong>: ",' ('.$installer_lang['required'].')','char_port'.$i);
$this->Input("char_host[]",'',"&nbsp;&nbsp;&nbsp;".$installer_lang['Host'].": ",' ('.$installer_lang['optional'].')','char_host'.$i);
$this->Input("char_dbuser[]",'',"&nbsp;&nbsp;&nbsp;".$installer_lang['DB user'].": ",' ('.$installer_lang['optional'].')','char_dbuser'.$i);
$this->Input("char_dbpass[]",'',"&nbsp;&nbsp;&nbsp;".$installer_lang['DB pass'].": ",' ('.$installer_lang['optional'].')','char_dbpass'.$i);
if ($_SESSION['wwcmsv2install']['core']=='Trinity'){
$this->Input("char_rasoap[]",'',"&nbsp;&nbsp;&nbsp;".$installer_lang['Remote Access Port'].": ",' ('.$installer_lang['required'].')','char_rasoap'.$i);

}
else if ($_SESSION['wwcmsv2install']['core']=='MaNGOS' or $_SESSION['wwcmsv2install']['core']=='Trinitysoap'){

$this->Input("char_rasoap[]",'',"&nbsp;&nbsp;&nbsp;".$installer_lang['SOAP Port'].": ",' ('.$installer_lang['required'].')','char_rasoap'.$i);

}
$this->Input("char_names[]",'',"&nbsp;&nbsp;&nbsp;<strong>".$installer_lang['Name']."</strong>: ",' ('.$installer_lang['required'].')','char_names'.$i);

echo "</div>";
$j++;

}
$i++;
}
//if ($j=='1')
//$this->Input("char_db[]",$curr_db);
echo '<div id="addmore'.$j.'"><a href="#" onclick="javascript:addmore('.$j.');return false;">[+'.$installer_lang['add more'].']</a></div>';

#
#
#
echo '<small style=" font-size:10px; color:gray">('.$installer_lang['Compatible Database is Autodetected'].', '.($j-1).' '.$installer_lang['found'].')</small>';

echo '</div></div><br>';




//
//  WEBSITE DATABASE
//
echo '<div id="tworows2" ><div id="tworows3">'.$installer_lang['Website database'].':<br>
';
#
#EMPTY DB DETECTION:
#
$dbquery = mysql_query("SHOW DATABASES");

#some vars:
$i = 0;$j=0;$curr_db=false;

#do loop:
while ($row = mysql_fetch_assoc($dbquery)) {
$arr[$i] = $row['Database'];


if ($this->checkForEmptyDB($arr[$i]))
{
$j++;$curr_db=$arr[$i];
//echo $arr[$i];

}
$i++;
}
$this->Input("web_db",$curr_db,false,' '.$installer_lang['If DB does not exists, it will be created']);



#
#
#
echo '<small style="font-size:10px; color:gray">('.$installer_lang['Compatible Database is Autodetected'].', '.$j.' '.$installer_lang['found'].')</small>';

echo '
</div></div><br><br>';

mysql_close( $connect );
}
else
{
echo $installer_lang['Connection Failed'].' ('.mysql_error().')';
$stop=true;
}
}
elseif ($step=='6')
{
if ($_SESSION['wwcmsv2install']['web_db']=='')
{
echo $installer_lang['You did not enter website database, please go step back.'];return;

}
//
// Create db, add tables and write config.php
//
$connected=true;
$connect = @mysql_connect($_SESSION['wwcmsv2install']['db_host'], $_SESSION['wwcmsv2install']['db_user'], $_SESSION['wwcmsv2install']['db_pass']) or $connected=false;
if ($connected)
{
mysql_query("create database ".$_SESSION['wwcmsv2install']['web_db'] );
mysql_select_db($_SESSION['wwcmsv2install']['web_db']) or die("Website database error: ".mysql_error());
mysql_query( "SET AUTOCOMMIT=0" );
mysql_query( "START TRANSACTION" );
mysql_query( "DROP TABLE IF EXISTS `wwc2_active_users`");
echo $installer_lang['Delete']." wwc2_active_users<br>";
mysql_query( "DROP TABLE IF EXISTS `wwc2_banned_users`");
echo $installer_lang['Delete']." wwc2_banned_users<br>";
mysql_query( "DROP TABLE IF EXISTS `wwc2_active_guests`");
echo $installer_lang['Delete']." wwc2_active_guests<br>";
mysql_query( "DROP TABLE IF EXISTS `wwc2_users_more`");
echo $installer_lang['Delete']." wwc2_users_more<br>";
mysql_query( "DROP TABLE IF EXISTS `wwc2_config`");
echo $installer_lang['Delete']." wwc2_config<br>";
mysql_query( "DROP TABLE IF EXISTS `wwc2_links`");
echo $installer_lang['Delete']." wwc2_links<br>";
mysql_query( "DROP TABLE IF EXISTS `wwc2_accounts`");
echo $installer_lang['Delete']." wwc2_accounts<br>";
mysql_query( "DROP TABLE IF EXISTS `wwc2_news`");
echo $installer_lang['Delete']." wwc2_news<br>";
mysql_query( "DROP TABLE IF EXISTS `wwc2_vote_data`");
echo $installer_lang['Delete']." wwc2_vote_data<br>";
mysql_query( "DROP TABLE IF EXISTS `wwc2_news_c`");
echo $installer_lang['Delete']." wwc2_news_c<br>";
mysql_query( "DROP TABLE IF EXISTS `wwc2_template`");
echo $installer_lang['Delete']." wwc2_template<br><br>";

$query7 = mysql_query(
"CREATE TABLE `wwc2_active_guests` (
`ip` varchar(15) NOT NULL,
`timestamp` int(11) unsigned NOT NULL,
PRIMARY KEY (`ip`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;");
echo $installer_lang['Create']." wwc2_active_guests<br>";
$query8 = mysql_query("CREATE TABLE `wwc2_active_users` (
`username` varchar(30) NOT NULL,
`timestamp` int(11) unsigned NOT NULL,
PRIMARY KEY (`username`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;");
echo $installer_lang['Create']." wwc2_active_users<br>";
$query9 = mysql_query("CREATE TABLE `wwc2_banned_users` (
`username` varchar(30) NOT NULL,
`timestamp` int(11) unsigned NOT NULL,
PRIMARY KEY (`username`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;");
echo $installer_lang['Create']." wwc2_banned_users<br>";
$query2 = mysql_query(
"CREATE TABLE `wwc2_config` (
`conf_name` varchar(255) COLLATE latin1_general_ci NOT NULL DEFAULT '',
`conf_value` text COLLATE latin1_general_ci,
`conf_descr` text COLLATE latin1_general_ci,
`conf_stickied` int(1) NOT NULL DEFAULT '0',
`conf_dropdown` text COLLATE latin1_general_ci,
PRIMARY KEY (`conf_name`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;"
);
echo $installer_lang['Create']." wwc2_config<br>";
$query10 = mysql_query("CREATE TABLE `wwc2_links` (
`id` int(10) NOT NULL AUTO_INCREMENT,
`linktitle` varchar(255) NOT NULL DEFAULT 'notitle',
`linkurl` varchar(255) NOT NULL DEFAULT 'http://',
`linkdescr` varchar(255) DEFAULT '',
`linkgrup` varchar(100) NOT NULL DEFAULT '0',
`linkorder` int(11) NOT NULL DEFAULT '0',
`linkprems` int(10) NOT NULL DEFAULT '0',
PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=441 DEFAULT CHARSET=latin1;");
echo $installer_lang['Create']." wwc2_links<br>";
$query3 = mysql_query(
"CREATE TABLE `wwc2_news` (
`id` bigint(20) NOT NULL AUTO_INCREMENT,
`title` varchar(255) COLLATE latin1_general_ci NOT NULL,
`content` longtext COLLATE latin1_general_ci NOT NULL,
`iconid` int(11) NOT NULL DEFAULT '0',
`timepost` varchar(100) COLLATE latin1_general_ci NOT NULL,
`stickied` int(1) NOT NULL DEFAULT '0' COMMENT '0 or 1',
`hidden` int(1) NOT NULL DEFAULT '0' COMMENT '0 or 1',
`author` varchar(50) COLLATE latin1_general_ci NOT NULL,
PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=88 DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;");
echo $installer_lang['Create']." wwc2_news<br>";
$query4 = mysql_query(
"CREATE TABLE `wwc2_news_c` (
`id` bigint(20) NOT NULL AUTO_INCREMENT,
`poster` varchar(255) COLLATE latin1_general_ci NOT NULL,
`content` text COLLATE latin1_general_ci NOT NULL,
`newsid` int(11) NOT NULL,
`timepost` varchar(100) COLLATE latin1_general_ci NOT NULL,
`datepost` varchar(100) COLLATE latin1_general_ci NOT NULL,
PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=145 DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;");
echo $installer_lang['Create']." wwc2_news_c<br>";
$query6 = mysql_query("CREATE TABLE `wwc2_template` (
`templateid` int(10) unsigned NOT NULL auto_increment,
`styleid` smallint(6) NOT NULL default '0',
`title` varchar(100) NOT NULL default '',
`template` mediumtext,
`template_un` mediumtext,
`templatetype` enum('template','css','other') NOT NULL default 'template',
`dateline` int(10) unsigned NOT NULL default '0',
`username` varchar(100) NOT NULL default '',
`version` varchar(30) NOT NULL default '',
PRIMARY KEY  (`templateid`),
KEY `title` (`title`,`styleid`,`templatetype`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;");
echo $installer_lang['Create']." wwc2_template<br>";
$query1 = mysql_query(
"CREATE TABLE `wwc2_users_more` (
`id` bigint(20) NOT NULL AUTO_INCREMENT,
`acc_login` varchar(55) COLLATE latin1_general_ci NOT NULL,
`vp` bigint(55) NOT NULL DEFAULT '0',
`userid` varchar(32) COLLATE latin1_general_ci DEFAULT NULL,
`question` varchar(100) COLLATE latin1_general_ci DEFAULT NULL,
`answer` varchar(100) COLLATE latin1_general_ci NOT NULL  DEFAULT '',
`dp` bigint(55) NOT NULL DEFAULT '0',
`gmlevel` varchar(11) COLLATE latin1_general_ci NOT NULL  DEFAULT '',
`avatar` varchar(100) COLLATE latin1_general_ci NOT NULL  DEFAULT '',
PRIMARY KEY (`id`,`acc_login`)
) ENGINE=MyISAM AUTO_INCREMENT=112 DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;");
echo $installer_lang['Create']." wwc2_users_more<br>";

$query11 = mysql_query(
"CREATE TABLE `wwc2_vote_data` (
`id` bigint(21) NOT NULL AUTO_INCREMENT,
`userid` bigint(21) DEFAULT NULL,
`siteid` bigint(21) NOT NULL,
`timevoted` bigint(21) NOT NULL,
`voteip` varchar(21) COLLATE latin1_general_ci DEFAULT NULL,
PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=170 DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;");
echo $installer_lang['Create']." wwc2_vote_data<br><br>";


//populate configuration:
$query5 = mysql_query("INSERT INTO `wwc2_config` VALUES ('engine_lang','".$lang."','','1','')") or die (mysql_error());
echo $installer_lang['Inserting data to']." wwc2_config";
mysql_query("INSERT INTO `wwc2_config` VALUES ('engine_core','".$_SESSION['wwcmsv2install']['core']."','','1','')") or die (mysql_error());
mysql_query("INSERT INTO `wwc2_config` VALUES ('engine_logon_db','".$_SESSION['wwcmsv2install']['logon_db']."','','1','')") or die (mysql_error());
mysql_query("INSERT INTO `wwc2_config` VALUES ('engine_styleid','1','Change style ID to change style.','1','')") or die (mysql_error());
/**
* Now construct realm databases string in format:
* "DB1|REALM_PORT|DB1_HOST|DB1_USER|DB1_PASS;DB2|REALM_PORT;DB3|REALM_PORT" etc...
* without quotes
*/
$char_counter=0;$char_db='';$raorsoap_port='';
foreach ($_SESSION['wwcmsv2install']['char_db'] as $key2=>$sess_chardb)
{

if($sess_chardb<>'')
{
if(trim($_SESSION['wwcmsv2install']['char_host'][$char_counter])=='')
$char_db.=  ''.$sess_chardb.'|'.$_SESSION['wwcmsv2install']['char_port'][$char_counter].';';
else
$char_db.=  ''.$sess_chardb.'|'.$_SESSION['wwcmsv2install']['char_port'][$char_counter].'|'.$_SESSION['wwcmsv2install']['char_host'][$char_counter].'|'.$_SESSION['wwcmsv2install']['char_dbuser'][$char_counter].'|'.$_SESSION['wwcmsv2install']['char_dbpass'][$char_counter].';';
/**
* TRINITY RA PORT or SOAP PORT:
*/
if ($_SESSION['wwcmsv2install']['core']=='Trinity' or $_SESSION['wwcmsv2install']['core']=='MaNGOS' or $_SESSION['wwcmsv2install']['core']=='Trinitysoap')
{
$raorsoap_port.= $_SESSION['wwcmsv2install']['char_rasoap'][$char_counter].'|';
}
/**
* Realm names:
*/
$_SESSION['wwcmsv2install']['char_names2'][$char_counter]=$_SESSION['wwcmsv2install']['char_names'][$char_counter];
}

$char_counter++;

}
$char_db=rtrim($char_db,";");
//continue:
/**
* TRINITY RA PORT or MANGOS SOAP PORT or TRINITY SOAP PORT:
*/
if ($_SESSION['wwcmsv2install']['core']=='Trinity')
{
if ($raorsoap_port=='') $raorsoap_port='3443';
mysql_query("INSERT INTO `wwc2_config` VALUES ('trinity_soap_port','7878','TrintyCore: SOAP Port (for sending ingame mail)<br><small>realm1_SOAP_port|realm2_SOAP_port</small>','1','')") or die (mysql_error());
mysql_query("INSERT INTO `wwc2_config` VALUES ('trinity_ra_port','".$raorsoap_port."','TrintyCore: Remote Access Port (for sending ingame mail)<br><small>realm1_RA_port|realm2_RA_port</small>','1','')") or die (mysql_error());
mysql_query("INSERT INTO `wwc2_config` VALUES ('mangos_soap_port','7878','MaNGOS: Soap Port (for sending ingame mail)','1','')") or die (mysql_error());

mysql_query("INSERT INTO `wwc2_config` VALUES ('trinity_ra_userpass','".strtoupper($_SESSION['wwcmsv2install']['char_rasoap_user'])."|".$_SESSION['wwcmsv2install']['char_rasoap_pass']."','TrintyCore: RA Username and Password (for sending ingame mail)<br><small>RA_username|RA_password</small>','1','')") or die (mysql_error());


}
else if ($_SESSION['wwcmsv2install']['core']=='MaNGOS')
{

if ($raorsoap_port=='') $raorsoap_port='7878';
mysql_query("INSERT INTO `wwc2_config` VALUES ('trinity_soap_port','".$raorsoap_port."','TrintyCore: SOAP Port (for sending ingame mail)<br><small>realm1_SOAP_port|realm2_SOAP_port</small>','1','')") or die (mysql_error());
mysql_query("INSERT INTO `wwc2_config` VALUES ('mangos_soap_port','".$raorsoap_port."','MaNGOS: Soap Port (for sending ingame mail)','1','')") or die (mysql_error());
mysql_query("INSERT INTO `wwc2_config` VALUES ('trinity_ra_port','3443','TrintyCore: Remote Access Port (for sending ingame mail)<br><small>realm1_RA_port|realm2_RA_port</small>','1','')") or die (mysql_error());
mysql_query("INSERT INTO `wwc2_config` VALUES ('mangos_soap_userpass','".strtoupper($_SESSION['wwcmsv2install']['char_rasoap_user'])."|".$_SESSION['wwcmsv2install']['char_rasoap_pass']."','MaNGOS: SOAP Username and Password(for sending ingame mail)<br><small>SOAP_username|SOAP_password</small>','1','')") or die (mysql_error());
}
else if ($_SESSION['wwcmsv2install']['core']=='Trinitysoap')
{
if ($raorsoap_port=='') $raorsoap_port='7878';
mysql_query("INSERT INTO `wwc2_config` VALUES ('trinity_soap_port','".$raorsoap_port."','TrintyCore: SOAP Port (for sending ingame mail)<br><small>realm1_SOAP_port|realm2_SOAP_port</small>','1','')") or die (mysql_error());
mysql_query("INSERT INTO `wwc2_config` VALUES ('mangos_soap_port','".$raorsoap_port."','MaNGOS: Soap Port (for sending ingame mail)<br><small>realm1_SOAP_port|realm2_SOAP_port</small>','1','')") or die (mysql_error());
mysql_query("INSERT INTO `wwc2_config` VALUES ('trinity_ra_port','3443|','TrintyCore: Remote Access Port (for sending ingame mail)<br><small>realm1_RA_port|realm2_RA_port</small>','1','')") or die (mysql_error());
mysql_query("INSERT INTO `wwc2_config` VALUES ('mangos_soap_userpass','".strtoupper($_SESSION['wwcmsv2install']['char_rasoap_user'])."|".$_SESSION['wwcmsv2install']['char_rasoap_pass']."','MaNGOS: SOAP Username and Password (for sending ingame mail)<br><small>SOAP_username|SOAP_password</small>','1','')") or die (mysql_error());
mysql_query("INSERT INTO `wwc2_config` VALUES ('trinity_soap_userpass','".strtoupper($_SESSION['wwcmsv2install']['char_rasoap_user'])."|".$_SESSION['wwcmsv2install']['char_rasoap_pass']."','TrintyCore: SOAP Username and Password (for sending ingame mail)<br><small>SOAP_username|SOAP_password</small>','1','')") or die (mysql_error());
}
else
{
mysql_query("INSERT INTO `wwc2_config` VALUES ('mangos_soap_port','7878','MaNGOS: Soap Port (for sending ingame mail)','1','')") or die (mysql_error());
mysql_query("INSERT INTO `wwc2_config` VALUES ('trinity_ra_port','3443','TrintyCore: Remote Access Port (for sending ingame mail)<br><small>realm1_RA_port|realm2_RA_port</small>','1','')") or die (mysql_error());
mysql_query("INSERT INTO `wwc2_config` VALUES ('trinity_soap_port','7878','TrintyCore: SOAP Port (for sending ingame mail)<br><small>realm1_SOAP_port|realm2_SOAP_port</small>','1','')") or die (mysql_error());
mysql_query("INSERT INTO `wwc2_config` VALUES ('trinity_soap_userpass','|','TrintyCore: SOAP Username and Password (for sending ingame mail)<br><small>SOAP_username|SOAP_password</small>','1','')") or die (mysql_error());
mysql_query("INSERT INTO `wwc2_config` VALUES ('mangos_soap_userpass','|','MaNGOS: SOAP Username and Password (for sending ingame mail)<br><small>RA_username|RA_password</small>','1','')") or die (mysql_error());
mysql_query("INSERT INTO `wwc2_config` VALUES ('trinity_ra_userpass','|','TrintyCore: RA Username and Password (for sending ingame mail)<br><small>RA_username|RA_password</small>','1','')") or die (mysql_error());
}

mysql_query("INSERT INTO `wwc2_config` VALUES ('engine_char_dbs','".$char_db."','<br><small>DB1|REALM_PORT|DB1_HOST|DB1_USER|DB1_PASS;DB2|REALM_PORT</small>','1','')") or die (mysql_error());
/**
* Realm names name|name|name
*/
$char_names=implode("|",$_SESSION['wwcmsv2install']['char_names2']);


//continue:
mysql_query("INSERT INTO `wwc2_config` VALUES ('engine_realmnames','".htmlspecialchars($char_names)."','<br><small>realmname1|realmname2|realmname3</small>','1','')") or die (mysql_error());


mysql_query("INSERT INTO `wwc2_config` VALUES ('engine_web_db','".$_SESSION['wwcmsv2install']['web_db']."','','1','')") or die (mysql_error());

mysql_query("INSERT INTO `wwc2_config` VALUES ('engine_acp_folder','admincp\/','foldername\/','1','')") or die (mysql_error());

mysql_query("INSERT INTO `wwc2_config` VALUES ('license','FREE','','1','')") or die (mysql_error());

if ($_SESSION['wwcmsv2install']['core']=='ArcEmu')
mysql_query("INSERT INTO `wwc2_config` VALUES ('premission_admin','az','','1','')") or die (mysql_error());
else
mysql_query("INSERT INTO `wwc2_config` VALUES ('premission_admin','4','','1','')") or die (mysql_error());

if ($_SESSION['wwcmsv2install']['core']=='ArcEmu')
mysql_query("INSERT INTO `wwc2_config` VALUES ('premission_gm','a','','1','')") or die (mysql_error());
else
mysql_query("INSERT INTO `wwc2_config` VALUES ('premission_gm','3','','1','')") or die (mysql_error());

mysql_query("INSERT INTO `wwc2_config` VALUES ('title','My WoW Server','','1','')") or die (mysql_error());

mysql_query("INSERT INTO `wwc2_config` VALUES ('engine_logusers','true','true/false, disable if your website is slow','1','true|false')") or die (mysql_error());
mysql_query("INSERT INTO `wwc2_config` VALUES ('vote_enable','1','1 = enabled;  0 = disabled','1','0|1')") or die (mysql_error());

mysql_query("INSERT INTO `wwc2_config` VALUES ('module_userpanel','loginout.php|register.php|credits.php|userpanel.php|MODULE_TEMPLATE.php','Userpanel: Do not show modules in this list<br><small>module1.php|module2.php</small>','1','')") or die (mysql_error());

mysql_query("INSERT INTO `wwc2_config` VALUES ('footer_detail','0','Footer Credits: <small>0 = simplified; 1 = full detail; 2 = full for admins only</small>','1','0|1|2')") or die (mysql_error());

mysql_query("INSERT INTO `wwc2_news` (title,content,timepost,author) VALUES ('Welcome',
'Thank you for using OpenWoW CMS v2.

If your administrator double click here to edit news.
Go to [b]administration panel[/b] to manage CMS.','".date("U")."','WebWoWCMSv2')") or die (mysql_error());

$this->mysql_import_file('./engine/installer/sql/wwc2_template.sql',$errmsg);
echo $errmsg;$errmsg='';
echo "<br>".$installer_lang['Inserting data to']." wwc2_template";
$this->mysql_import_file('./engine/installer/sql/wwc2_links.sql',$errmsg);
echo $errmsg;
echo "<br>".$installer_lang['Inserting data to']." wwc2_links<br>";


if ( $query1 && $query2 && $query3 && $query4 && $query5 && $query6 && $query7 && $query8 && $query9 && $query10 && $query11 ) {
mysql_query( "COMMIT" );
mysql_query( "SET AUTOCOMMIT=1" );
mysql_close( $connect );
echo "<br><font color=green>".$installer_lang['Tables are created successfully']."</font>";

}
else {
if ($query1) echo "true"; else echo 'false';
echo $installer_lang['Failed to create tables']."<br>" . mysql_error();
mysql_query( "ROLLBACK" );
mysql_query( "SET AUTOCOMMIT=1" );


mysql_close( $connect );
$stop=true;
}
}
}
elseif ($step=='7')/*add admin user*/
{

if ($_SESSION['wwcmsv2install']['web_db']=='')
{
echo $installer_lang['You did not enter website database, please go step back.'];return;

}
//
// Create db, add tables and write config.php
//
$connected=true;
$connect = @mysql_connect($_SESSION['wwcmsv2install']['db_host'], $_SESSION['wwcmsv2install']['db_user'], $_SESSION['wwcmsv2install']['db_pass']) or $connected=false;
if ($connected)
{
/**
* Ok we need to check for admin account
**/
?>
<script type="text/javascript">
function checkadmin()
{
var host = document.getElementById("host").value;
var user = document.getElementById("user").value;
var pass = document.getElementById("pass").value;
var admin_username = document.getElementById("admin_username").value;
var admin_password = document.getElementById("admin_password").value;
$('#db_con').fadeIn('slow', function() {});
document.getElementById("checkadmin").innerHTML="<?php echo $installer_lang['Connecting']; ?>...";
$.post("./engine/installer/dynamic/checkadmin.php?l=<?php echo $installer_lang['Next Step']; ?>&f=<?php echo $installer_lang['Connection Failed']; ?>&s=<?php echo $installer_lang['Connection Successful']; ?>", {host:host, user: user,pass: pass, admin_username: admin_username, admin_password: admin_password },function(data)
{
document.getElementById("checkadmin").innerHTML="" + data;
}
);
}
</script>
<input name="host" id="host" type="hidden" value="<?php echo $_SESSION['wwcmsv2install']['db_host']; ?>">
<input name="user" id="user" type="hidden" value="<?php echo $_SESSION['wwcmsv2install']['db_user']; ?>">
<input name="pass" id="pass" type="hidden" value="<?php echo $_SESSION['wwcmsv2install']['db_pass']; ?>">
<?php
echo $installer_lang['Admin Username'].':';
$this->Input("admin_username",'');echo '<br>';
echo $installer_lang['Admin Password'].':';
$this->Input("admin_password",'');echo '<br>';
echo "<br><span id='checkadmin'><input type='button' onclick='javascript:checkadmin();return false' value='".$installer_lang['Save']."'><br></span>";
$stop=true;
}
}
elseif ($step=='8')
{



$connected=true;
$connect = @mysql_connect($_SESSION['wwcmsv2install']['db_host'], $_SESSION['wwcmsv2install']['db_user'], $_SESSION['wwcmsv2install']['db_pass']) or $connected=false;
if ($connected)
{


$string = "<?php" . $ln. '$config=array(' . $ln;

$sql1=mysql_query("SELECT * FROM ".$_SESSION['wwcmsv2install']['web_db'].".wwc2_config")or die (mysql_error());
while ($sql2=mysql_fetch_array($sql1))
{
$string .= "'".$sql2[0]."' => '".$sql2[1]."'," . $ln;
}

$string .= ");" . $ln . $ln . "define('AXE',1);" . $ln . $ln;
$this->writtefile($string,'./config/config.php');

echo "<br><br>";

$string = "<?php" . $ln. '$db_host="'.$_SESSION['wwcmsv2install']['db_host'].'";' . $ln;
$string .= '$db_user="'.$_SESSION['wwcmsv2install']['db_user'].'";' . $ln;
$string .= '$db_pass="'.$_SESSION['wwcmsv2install']['db_pass'].'";' . $ln;
$string .= "define('AXE_db',1);" . $ln . $ln;
$this->writtefile($string,'./config/config_db.php');


}
else
{
echo $installer_lang['Go to']." '".$installer_lang['Database Connection']."'.";
$stop=true;
}

}
elseif ($step=='9')
{
echo "Whoops, we are sorry but script could not create following files: config/config.php and config/config_db.php<br>
probably due CHMOD file premissions, try using your ftp program and chmod them to be writtable, also if you are on windows
navigate to www/config/ folder, right click propreties, uncheck 'Read Only'. If files does not exists, create two empty files
(config.php and config_db.php).<br><br>Click on last step on the left.";return;
}

if ($stop) return;
if ($step=='8')
echo '<br><br><input name="next" type="submit" value="'.$installer_lang['Start using the site'].'"></form>';
else
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
function writtefile($string,$file)//prints
{
global $lang,$installer_lang;

$fh = fopen( $file, 'w');
fwrite($fh, $string);
fclose($fh);

echo $file . ' <font color=\'green\'><b>' . $installer_lang['written successfully']. '</b></font>';

/**
*We will leave this file writtable becouse administrator will want to recache config.php
*but we will chmod file config_db.php becouse it no longer needs changing.
**/
if (preg_match("/config_db.php/",$file))
@chmod($file, 0644);
if (is_writable($file))
{
echo '<br>' . $installer_lang['We suggest that you CHMOD'] . ' <b>' . $file . '</b> ' . $installer_lang['to'] . ' 0664.';
}
}

// --------------------------------------------------------------------

/**
* Check table
*
* @access	public
* @param	string
* @return	boolean
*/
function checkTable($table)
{
$result = @mysql_query("SELECT * FROM $table");

// I could just cast this, but I feel as if this is safer approach
return (!$result) ? FALSE : TRUE;
}

// --------------------------------------------------------------------

/**
* Check For Empty Database
*
* @access	public
* @param	string
* @return	boolean
*/
function checkForEmptyDB($database)
{
$query  = 'SELECT count(*) TABLES, table_schema ';
$query .= 'FROM information_schema.TABLES ';
$query .= 'WHERE table_schema= \'' . $database . '\' ';
$query .= 'GROUP BY table_schema';

$result = @mysql_query($query);
$result = mysql_fetch_array($result);

return ($result == '0') ? TRUE : FALSE;
}

// --------------------------------------------------------------------

/**
* Input
*
* @access	public
* @param	string
* @param	string
* @param	string
* @param	string
* @param	string
* @param	string
* @return	void
*/
function Input($name, $value=false,$text=false, $text2=false,$id=false, $more=false)
{
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
* @access	public
* @param	string
* @return	boolean
*/
function Tree()
{
global $installer_lang, $lang;

$current_step = '1';

if(isset($_GET['step']) && !empty($_GET['step']))
{
$current_step = sanitize($_GET['step']);
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
echo '<strong><a href="./?step=' . $i . '&lang=' . $lang . '">' . $step . '</a></strong><br>';
$color = 'gray';
}

else
{
echo '<font color='.$color.'><a href="./?step='.$i.'&lang='.$lang.'">'.$step.'</a></font><br>';
}

$i++;
}
}

// --------------------------------------------------------------------

/**
* MySQL Import File
*
* @access	public
* @param	string
* @return	boolean
*/
function mysql_import_file($filename, &$errmsg)
{
// Read the file
$lines = file($filename);

if(!$lines)
{
$errmsg = "Could not open file $filename";
return FALSE;
}

$scriptfile = FALSE;


// Run each line as a query
foreach($lines as $query)
{
$query = trim($query);

if($query == '')
{
continue;
}

if(!mysql_query($query.';'))
{
$errmsg = "<strong>Query</strong> " . htmlspecialchars($query) . " <b>FAILED</b><br>REPORT: " . mysql_error() . "<br>";
return FALSE;
}
}

return TRUE;
}
}

$Install = new Install;
?>


<html>
<head>
<title><?php echo $installer_lang["WWC v2 Installer"]; ?></title>

<meta http-equiv = "Content-Type" content = "text/html;charset=utf-8">
<link href = "./engine/installer/res/style.css" rel = "stylesheet" type = "text/css"/>
<script src="./engine/js/jquery-1.4.2.min.js"></script>
</head>

<body>
<div id = "container">
<div id = "header">
<table width="100%" height="100px" cellpadding="0" cellspacing="0" border="0" >
<tr>
<td width="200px" valign="top">
<h1><img src="engine/installer/res/logo.png"><span><strong><?php echo $installer_lang["WebWoW CMS v2 Install Script"]; ?></strong></span></h1>
</td>
<td><div id = "footer">OpenWoW CMS v2 &copy; 2012<br/>Powered by <a href = "http://www.openwow.net" title="OpenWoW CMS">OpenWoW</a></div>
</td></tr>
</table>
</div>
<div id = "content">
<br/>
<table width="100%" height="97%" border="0" >
<tr>
<td width="200px" id="listmenu" valign="top"><?php
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
echo 'realm_databases = '.$Install->ln();
foreach ($_SESSION['wwcmsv2install']['char_db'] as $key2=>$sess_chardb)
{

if($sess_chardb=='')
unset($_SESSION['wwcmsv2install']['char_db'][$key2]);
else
echo  '   '.$sess_chardb.$Install->ln();
$noparsemore=true;
}
}
else
echo $key.' = '.htmlspecialchars(trim($storeddata)). $Install->ln(). '------------------'. $Install->ln();

}
}

}
}

if (isset($_SESSION['wwcmsv2install']['web_db']) && trim($_SESSION['wwcmsv2install']['web_db'])<>'')
echo  '------------------'. $Install->ln().'web_db = '.htmlspecialchars(trim($_SESSION['wwcmsv2install']['web_db']));
echo "</textarea>";

?>
</td>
<td style="padding-left:28px" valign="top"><?php
$Install->Go();
?></td>
</tr>
</table>


</div>

<!---->
</div>
</body>
</html><?php
//
// Exit at end
//
exit;

