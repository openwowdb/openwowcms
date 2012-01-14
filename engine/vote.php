<?php
##################################################################
# This file is a part of OpenWoW CMS by www.openwow.co

#   Project Owner    : OpenWoW CMS (http://www.openwow.com
#   Copyright        : (c) www.openwow.com, 201
#   Credits          : Based on work done by AXE and Maverfa
#   License          : GPLv
#################################################################

if($user->logged_in)
	include ("./engine/_cache/cache_vote_loggedin.php");
else
	include ("./engine/_cache/cache_vote_loggedout.php");
