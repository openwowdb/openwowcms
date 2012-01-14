<?php
###################################################################
# This file is a part of OpenWoW CMS by www.openwow.com
#
#   Project Owner    : OpenWoW CMS (http://www.openwow.com)
#   Copyright        : (c) www.openwow.com, 2010
#   Credits          : Based on work done by AXE and Maverfax
#   License          : GPLv3
##################################################################

if($user->logged_in)
	include ("./engine/_cache/cache_vote_loggedin.php");
else
	include ("./engine/_cache/cache_vote_loggedout.php");
