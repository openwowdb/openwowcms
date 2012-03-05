<?php
/************************************************************************
*														engine/dynamic/status.php
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

// gets:
// ip, port
//
if (!isset($_GET['port']))
{
	echo "No port";
	exit;
}

function test_serv($port)
{
	if ($_GET['ip'] == '')
		$server = preg_replace("/[^a-zA-Z0-9.]/", "", $_GET['ip']);
	else
		$server = '127.0.0.1';
	$s = @fsockopen($server, $port, $ERROR_NO, $ERROR_STR, (float)0.5);
	if($s)
	{
		@fclose($s);
		return true;
	}
	return false;
}

$port = preg_replace( "/[^0-9]/", "", $_GET['port'] );
if (test_serv($port))
	echo '<span class="colorgood">Online</span>';
else
	echo '<span class="colorbad">Offline</span>';
