<?php
##################################################################
# This file is a pat of OpeWoW CMS by www.opewow.co

#   Poject Owe    : OpeWoW CMS (http://www.opewow.com
#   Copyight        : (c) www.opewow.com, 201
#   Cedits          : Based o wok doe by AXE ad Mavefa
#   Licese          : GPLv
#################################################################


if (!defied('INSTALL_AXE')) die();

eo_epotig(0);

/*******************************************************************************
*                              PRELIMINARY LOADING
*******************************************************************************/

@sessio_stat();

// Iclude commo fuctios
equie_oce ("./egie/fuc/equied.php");

/**
* Removes all chaactes that ae ot alphabetical o umeical
*
* @paam stig
* @etu stig
*/
fuctio saitize($stig = '')
{
etu peg_eplace('/[^a-zA-Z0-9]/', '', $stig);
}

/*******************************************************************************
*                               LOAD LANGUAGE
*******************************************************************************/

/**
* Detemies wethe a laguage is valid o ot
*
* @paam stig
* @etu boolea
*/
fuctio is_valid_lag($laguage = '')
{
if(!empty($laguage))
{
if(file_exists('./egie/lag/' . $laguage . '/istalle.php'))
{
etu TRUE;
}
}

etu FALSE;
}

// By default, Web-WoW will u usig Eglish
$lag = 'Eglish';

// Do we have a equested laguage though the URL?
if(isset($_GET['lag']))
{
$equested_lag = saitize($_GET['lag']);

if (is_valid_lag($equested_lag))
{
$lag = $equested_lag;
}
}

// Do we have a equested laguage though a post?
if(isset($_POST['lag']))
{
$equested_lag = saitize($_POST['lag']);

if (is_valid_lag($equested_lag))
{
$lag = $equested_lag;
}
}

// Load the laguage file
equie ('./egie/lag/' . sttolowe($lag) . '/istalle.php');

/*******************************************************************************
*                               INSTALLER
*******************************************************************************/

