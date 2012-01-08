<?php
session_start();
error_reporting(~E_NOTICE);
$fail=false;
if (!isset($_SESSION['wwcmsv2install'])) 
{
	echo 'No access.';session_destroy();exit;
}
function _htmlspecialchars($str)
{
	$str = preg_replace('/&(?!#[0-9]+;)/s', '&amp;', $str);
	$str = str_replace(array('<', '>', '"'), array('&lt;', '&gt;', '&quot;'), $str);
	return $str;
}


$con = @mysql_connect($_POST['host'], $_POST['user'], $_POST['pass']) or $fail=true;
@mysql_close( $con );
if ($fail)
echo '&nbsp;&nbsp;<font color="red">'._htmlspecialchars($_GET['f']).'</font> ('.mysql_error().")";
else
echo '<font color="green">'._htmlspecialchars($_GET['s']).'</font><br><br><input name="next" type="submit" value="'.$_GET['l'].' (4/8)"></form>';
?>
