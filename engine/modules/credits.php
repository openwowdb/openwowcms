<?php 
global $Html,$form,$lang_admincp,$lang,$config;

if (!$lang_admincp) include_once(PATHROOT."engine/lang/".strtolower($config['engine_lang'])."/admincp.php");
include_once(PATHROOT.$config['engine_acp_folder']."defines.php");
/**
* This part of website is executed before any output is given
* so every post data is processed here then using Header(Location:)
* we simply call normal site and display errors
**/
if(isset($proccess) && $proccess == TRUE){
	/**
    * Processes the user submitted login form, if errors
    * are found, the user is redirected to correct the information,
    * if not, the user is effectively logged in to the system.
	* If user is logged in, he will be logged out and redirected to 
	* index.php page.
    */
	function Process(){
	   global $user, $form;
	   
	  /* Login attempt */
      $retval = $user->login($_POST['user'], $_POST['pass'], isset($_POST['remember']));
      /* Login failed */
      if(!$retval){
         $_SESSION['value_array'] = $_POST;
         $_SESSION['error_array'] = $form->getErrorArray();
      }
	  else
	  	header("Location: index.php");
	 
	 
	}
	
	if (isset($_POST['sublogin'])){
		/* Initialize process */
		Process();
	}
	else
	{
		//add code if any
	}
	
	/* Reinitilaze 'form' proccess with latest session data */
	$form->_Form();
	return;
	
}


?>
<!-- This element is important, must be at beginning of module output, dont change it, except module name -->
<div class="post_body_title"><?php echo $lang_admincp['Credits']; ?></div>
<?php
$Html->credits_cms(); 
?>