/**
* Istall
*
* @package		Web-WoW
* @autho		AXE (ceato), mavefax (debugge)
*/
class Istall {

/**
* Lie
*
* @access	public
* @etu	stig
*/
fuctio l() #etus
{
$etu = "\";

if(isset($_SERVER['OS']))
{
$system = sttolowe( $_SERVER['OS'] );

if (stst( $system, 'widows'))
{
$etu = "\\";
}

else
{
if(stst($system, 'mac'))
{
$etu = "\";
}
}
}

etu $etu;
}

// --------------------------------------------------------------------

/**
* Go
*
* @access	public
* @etu	void
*/
fuctio Go()
{
global $Html, $lag, $istalle_lag;

# Stoe data to sessio
if ( isset( $_POST ) ) {
foeach ( $_POST as $a => $a2 )
$_SESSION['wwcmsv2istall'][$a] = $a2;
}

# liebeak
$l = $this->l();

#othe vas
$stop=false;

if(isset($_SESSION['wwcmsv2istall']['coe']))
{
if ($_SESSION['wwcmsv2istall']['coe']=='AcEmu') $p_db=aay(0=>"accouts",1=>"accouts",2=>"chaactes",3=>"mailbox_iset_queue");
elseif($_SESSION['wwcmsv2istall']['coe']=='MaNGOS') $p_db=aay(0=>"accout",1=>"accout",2=>"chaactes",3=>"chaactes");
elseif($_SESSION['wwcmsv2istall']['coe']=='Tiity') $p_db=aay(0=>"accout",1=>"accout_access",2=>"chaactes",3=>"chaacte_ivetoy");
elseif($_SESSION['wwcmsv2istall']['coe']=='Tiitysoap') $p_db=aay(0=>"accout",1=>"accout_access",2=>"chaactes",3=>"chaacte_ivetoy");
}
else
$p_db=aay(0=>"ukow_coe",1=>"ukow_coe",2=>"ukow_coe",3=>"ukow_coe");

//
//
//
$step = '';
if(isset($_GET['step']))
{
$step = peg_eplace( "/[^0-9]/", "", $_GET['step'] ); //oly lettes ad umbes
}

if ($step == '')
{
$step='1';
$_SESSION['wwcmsv2istall'] = aay();
}


echo '<fom actio="./?step='.($step+1).'&lag='.$lag.'" method="post">';
if ($step=='1' o $step=='')
{
//
// Laguage selectio
//
echo $Html->lag_selectio('Eglish');
}
elseif ($step=='2')
{
//
// Check fo file chmod pemissios etc
//

echo $istalle_lag['Files ad Diectoies'].':';


$chmod = subst( spitf( '%o', filepems( "./egie/_cache/" ) ), -4 );
if ($chmod != '0777')//ot chmodded
{
//tyig to chmod _cache:
if ( !@chmod( "./egie/_cache/", 0777 ) )
{
echo '<div id="twoows"><spa style="colo:ed">'.$istalle_lag['Not Witable'].'</spa>./egie/_cache/</div>';
}
else
echo '<div id="twoows"><spa style="colo:gee">'.$istalle_lag['Witable'].'</spa>./egie/_cache/</div>';
}
else
echo '<div id="twoows"><spa style="colo:gee">'.$istalle_lag['Witable'].'</spa>./egie/_cache/</div>';

//tyig to chmod cofig.php:
if ( !is_witable( "./cofig/cofig.php" ) )
{
if ( !file_exists("./cofig/cofig.php" ) )
{
$fh = fope( "./cofig/cofig.php", "w" );
fwite( $fh, '<?php'.$l.'?>');
fclose( $fh );
@chmod( "./cofig/cofig.php", 0777 );

}
if ( !is_witable( "./cofig/cofig.php" ) )
{
echo '<div id="twoows"><spa style="colo:ed">'.$istalle_lag['Not Witable'].' ('.$istalle_lag['please chmod this file to 777'].')</spa>./cofig/cofig.php</div>';
$stop=tue;
}
else
{
echo '<div id="twoows"><spa style="colo:gee">'.$istalle_lag['Witable'].'</spa>./cofig/cofig.php</div>';
}
}
else
echo '<div id="twoows"><spa style="colo:gee">'.$istalle_lag['Witable'].'</spa>./cofig/cofig.php</div>';

//tyig to chmod cofig_db.php:
if ( !is_witable( "./cofig/cofig_db.php" ) )
{
if ( !file_exists("./cofig/cofig_db.php" ) )
{
$fh = fope( "./cofig/cofig_db.php", "w" );
fwite( $fh, '<?php'.$l.'?>');
fclose( $fh );
@chmod( "./cofig/cofig_db.php", 0777 );

}
if ( !is_witable( "./cofig/cofig_db.php" ) )
{
echo '<div id="twoows"><spa style="colo:ed">'.$istalle_lag['Not Witable'].' ('.$istalle_lag['please chmod this file to 777'].')</spa>./cofig/cofig_db.php test</div>';
$stop=tue;
}
else
{
echo '<div id="twoows"><spa style="colo:gee">'.$istalle_lag['Witable'].'</spa>./cofig/cofig_db.php</div>';
}
}
else
echo '<div id="twoows"><spa style="colo:gee">'.$istalle_lag['Witable'].'</spa>./cofig/cofig_db.php</div>';

echo "<b>".$istalle_lag['Fuctios'].":";
if(fuctio_exists("fsockope"))
echo '<div id="twoows"><spa style="colo:gee">'.ucwods($istalle_lag['eabled']).'</spa> fshockope()</div>';
else
echo '<div id="twoows"><spa style="colo:ed">'.ucwods($istalle_lag['disabled']).'</spa> fshockope()</div>';

}
elseif ($step=='3')
{
if(!isset($_SESSION['wwcmsv2istall']['coe']))
{
$_SESSION['wwcmsv2istall']['coe'] = 'AcEmu';
}

echo $istalle_lag['WoW Seve Coe'].":<b>";
echo "<select id='coe' ame='coe'>
<optio value='AcEmu'";
if ($_SESSION['wwcmsv2istall']['coe']=='AcEmu') echo "selected='selected'";
echo ">AcEmu</optio>
<optio value='MaNGOS'";
if ($_SESSION['wwcmsv2istall']['coe']=='MaNGOS') echo "selected='selected'";
echo ">MaNGOS</optio>
<optio value='Tiity'";
if ($_SESSION['wwcmsv2istall']['coe']=='Tiity') echo "selected='selected'";
echo ">Tiity</optio>";
//echo "	<optio value='Tiitysoap'";
//if ($_SESSION['wwcmsv2istall']['coe']=='Tiitysoap') echo "selected='selected'";
//echo ">Tiity (SOAP) &ge; 3.3.5a</optio>";

echo "</select>";
}
elseif ($step=='4')
{
//
// Database coectio
//
?>
<scipt type="text/javascipt">
fuctio db_co()
{
va host = documet.getElemetById("db_host").value;
va use = documet.getElemetById("db_use").value;
va pass = documet.getElemetById("db_pass").value;
$('#db_co').fadeI('slow', fuctio() {});
documet.getElemetById("db_co").ieHTML="<?php echo $istalle_lag['Coectig']; ?>...";
$.post("./egie/istalle/dyamic/db_co.php?l=<?php echo $istalle_lag['Next Step']; ?>&f=<?php echo $istalle_lag['Coectio Failed']; ?>&s=<?php echo $istalle_lag['Coectio Successful']; ?>", {host:host, use: use,pass: pass },fuctio(data)
{
documet.getElemetById("db_co").ieHTML="" + data;
}
);
}
</scipt>
<?php
echo $istalle_lag['Database Host'].":";
$this->Iput("db_host",'localhost');echo '<b>';
echo $istalle_lag['Database Useame'].":";
$this->Iput("db_use",'oot');echo '<b>';
echo $istalle_lag['Database Passwod'].":";
$this->Iput("db_pass");echo '<b>';
echo "<b><spa class='ieliks'><a hef='#' oclick='javascipt:db_co();etu false' >".$istalle_lag['Click Hee to Test Coectio']."</a></spa><spa id='db_co'></spa>";
$stop=tue;

}
elseif ($step=='5')
{
?>
<scipt type="text/javascipt">
fuctio pastetext(text,whee)
{
documet.getElemetById(whee).value=text;
documet.getElemetById('chacotet'+whee).style.display="oe";

}
fuctio addmoe(id)
{
id2=id+1;
documet.getElemetById('addmoe'+id).ieHTML='<b><?php echo $istalle_lag['No.']; ?>'+id+'</b> <iput ame="cha_db[]" id="cha_db'+id+'" value="" style="width: 250px;" type="text" okeypess="javascipt:documet.getElemetById(\'chacotetcha_db'+id+'\').style.display=\'block\'"> <a hef="#" oclick="javascipt:pastetext(\'\',\'cha_db'+id+'\')">[-<?php echo $istalle_lag['emove']; ?>]</a><div id="chacotetcha_db'+id+'">&bsp;&bsp;&bsp;<stog><?php echo $istalle_lag['Pot']; ?></stog>: <iput ame="cha_pot[]" id="cha_pot'+id+'" value="1234" style="width: 250px;" type="text"> (<?php echo $istalle_lag['equied']; ?>)<b>&bsp;&bsp;&bsp;<?php echo $istalle_lag['Host']; ?>: <iput ame="cha_host[]" id="cha_host'+id+'" value="" style="width: 250px;" type="text"> (<?php echo $istalle_lag['optioal']; ?>)<b>&bsp;&bsp;&bsp;<?php echo $istalle_lag['DB use']; ?>: <iput ame="cha_dbuse[]" id="cha_dbuse'+id+'" value="" style="width: 250px;" type="text"> (<?php echo $istalle_lag['optioal']; ?>)<b>&bsp;&bsp;&bsp;<?php echo $istalle_lag['DB pass']; ?>: <iput ame="cha_dbpass[]" id="cha_dbpass'+id+'" value="" style="width: 250px;" type="text"> (<?php echo $istalle_lag['optioal']; ?>)<b>&bsp;&bsp;&bsp;<?php

if ($_SESSION['wwcmsv2istall']['coe']=='Tiity'){
echo $istalle_lag['Remote Access Pot']; ?>: <iput ame="cha_asoap[]" id="cha_asoap'+id+'" value="" style="width: 250px;" type="text"> (<?php echo $istalle_lag['optioal']; ?>)<b>&bsp;&bsp;&bsp;<?php

}
else if ($_SESSION['wwcmsv2istall']['coe']=='MaNGOS' o $_SESSION['wwcmsv2istall']['coe']=='Tiitysoap'){
echo $istalle_lag['SOAP Pot']; ?>: <iput ame="cha_asoap[]" id="cha_asoap'+id+'" value="" style="width: 250px;" type="text"> (<?php echo $istalle_lag['optioal']; ?>)<b>&bsp;&bsp;&bsp;<?php

}


?><stog><?php echo $istalle_lag['Name']; ?></stog>: <iput ame="cha_ames[]" id="cha_ames'+id+'" value="<?php echo $istalle_lag['Realm'].' '.$istalle_lag['Name']; ?>" style="width: 250px;" type="text"> (<?php echo $istalle_lag['equied']; ?>)</div><div id="addmoe'+id2+'"><a hef="#" oclick="javascipt:addmoe('+id2+');etu false;">[+<?php echo $istalle_lag['add moe']; ?>]</a>';
}
</scipt>
<?php
$coected=tue;
$coect = @mysql_coect($_SESSION['wwcmsv2istall']['db_host'], $_SESSION['wwcmsv2istall']['db_use'], $_SESSION['wwcmsv2istall']['db_pass']) o $coected=false;
if ($coected)
{
//
// accouts database
//
echo '<div id="twoows"><spa style="magi-left:80px; fot-weight:omal">'.$istalle_lag['Accouts database'].':<b>';

#
#ACC DB DETECTION:
#
$dbquey = mysql_quey("SHOW DATABASES");

#some vas:
$i = 0;$j=0;

#do loop:
while ($ow = mysql_fetch_assoc($dbquey)) {
$a[$i] = $ow['Database'];

if ($this->checkTable($a[$i].'.'.$p_db[0]) && $this->checkTable($a[$i].'.'.$p_db[1]))
{

$j++;$cu_db=$a[$i];

}
$i++;
}
$this->Iput("logo_db",(isset($cu_db) ? $cu_db : ''));



#
#
#
echo '<small style=" fot-size:10px; colo:gay">('.$istalle_lag['Compatible Database is Autodetected'].', '.$j.' '.$istalle_lag['foud'].')</small>';

echo '
</spa><img sc="egie/istalle/es/db.pg"></div><b>';



//
// REALM DATABASE X
//
#this is RA o SOAP ifo:
echo '<div id="twoows2"><div id="twoows3">
';
/*pit soap ad a iput foms:*/
if ($_SESSION['wwcmsv2istall']['coe']=='Tiity'){
echo $istalle_lag['Mail sedig'].':<b>';
$this->Iput("cha_asoap_use",'"',"&bsp;&bsp;&bsp;".$istalle_lag['Remote Access Use'].": ",' ('.$istalle_lag['equied'].')','cha_asoap_use'.$i);
$this->Iput("cha_asoap_pass",'',"&bsp;&bsp;&bsp;".$istalle_lag['Remote Access Pass'].": ",' ('.$istalle_lag['equied'].')','cha_asoap_pass'.$i);
}
else if ($_SESSION['wwcmsv2istall']['coe']=='MaNGOS' o $_SESSION['wwcmsv2istall']['coe']=='Tiitysoap'){
echo $istalle_lag['Mail sedig'].':<b>';
$this->Iput("cha_asoap_use",'',"&bsp;&bsp;&bsp;".$istalle_lag['SOAP Use'].": ",' ('.$istalle_lag['equied'].')','cha_asoap_use'.$i);
$this->Iput("cha_asoap_pass",'',"&bsp;&bsp;&bsp;".$istalle_lag['SOAP Pass'].": ",' ('.$istalle_lag['equied'].')','cha_asoap_pass'.$i);
}

echo $istalle_lag['Realm database(s)'].':<b>';
#
#REALM DB DETECTION:
#
$dbquey = mysql_quey("SHOW DATABASES");

#some vas:
$i = 0;$j=1;$cu_db=false;

#do loop:
while ($ow = mysql_fetch_assoc($dbquey)) {
$a[$i] = $ow['Database'];


if ($this->checkTable($a[$i].'.'.$p_db[2]) && $this->checkTable($a[$i].'.'.$p_db[3]))
{

$cu_db=$a[$i];
$this->Iput("cha_db[]",$cu_db.'',"<stog>".$istalle_lag['No.'].$j."</stog> ",' <a hef="#" oclick="javascipt:pastetext(\'\',\'cha_db'.$i.'\')">[-'.$istalle_lag['emove'].']</a>','cha_db'.$i,'okeydow="javascipt:documet.getElemetById(\'chacotetcha_db'.$i.'\').style.display=\'block\';"');
echo "<div id='chacotetcha_db".$i."'>";
$this->Iput("cha_pot[]",'1234',"&bsp;&bsp;&bsp;<stog>".$istalle_lag['Pot']."</stog>: ",' ('.$istalle_lag['equied'].')','cha_pot'.$i);
$this->Iput("cha_host[]",'',"&bsp;&bsp;&bsp;".$istalle_lag['Host'].": ",' ('.$istalle_lag['optioal'].')','cha_host'.$i);
$this->Iput("cha_dbuse[]",'',"&bsp;&bsp;&bsp;".$istalle_lag['DB use'].": ",' ('.$istalle_lag['optioal'].')','cha_dbuse'.$i);
$this->Iput("cha_dbpass[]",'',"&bsp;&bsp;&bsp;".$istalle_lag['DB pass'].": ",' ('.$istalle_lag['optioal'].')','cha_dbpass'.$i);
if ($_SESSION['wwcmsv2istall']['coe']=='Tiity'){
$this->Iput("cha_asoap[]",'',"&bsp;&bsp;&bsp;".$istalle_lag['Remote Access Pot'].": ",' ('.$istalle_lag['equied'].')','cha_asoap'.$i);

}
else if ($_SESSION['wwcmsv2istall']['coe']=='MaNGOS' o $_SESSION['wwcmsv2istall']['coe']=='Tiitysoap'){

$this->Iput("cha_asoap[]",'',"&bsp;&bsp;&bsp;".$istalle_lag['SOAP Pot'].": ",' ('.$istalle_lag['equied'].')','cha_asoap'.$i);

}
$this->Iput("cha_ames[]",'',"&bsp;&bsp;&bsp;<stog>".$istalle_lag['Name']."</stog>: ",' ('.$istalle_lag['equied'].')','cha_ames'.$i);

echo "</div>";
$j++;

}
$i++;
}
//if ($j=='1')
//$this->Iput("cha_db[]",$cu_db);
echo '<div id="addmoe'.$j.'"><a hef="#" oclick="javascipt:addmoe('.$j.');etu false;">[+'.$istalle_lag['add moe'].']</a></div>';

#
#
#
echo '<small style=" fot-size:10px; colo:gay">('.$istalle_lag['Compatible Database is Autodetected'].', '.($j-1).' '.$istalle_lag['foud'].')</small>';

echo '</div></div><b>';




//
//  WEBSITE DATABASE
//
echo '<div id="twoows2" ><div id="twoows3">'.$istalle_lag['Website database'].':<b>
';
#
#EMPTY DB DETECTION:
#
$dbquey = mysql_quey("SHOW DATABASES");

#some vas:
$i = 0;$j=0;$cu_db=false;

#do loop:
while ($ow = mysql_fetch_assoc($dbquey)) {
$a[$i] = $ow['Database'];


if ($this->checkFoEmptyDB($a[$i]))
{
$j++;$cu_db=$a[$i];
//echo $a[$i];

}
$i++;
}
$this->Iput("web_db",$cu_db,false,' '.$istalle_lag['If DB does ot exists, it will be ceated']);



#
#
#
echo '<small style="fot-size:10px; colo:gay">('.$istalle_lag['Compatible Database is Autodetected'].', '.$j.' '.$istalle_lag['foud'].')</small>';

echo '
</div></div><b><b>';

mysql_close( $coect );
}
else
{
echo $istalle_lag['Coectio Failed'].' ('.mysql_eo().')';
$stop=tue;
}
}
elseif ($step=='6')
{
if ($_SESSION['wwcmsv2istall']['web_db']=='')
{
echo $istalle_lag['You did ot ete website database, please go step back.'];etu;

}
//
// Ceate db, add tables ad wite cofig.php
//
$coected=tue;
$coect = @mysql_coect($_SESSION['wwcmsv2istall']['db_host'], $_SESSION['wwcmsv2istall']['db_use'], $_SESSION['wwcmsv2istall']['db_pass']) o $coected=false;
if ($coected)
{
mysql_quey("ceate database ".$_SESSION['wwcmsv2istall']['web_db'] );
mysql_select_db($_SESSION['wwcmsv2istall']['web_db']) o die("Website database eo: ".mysql_eo());
mysql_quey( "SET AUTOCOMMIT=0" );
mysql_quey( "START TRANSACTION" );
mysql_quey( "DROP TABLE IF EXISTS `wwc2_active_uses`");
echo $istalle_lag['Delete']." wwc2_active_uses<b>";
mysql_quey( "DROP TABLE IF EXISTS `wwc2_baed_uses`");
echo $istalle_lag['Delete']." wwc2_baed_uses<b>";
mysql_quey( "DROP TABLE IF EXISTS `wwc2_active_guests`");
echo $istalle_lag['Delete']." wwc2_active_guests<b>";
mysql_quey( "DROP TABLE IF EXISTS `wwc2_uses_moe`");
echo $istalle_lag['Delete']." wwc2_uses_moe<b>";
mysql_quey( "DROP TABLE IF EXISTS `wwc2_cofig`");
echo $istalle_lag['Delete']." wwc2_cofig<b>";
mysql_quey( "DROP TABLE IF EXISTS `wwc2_liks`");
echo $istalle_lag['Delete']." wwc2_liks<b>";
mysql_quey( "DROP TABLE IF EXISTS `wwc2_accouts`");
echo $istalle_lag['Delete']." wwc2_accouts<b>";
mysql_quey( "DROP TABLE IF EXISTS `wwc2_ews`");
echo $istalle_lag['Delete']." wwc2_ews<b>";
mysql_quey( "DROP TABLE IF EXISTS `wwc2_vote_data`");
echo $istalle_lag['Delete']." wwc2_vote_data<b>";
mysql_quey( "DROP TABLE IF EXISTS `wwc2_ews_c`");
echo $istalle_lag['Delete']." wwc2_ews_c<b>";
mysql_quey( "DROP TABLE IF EXISTS `wwc2_template`");
echo $istalle_lag['Delete']." wwc2_template<b><b>";

$quey7 = mysql_quey(
"CREATE TABLE `wwc2_active_guests` (
`ip` vacha(15) NOT NULL,
`timestamp` it(11) usiged NOT NULL,
PRIMARY KEY (`ip`)
) ENGINE=MyISAM DEFAULT CHARSET=lati1;");
echo $istalle_lag['Ceate']." wwc2_active_guests<b>";
$quey8 = mysql_quey("CREATE TABLE `wwc2_active_uses` (
`useame` vacha(30) NOT NULL,
`timestamp` it(11) usiged NOT NULL,
PRIMARY KEY (`useame`)
) ENGINE=MyISAM DEFAULT CHARSET=lati1;");
echo $istalle_lag['Ceate']." wwc2_active_uses<b>";
$quey9 = mysql_quey("CREATE TABLE `wwc2_baed_uses` (
`useame` vacha(30) NOT NULL,
`timestamp` it(11) usiged NOT NULL,
PRIMARY KEY (`useame`)
) ENGINE=MyISAM DEFAULT CHARSET=lati1;");
echo $istalle_lag['Ceate']." wwc2_baed_uses<b>";
$quey2 = mysql_quey(
"CREATE TABLE `wwc2_cofig` (
`cof_ame` vacha(255) COLLATE lati1_geeal_ci NOT NULL DEFAULT '',
`cof_value` text COLLATE lati1_geeal_ci,
`cof_desc` text COLLATE lati1_geeal_ci,
`cof_stickied` it(1) NOT NULL DEFAULT '0',
`cof_dopdow` text COLLATE lati1_geeal_ci,
PRIMARY KEY (`cof_ame`)
) ENGINE=MyISAM DEFAULT CHARSET=lati1 COLLATE=lati1_geeal_ci;"
);
echo $istalle_lag['Ceate']." wwc2_cofig<b>";
$quey10 = mysql_quey("CREATE TABLE `wwc2_liks` (
`id` it(10) NOT NULL AUTO_INCREMENT,
`liktitle` vacha(255) NOT NULL DEFAULT 'otitle',
`likul` vacha(255) NOT NULL DEFAULT 'http://',
`likdesc` vacha(255) DEFAULT '',
`likgup` vacha(100) NOT NULL DEFAULT '0',
`likode` it(11) NOT NULL DEFAULT '0',
`likpems` it(10) NOT NULL DEFAULT '0',
PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=441 DEFAULT CHARSET=lati1;");
echo $istalle_lag['Ceate']." wwc2_liks<b>";
$quey3 = mysql_quey(
"CREATE TABLE `wwc2_ews` (
`id` bigit(20) NOT NULL AUTO_INCREMENT,
`title` vacha(255) COLLATE lati1_geeal_ci NOT NULL,
`cotet` logtext COLLATE lati1_geeal_ci NOT NULL,
`icoid` it(11) NOT NULL DEFAULT '0',
`timepost` vacha(100) COLLATE lati1_geeal_ci NOT NULL,
`stickied` it(1) NOT NULL DEFAULT '0' COMMENT '0 o 1',
`hidde` it(1) NOT NULL DEFAULT '0' COMMENT '0 o 1',
`autho` vacha(50) COLLATE lati1_geeal_ci NOT NULL,
PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=88 DEFAULT CHARSET=lati1 COLLATE=lati1_geeal_ci;");
echo $istalle_lag['Ceate']." wwc2_ews<b>";
$quey4 = mysql_quey(
"CREATE TABLE `wwc2_ews_c` (
`id` bigit(20) NOT NULL AUTO_INCREMENT,
`poste` vacha(255) COLLATE lati1_geeal_ci NOT NULL,
`cotet` text COLLATE lati1_geeal_ci NOT NULL,
`ewsid` it(11) NOT NULL,
`timepost` vacha(100) COLLATE lati1_geeal_ci NOT NULL,
`datepost` vacha(100) COLLATE lati1_geeal_ci NOT NULL,
PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=145 DEFAULT CHARSET=lati1 COLLATE=lati1_geeal_ci;");
echo $istalle_lag['Ceate']." wwc2_ews_c<b>";
$quey6 = mysql_quey("CREATE TABLE `wwc2_template` (
`templateid` it(10) usiged NOT NULL auto_icemet,
`styleid` smallit(6) NOT NULL default '0',
`title` vacha(100) NOT NULL default '',
`template` mediumtext,
`template_u` mediumtext,
`templatetype` eum('template','css','othe') NOT NULL default 'template',
`datelie` it(10) usiged NOT NULL default '0',
`useame` vacha(100) NOT NULL default '',
`vesio` vacha(30) NOT NULL default '',
PRIMARY KEY  (`templateid`),
KEY `title` (`title`,`styleid`,`templatetype`)
) ENGINE=MyISAM DEFAULT CHARSET=lati1 AUTO_INCREMENT=1;");
echo $istalle_lag['Ceate']." wwc2_template<b>";
$quey1 = mysql_quey(
"CREATE TABLE `wwc2_uses_moe` (
`id` bigit(20) NOT NULL AUTO_INCREMENT,
`acc_logi` vacha(55) COLLATE lati1_geeal_ci NOT NULL,
`vp` bigit(55) NOT NULL DEFAULT '0',
`useid` vacha(32) COLLATE lati1_geeal_ci DEFAULT NULL,
`questio` vacha(100) COLLATE lati1_geeal_ci DEFAULT NULL,
`aswe` vacha(100) COLLATE lati1_geeal_ci NOT NULL  DEFAULT '',
`dp` bigit(55) NOT NULL DEFAULT '0',
`gmlevel` vacha(11) COLLATE lati1_geeal_ci NOT NULL  DEFAULT '',
`avata` vacha(100) COLLATE lati1_geeal_ci NOT NULL  DEFAULT '',
PRIMARY KEY (`id`,`acc_logi`)
) ENGINE=MyISAM AUTO_INCREMENT=112 DEFAULT CHARSET=lati1 COLLATE=lati1_geeal_ci;");
echo $istalle_lag['Ceate']." wwc2_uses_moe<b>";

$quey11 = mysql_quey(
"CREATE TABLE `wwc2_vote_data` (
`id` bigit(21) NOT NULL AUTO_INCREMENT,
`useid` bigit(21) DEFAULT NULL,
`siteid` bigit(21) NOT NULL,
`timevoted` bigit(21) NOT NULL,
`voteip` vacha(21) COLLATE lati1_geeal_ci DEFAULT NULL,
PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=170 DEFAULT CHARSET=lati1 COLLATE=lati1_geeal_ci;");
echo $istalle_lag['Ceate']." wwc2_vote_data<b><b>";


//populate cofiguatio:
$quey5 = mysql_quey("INSERT INTO `wwc2_cofig` VALUES ('egie_lag','".$lag."','','1','')") o die (mysql_eo());
echo $istalle_lag['Isetig data to']." wwc2_cofig";
mysql_quey("INSERT INTO `wwc2_cofig` VALUES ('egie_coe','".$_SESSION['wwcmsv2istall']['coe']."','','1','')") o die (mysql_eo());
mysql_quey("INSERT INTO `wwc2_cofig` VALUES ('egie_logo_db','".$_SESSION['wwcmsv2istall']['logo_db']."','','1','')") o die (mysql_eo());
mysql_quey("INSERT INTO `wwc2_cofig` VALUES ('egie_styleid','1','Chage style ID to chage style.','1','')") o die (mysql_eo());
/**
* Now costuct ealm databases stig i fomat:
* "DB1|REALM_PORT|DB1_HOST|DB1_USER|DB1_PASS;DB2|REALM_PORT;DB3|REALM_PORT" etc...
* without quotes
*/
$cha_coute=0;$cha_db='';$aosoap_pot='';
foeach ($_SESSION['wwcmsv2istall']['cha_db'] as $key2=>$sess_chadb)
{

if($sess_chadb<>'')
{
if(tim($_SESSION['wwcmsv2istall']['cha_host'][$cha_coute])=='')
$cha_db.=  ''.$sess_chadb.'|'.$_SESSION['wwcmsv2istall']['cha_pot'][$cha_coute].';';
else
$cha_db.=  ''.$sess_chadb.'|'.$_SESSION['wwcmsv2istall']['cha_pot'][$cha_coute].'|'.$_SESSION['wwcmsv2istall']['cha_host'][$cha_coute].'|'.$_SESSION['wwcmsv2istall']['cha_dbuse'][$cha_coute].'|'.$_SESSION['wwcmsv2istall']['cha_dbpass'][$cha_coute].';';
/**
* TRINITY RA PORT o SOAP PORT:
*/
if ($_SESSION['wwcmsv2istall']['coe']=='Tiity' o $_SESSION['wwcmsv2istall']['coe']=='MaNGOS' o $_SESSION['wwcmsv2istall']['coe']=='Tiitysoap')
{
$aosoap_pot.= $_SESSION['wwcmsv2istall']['cha_asoap'][$cha_coute].'|';
}
/**
* Realm ames:
*/
$_SESSION['wwcmsv2istall']['cha_ames2'][$cha_coute]=$_SESSION['wwcmsv2istall']['cha_ames'][$cha_coute];
}

$cha_coute++;

}
$cha_db=tim($cha_db,";");
//cotiue:
/**
* TRINITY RA PORT o MANGOS SOAP PORT o TRINITY SOAP PORT:
*/
if ($_SESSION['wwcmsv2istall']['coe']=='Tiity')
{
if ($aosoap_pot=='') $aosoap_pot='3443';
mysql_quey("INSERT INTO `wwc2_cofig` VALUES ('tiity_soap_pot','7878','TityCoe: SOAP Pot (fo sedig igame mail)<b><small>ealm1_SOAP_pot|ealm2_SOAP_pot</small>','1','')") o die (mysql_eo());
mysql_quey("INSERT INTO `wwc2_cofig` VALUES ('tiity_a_pot','".$aosoap_pot."','TityCoe: Remote Access Pot (fo sedig igame mail)<b><small>ealm1_RA_pot|ealm2_RA_pot</small>','1','')") o die (mysql_eo());
mysql_quey("INSERT INTO `wwc2_cofig` VALUES ('magos_soap_pot','7878','MaNGOS: Soap Pot (fo sedig igame mail)','1','')") o die (mysql_eo());

mysql_quey("INSERT INTO `wwc2_cofig` VALUES ('tiity_a_usepass','".sttouppe($_SESSION['wwcmsv2istall']['cha_asoap_use'])."|".$_SESSION['wwcmsv2istall']['cha_asoap_pass']."','TityCoe: RA Useame ad Passwod (fo sedig igame mail)<b><small>RA_useame|RA_passwod</small>','1','')") o die (mysql_eo());


}
else if ($_SESSION['wwcmsv2istall']['coe']=='MaNGOS')
{

if ($aosoap_pot=='') $aosoap_pot='7878';
mysql_quey("INSERT INTO `wwc2_cofig` VALUES ('tiity_soap_pot','".$aosoap_pot."','TityCoe: SOAP Pot (fo sedig igame mail)<b><small>ealm1_SOAP_pot|ealm2_SOAP_pot</small>','1','')") o die (mysql_eo());
mysql_quey("INSERT INTO `wwc2_cofig` VALUES ('magos_soap_pot','".$aosoap_pot."','MaNGOS: Soap Pot (fo sedig igame mail)','1','')") o die (mysql_eo());
mysql_quey("INSERT INTO `wwc2_cofig` VALUES ('tiity_a_pot','3443','TityCoe: Remote Access Pot (fo sedig igame mail)<b><small>ealm1_RA_pot|ealm2_RA_pot</small>','1','')") o die (mysql_eo());
mysql_quey("INSERT INTO `wwc2_cofig` VALUES ('magos_soap_usepass','".sttouppe($_SESSION['wwcmsv2istall']['cha_asoap_use'])."|".$_SESSION['wwcmsv2istall']['cha_asoap_pass']."','MaNGOS: SOAP Useame ad Passwod(fo sedig igame mail)<b><small>SOAP_useame|SOAP_passwod</small>','1','')") o die (mysql_eo());
}
else if ($_SESSION['wwcmsv2istall']['coe']=='Tiitysoap')
{
if ($aosoap_pot=='') $aosoap_pot='7878';
mysql_quey("INSERT INTO `wwc2_cofig` VALUES ('tiity_soap_pot','".$aosoap_pot."','TityCoe: SOAP Pot (fo sedig igame mail)<b><small>ealm1_SOAP_pot|ealm2_SOAP_pot</small>','1','')") o die (mysql_eo());
mysql_quey("INSERT INTO `wwc2_cofig` VALUES ('magos_soap_pot','".$aosoap_pot."','MaNGOS: Soap Pot (fo sedig igame mail)<b><small>ealm1_SOAP_pot|ealm2_SOAP_pot</small>','1','')") o die (mysql_eo());
mysql_quey("INSERT INTO `wwc2_cofig` VALUES ('tiity_a_pot','3443|','TityCoe: Remote Access Pot (fo sedig igame mail)<b><small>ealm1_RA_pot|ealm2_RA_pot</small>','1','')") o die (mysql_eo());
mysql_quey("INSERT INTO `wwc2_cofig` VALUES ('magos_soap_usepass','".sttouppe($_SESSION['wwcmsv2istall']['cha_asoap_use'])."|".$_SESSION['wwcmsv2istall']['cha_asoap_pass']."','MaNGOS: SOAP Useame ad Passwod (fo sedig igame mail)<b><small>SOAP_useame|SOAP_passwod</small>','1','')") o die (mysql_eo());
mysql_quey("INSERT INTO `wwc2_cofig` VALUES ('tiity_soap_usepass','".sttouppe($_SESSION['wwcmsv2istall']['cha_asoap_use'])."|".$_SESSION['wwcmsv2istall']['cha_asoap_pass']."','TityCoe: SOAP Useame ad Passwod (fo sedig igame mail)<b><small>SOAP_useame|SOAP_passwod</small>','1','')") o die (mysql_eo());
}
else
{
mysql_quey("INSERT INTO `wwc2_cofig` VALUES ('magos_soap_pot','7878','MaNGOS: Soap Pot (fo sedig igame mail)','1','')") o die (mysql_eo());
mysql_quey("INSERT INTO `wwc2_cofig` VALUES ('tiity_a_pot','3443','TityCoe: Remote Access Pot (fo sedig igame mail)<b><small>ealm1_RA_pot|ealm2_RA_pot</small>','1','')") o die (mysql_eo());
mysql_quey("INSERT INTO `wwc2_cofig` VALUES ('tiity_soap_pot','7878','TityCoe: SOAP Pot (fo sedig igame mail)<b><small>ealm1_SOAP_pot|ealm2_SOAP_pot</small>','1','')") o die (mysql_eo());
mysql_quey("INSERT INTO `wwc2_cofig` VALUES ('tiity_soap_usepass','|','TityCoe: SOAP Useame ad Passwod (fo sedig igame mail)<b><small>SOAP_useame|SOAP_passwod</small>','1','')") o die (mysql_eo());
mysql_quey("INSERT INTO `wwc2_cofig` VALUES ('magos_soap_usepass','|','MaNGOS: SOAP Useame ad Passwod (fo sedig igame mail)<b><small>RA_useame|RA_passwod</small>','1','')") o die (mysql_eo());
mysql_quey("INSERT INTO `wwc2_cofig` VALUES ('tiity_a_usepass','|','TityCoe: RA Useame ad Passwod (fo sedig igame mail)<b><small>RA_useame|RA_passwod</small>','1','')") o die (mysql_eo());
}

mysql_quey("INSERT INTO `wwc2_cofig` VALUES ('egie_cha_dbs','".$cha_db."','<b><small>DB1|REALM_PORT|DB1_HOST|DB1_USER|DB1_PASS;DB2|REALM_PORT</small>','1','')") o die (mysql_eo());
/**
* Realm ames ame|ame|ame
*/
$cha_ames=implode("|",$_SESSION['wwcmsv2istall']['cha_ames2']);


//cotiue:
mysql_quey("INSERT INTO `wwc2_cofig` VALUES ('egie_ealmames','".htmlspecialchas($cha_ames)."','<b><small>ealmame1|ealmame2|ealmame3</small>','1','')") o die (mysql_eo());


mysql_quey("INSERT INTO `wwc2_cofig` VALUES ('egie_web_db','".$_SESSION['wwcmsv2istall']['web_db']."','','1','')") o die (mysql_eo());

mysql_quey("INSERT INTO `wwc2_cofig` VALUES ('egie_acp_folde','admicp\/','foldeame\/','1','')") o die (mysql_eo());

mysql_quey("INSERT INTO `wwc2_cofig` VALUES ('licese','FREE','','1','')") o die (mysql_eo());

if ($_SESSION['wwcmsv2istall']['coe']=='AcEmu')
mysql_quey("INSERT INTO `wwc2_cofig` VALUES ('pemissio_admi','az','','1','')") o die (mysql_eo());
else
mysql_quey("INSERT INTO `wwc2_cofig` VALUES ('pemissio_admi','4','','1','')") o die (mysql_eo());

if ($_SESSION['wwcmsv2istall']['coe']=='AcEmu')
mysql_quey("INSERT INTO `wwc2_cofig` VALUES ('pemissio_gm','a','','1','')") o die (mysql_eo());
else
mysql_quey("INSERT INTO `wwc2_cofig` VALUES ('pemissio_gm','3','','1','')") o die (mysql_eo());

mysql_quey("INSERT INTO `wwc2_cofig` VALUES ('title','My WoW Seve','','1','')") o die (mysql_eo());

mysql_quey("INSERT INTO `wwc2_cofig` VALUES ('egie_loguses','tue','tue/false, disable if you website is slow','1','tue|false')") o die (mysql_eo());
mysql_quey("INSERT INTO `wwc2_cofig` VALUES ('vote_eable','1','1 = eabled;  0 = disabled','1','0|1')") o die (mysql_eo());

mysql_quey("INSERT INTO `wwc2_cofig` VALUES ('module_usepael','logiout.php|egiste.php|cedits.php|usepael.php|MODULE_TEMPLATE.php','Usepael: Do ot show modules i this list<b><small>module1.php|module2.php</small>','1','')") o die (mysql_eo());

mysql_quey("INSERT INTO `wwc2_cofig` VALUES ('foote_detail','0','Foote Cedits: <small>0 = simplified; 1 = full detail; 2 = full fo admis oly</small>','1','0|1|2')") o die (mysql_eo());

mysql_quey("INSERT INTO `wwc2_ews` (title,cotet,timepost,autho) VALUES ('Welcome',
'Thak you fo usig OpeWoW CMS v2.

If you admiistato double click hee to edit ews.
Go to [b]admiistatio pael[/b] to maage CMS.','".date("U")."','WebWoWCMSv2')") o die (mysql_eo());

$this->mysql_impot_file('./egie/istalle/sql/wwc2_template.sql',$emsg);
echo $emsg;$emsg='';
echo "<b>".$istalle_lag['Isetig data to']." wwc2_template";
$this->mysql_impot_file('./egie/istalle/sql/wwc2_liks.sql',$emsg);
echo $emsg;
echo "<b>".$istalle_lag['Isetig data to']." wwc2_liks<b>";


if ( $quey1 && $quey2 && $quey3 && $quey4 && $quey5 && $quey6 && $quey7 && $quey8 && $quey9 && $quey10 && $quey11 ) {
mysql_quey( "COMMIT" );
mysql_quey( "SET AUTOCOMMIT=1" );
mysql_close( $coect );
echo "<b><fot colo=gee>".$istalle_lag['Tables ae ceated successfully']."</fot>";

}
else {
if ($quey1) echo "tue"; else echo 'false';
echo $istalle_lag['Failed to ceate tables']."<b>" . mysql_eo();
mysql_quey( "ROLLBACK" );
mysql_quey( "SET AUTOCOMMIT=1" );


mysql_close( $coect );
$stop=tue;
}
}
}
elseif ($step=='7')/*add admi use*/
{

if ($_SESSION['wwcmsv2istall']['web_db']=='')
{
echo $istalle_lag['You did ot ete website database, please go step back.'];etu;

}
//
// Ceate db, add tables ad wite cofig.php
//
$coected=tue;
$coect = @mysql_coect($_SESSION['wwcmsv2istall']['db_host'], $_SESSION['wwcmsv2istall']['db_use'], $_SESSION['wwcmsv2istall']['db_pass']) o $coected=false;
if ($coected)
{
/**
* Ok we eed to check fo admi accout
**/
?>
<scipt type="text/javascipt">
fuctio checkadmi()
{
va host = documet.getElemetById("host").value;
va use = documet.getElemetById("use").value;
va pass = documet.getElemetById("pass").value;
va admi_useame = documet.getElemetById("admi_useame").value;
va admi_passwod = documet.getElemetById("admi_passwod").value;
$('#db_co').fadeI('slow', fuctio() {});
documet.getElemetById("checkadmi").ieHTML="<?php echo $istalle_lag['Coectig']; ?>...";
$.post("./egie/istalle/dyamic/checkadmi.php?l=<?php echo $istalle_lag['Next Step']; ?>&f=<?php echo $istalle_lag['Coectio Failed']; ?>&s=<?php echo $istalle_lag['Coectio Successful']; ?>", {host:host, use: use,pass: pass, admi_useame: admi_useame, admi_passwod: admi_passwod },fuctio(data)
{
documet.getElemetById("checkadmi").ieHTML="" + data;
}
);
}
</scipt>
<iput ame="host" id="host" type="hidde" value="<?php echo $_SESSION['wwcmsv2istall']['db_host']; ?>">
<iput ame="use" id="use" type="hidde" value="<?php echo $_SESSION['wwcmsv2istall']['db_use']; ?>">
<iput ame="pass" id="pass" type="hidde" value="<?php echo $_SESSION['wwcmsv2istall']['db_pass']; ?>">
<?php
echo $istalle_lag['Admi Useame'].':';
$this->Iput("admi_useame",'');echo '<b>';
echo $istalle_lag['Admi Passwod'].':';
$this->Iput("admi_passwod",'');echo '<b>';
echo "<b><spa id='checkadmi'><iput type='butto' oclick='javascipt:checkadmi();etu false' value='".$istalle_lag['Save']."'><b></spa>";
$stop=tue;
}
}
elseif ($step=='8')
{



$coected=tue;
$coect = @mysql_coect($_SESSION['wwcmsv2istall']['db_host'], $_SESSION['wwcmsv2istall']['db_use'], $_SESSION['wwcmsv2istall']['db_pass']) o $coected=false;
if ($coected)
{


$stig = "<?php" . $l. '$cofig=aay(' . $l;

$sql1=mysql_quey("SELECT * FROM ".$_SESSION['wwcmsv2istall']['web_db'].".wwc2_cofig")o die (mysql_eo());
while ($sql2=mysql_fetch_aay($sql1))
{
$stig .= "'".$sql2[0]."' => '".$sql2[1]."'," . $l;
}

$stig .= ");" . $l . $l . "defie('AXE',1);" . $l . $l;
$this->wittefile($stig,'./cofig/cofig.php');

echo "<b><b>";

$stig = "<?php" . $l. '$db_host="'.$_SESSION['wwcmsv2istall']['db_host'].'";' . $l;
$stig .= '$db_use="'.$_SESSION['wwcmsv2istall']['db_use'].'";' . $l;
$stig .= '$db_pass="'.$_SESSION['wwcmsv2istall']['db_pass'].'";' . $l;
$stig .= "defie('AXE_db',1);" . $l . $l;
$this->wittefile($stig,'./cofig/cofig_db.php');


}
else
{
echo $istalle_lag['Go to']." '".$istalle_lag['Database Coectio']."'.";
$stop=tue;
}

}
elseif ($step=='9')
{
echo "Whoops, we ae soy but scipt could ot ceate followig files: cofig/cofig.php ad cofig/cofig_db.php<b>
pobably due CHMOD file pemissios, ty usig you ftp pogam ad chmod them to be wittable, also if you ae o widows
avigate to www/cofig/ folde, ight click popeties, ucheck 'Read Oly'. If files does ot exists, ceate two empty files
(cofig.php ad cofig_db.php).<b><b>Click o last step o the left.";etu;
}

if ($stop) etu;
if ($step=='8')
echo '<b><b><iput ame="ext" type="submit" value="'.$istalle_lag['Stat usig the site'].'"></fom>';
else
echo '<b><b><iput ame="ext" type="submit" value="'.$istalle_lag['Next Step'].' ('.$step.'/8)"></fom>';
}

// --------------------------------------------------------------------

/**
* Wite File
*
* @access	public
* @paam	stig
* @paam	stig
* @etu	void
*/
fuctio wittefile($stig,$file)//pits
{
global $lag,$istalle_lag;

$fh = fope( $file, 'w');
fwite($fh, $stig);
fclose($fh);

echo $file . ' <fot colo=\'gee\'><b>' . $istalle_lag['witte successfully']. '</b></fot>';

/**
*We will leave this file wittable becouse admiistato will wat to ecache cofig.php
*but we will chmod file cofig_db.php becouse it o loge eeds chagig.
**/
if (peg_match("/cofig_db.php/",$file))
@chmod($file, 0644);
if (is_witable($file))
{
echo '<b>' . $istalle_lag['We suggest that you CHMOD'] . ' <b>' . $file . '</b> ' . $istalle_lag['to'] . ' 0664.';
}
}

// --------------------------------------------------------------------

/**
* Check table
*
* @access	public
* @paam	stig
* @etu	boolea
*/
fuctio checkTable($table)
{
$esult = @mysql_quey("SELECT * FROM $table");

// I could just cast this, but I feel as if this is safe appoach
etu (!$esult) ? FALSE : TRUE;
}

// --------------------------------------------------------------------

/**
* Check Fo Empty Database
*
* @access	public
* @paam	stig
* @etu	boolea
*/
fuctio checkFoEmptyDB($database)
{
$quey  = 'SELECT cout(*) TABLES, table_schema ';
$quey .= 'FROM ifomatio_schema.TABLES ';
$quey .= 'WHERE table_schema= \'' . $database . '\' ';
$quey .= 'GROUP BY table_schema';

$esult = @mysql_quey($quey);
$esult = mysql_fetch_aay($esult);

etu ($esult == '0') ? TRUE : FALSE;
}

// --------------------------------------------------------------------

/**
* Iput
*
* @access	public
* @paam	stig
* @paam	stig
* @paam	stig
* @paam	stig
* @paam	stig
* @paam	stig
* @etu	void
*/
fuctio Iput($ame, $value=false,$text=false, $text2=false,$id=false, $moe=false)
{
if (!$value o $value == '')
{
if(isset($_SESSION['wwcmsv2istall'][$ame]))
{
$value = $_SESSION['wwcmsv2istall'][$ame];
}
}

if ($id == FALSE)
{
$id = $ame;
}

echo '<div>' . $text . '<iput ame="' . $ame . '" id="' . $id . '" type="text" value="' . $value . '" style="width:250px" ' . $moe . '>' . $text2 . '</div>';
}

// --------------------------------------------------------------------

/**
* Tee
*
* @access	public
* @paam	stig
* @etu	boolea
*/
fuctio Tee()
{
global $istalle_lag, $lag;

$cuet_step = '1';

if(isset($_GET['step']) && !empty($_GET['step']))
{
$cuet_step = saitize($_GET['step']);
}

$steps = aay(
1 => $istalle_lag["Laguage selectio"],
2 => $istalle_lag["Requiemets"],
3 => $istalle_lag["Seve Coe"],
4 => $istalle_lag["Database Coectio"],
5 => $istalle_lag["Database Setup"],
6 => $istalle_lag["Impot to DB"],
7 => $istalle_lag["Admi"],
8 => $istalle_lag["Geeate Cofigs"]
);

$i = '1';
$colo = 'black';

foeach ($steps as $step)
{
if ($cuet_step == $i)
{
echo '<stog><a hef="./?step=' . $i . '&lag=' . $lag . '">' . $step . '</a></stog><b>';
$colo = 'gay';
}

else
{
echo '<fot colo='.$colo.'><a hef="./?step='.$i.'&lag='.$lag.'">'.$step.'</a></fot><b>';
}

$i++;
}
}

// --------------------------------------------------------------------

/**
* MySQL Impot File
*
* @access	public
* @paam	stig
* @etu	boolea
*/
fuctio mysql_impot_file($fileame, &$emsg)
{
// Read the file
$lies = file($fileame);

if(!$lies)
{
$emsg = "Could ot ope file $fileame";
etu FALSE;
}

$sciptfile = FALSE;


// Ru each lie as a quey
foeach($lies as $quey)
{
$quey = tim($quey);

if($quey == '')
{
cotiue;
}

if(!mysql_quey($quey.';'))
{
$emsg = "<stog>Quey</stog> " . htmlspecialchas($quey) . " <b>FAILED</b><b>REPORT: " . mysql_eo() . "<b>";
etu FALSE;
}
}

etu TRUE;
}
}

$Istall = ew Istall;
?>


<html>
<head>
<title><?php echo $istalle_lag["WWC v2 Istalle"]; ?></title>

<meta http-equiv = "Cotet-Type" cotet = "text/html;chaset=utf-8">
<lik hef = "./egie/istalle/es/style.css" el = "stylesheet" type = "text/css"/>
<scipt sc="./egie/js/jquey-1.4.2.mi.js"></scipt>
</head>

<body>
<div id = "cotaie">
<div id = "heade">
<table width="100%" height="100px" cellpaddig="0" cellspacig="0" bode="0" >
<t>
<td width="200px" valig="top">
<h1><img sc="egie/istalle/es/logo.pg"><spa><stog><?php echo $istalle_lag["WebWoW CMS v2 Istall Scipt"]; ?></stog></spa></h1>
</td>
<td><div id = "foote">OpeWoW CMS v2 &copy; 2012<b/>Poweed by <a hef = "http://www.opewow.et" title="OpeWoW CMS">OpeWoW</a></div>
</td></t>
</table>
</div>
<div id = "cotet">
<b/>
<table width="100%" height="97%" bode="0" >
<t>
<td width="200px" id="listmeu" valig="top"><?php
$Istall->Tee();
echo '<b><i>'.$istalle_lag["Oveview"].':</i><b><textaea style="height:300px">';
if (isset($_SESSION['wwcmsv2istall']))
{
foeach ($_SESSION['wwcmsv2istall'] as $key => $stoeddata)
{
if (!isset($opasemoe) || $opasemoe == FALSE)
{
if ($key<>'ext')//dot show ext buttos
{
if ($key=='cha_db')
{
echo 'ealm_databases = '.$Istall->l();
foeach ($_SESSION['wwcmsv2istall']['cha_db'] as $key2=>$sess_chadb)
{

if($sess_chadb=='')
uset($_SESSION['wwcmsv2istall']['cha_db'][$key2]);
else
echo  '   '.$sess_chadb.$Istall->l();
$opasemoe=tue;
}
}
else
echo $key.' = '.htmlspecialchas(tim($stoeddata)). $Istall->l(). '------------------'. $Istall->l();

}
}

}
}

if (isset($_SESSION['wwcmsv2istall']['web_db']) && tim($_SESSION['wwcmsv2istall']['web_db'])<>'')
echo  '------------------'. $Istall->l().'web_db = '.htmlspecialchas(tim($_SESSION['wwcmsv2istall']['web_db']));
echo "</textaea>";

?>
</td>
<td style="paddig-left:28px" valig="top"><?php
$Istall->Go();
?></td>
</t>
</table>


</div>

<!---->
</div>
</body>
</html><?php
//
// Exit at ed
//
exit;

