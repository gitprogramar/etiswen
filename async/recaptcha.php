<html><head><meta name="robots" content="noindex, nofollow"></head><body></body></html>
<?php	
		// Recaptcha V2	
		define( '_JEXEC', 1 );
		define('JPATH_ROOT', realpath(dirname(__FILE__).'/../') );
		require_once ( JPATH_ROOT .'/async/utils.php');	
	
		$version = JRequest::getVar('version', '', 'get');		
	
		$recaptcha = array();
		/* ReCaptcha Version 2 */
		if($version == 2) {
			JPluginHelper::importPlugin( 'captcha');
			$dispatcher=JDispatcher::getInstance();
			// This will return the array of HTML code.
			$recaptcha=$dispatcher->trigger('onDisplay', array(null, 'dynamic_recaptcha_1', 'class=""'));			
		}

		$response = array();
		$response["value"] = (isset($recaptcha[0])) ? $recaptcha[0] : 'Plugin not found or not configured';
		echo json_encode($response);
		return;
?>