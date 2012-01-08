<?php
function nicetime($date)
{
	global $lang;
    if(empty($date)) {
        return "No date provided";
    }
   
    $periods         = array($lang["second"], $lang["minute"], $lang["hour"], $lang["day"], $lang["week"], $lang["month"], $lang["year"], $lang["decade"]);
    $lengths         = array("60","60","24","7","4.35","12","10");
   
    $now             = time();
    $unix_date         = strtotime($date);
   
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