<?php
/************************************************************************
*													 engine/func/nicetime.php
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

function nicetime($date)
{
	global $lang;
		if(empty($date)) {
				return "No date provided";
		}

		$periods         = array($lang["second"], $lang["minute"], $lang["hour"], $lang["day"], $lang["week"], $lang["month"], $lang["year"], $lang["decade"]);
		$lengths         = array("60","60","24","7","4.35","12","10");

	$now             = time();
	$unix_date = $date;

			 // check validity of date
		if(empty($unix_date)) {
				return "Bad date";
		}

		// is it future date or past date
		if($now > $unix_date) {
				$difference     = $now - $unix_date;
				$tense         = $lang["ago"];
		} else {
				$difference     = $unix_date - $now;
				$tense         = $lang["from now"];
		}

		for($j = 0; $difference >= $lengths[$j] && $j < count($lengths)-1; $j++) {
				$difference /= $lengths[$j];
		}

		$difference = round($difference);

		if($difference != 1) {
				$periods[$j].= $lang["s"];
		}

		return "$difference $periods[$j] {$tense}";
}
?>