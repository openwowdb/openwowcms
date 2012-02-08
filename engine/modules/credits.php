<?php
/************************************************************************
*														engine/modules/credits.php
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
global $lang_admincp,$config;

if (!isset($lang_admincp)) include_once(PATHROOT."engine/lang/".strtolower($config['engine_lang'])."/admincp.php");
include_once(PATHROOT.$config['engine_acp_folder']."defines.php");
/**
* This part of website is executed before any output is given
* so every post data is processed here then using Header(Location:)
* we simply call normal site and display errors
**/
if(isset($proccess) && $proccess == TRUE){
	return;
}
?>
<!-- This element is important, must be at beginning of module output, dont change it, except module name -->
<div class="post_body_title"><?php echo $lang_admincp['Credits']; ?></div>
<?php
Html::credits_cms();
?>