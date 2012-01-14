<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>Iframe ascii converter - WWCMSv2</title>
</head>

<body style="background:#FFFFFF; font:Arial, Helvetica, sans-serif 12px">
<form action="iframe_ascii.php" method="post"><center><input name="char" type="text" style="width:70%" /></center>
<?php
if (isset($_POST['submit']))
{
	echo  '<center><input type="text" style="width:70%" value="'.htmlspecialchars($_POST['char']).'"/></center>';
	echo "<pre>".htmlspecialchars($_POST['char'])."</pre>";
}

?><center><input name="submit" type="submit" value="Convert String" /></center>

</form>
</body>
</html>
