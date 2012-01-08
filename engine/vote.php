<?php
if($user->logged_in)
	include ("./engine/_cache/cache_vote_loggedin.php");
else
	include ("./engine/_cache/cache_vote_loggedout.php");
