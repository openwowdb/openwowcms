<?php
/************************************************************************
*														 	 engine/vote.php
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

if($user->logged_in)
	include ("./engine/_cache/cache_vote_loggedin.php");
else
	include ("./engine/_cache/cache_vote_loggedout.php");
