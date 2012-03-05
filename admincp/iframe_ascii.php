<?php
/************************************************************************
*													 admincp/iframe_ascii.php
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
?>
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
