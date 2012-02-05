<?php 
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