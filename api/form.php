<?php	
		// Async Form logic		
		define( '_JEXEC', 1 );
		define('JPATH_ROOT', realpath(dirname(__FILE__).'/../') );
		require_once ( JPATH_ROOT .'/api/utils.php');	
	
		// environment
		JFactory::getApplication('site');
	
		/*
		$version = JRequest::getVar('version', '', 'get');		
	
		$recaptcha = array();		
		if($version == 2) {
			JPluginHelper::importPlugin( 'captcha');
			$dispatcher=JDispatcher::getInstance();
			// This will return the array of HTML code.
			$recaptcha=$dispatcher->trigger('onDisplay', array(null, 'dynamic_recaptcha_1', 'class=""'));			
		}
		
		echo (isset($recaptcha[0])) ? $recaptcha[0] : 'Plugin not found or not configured';
		return;
		*/
		//$path=getcwd(); 
		//echo "Your Absolute Path is: "; 
		//echo $path;

		// handle error codes
		/*
		1 = Ok
		0 = Error
		-1 = Captcha Error
		-2 = Required Field Error
		*/
		$returnCode = 1;
		$extraFormData = "";		
		$response = array();		
		
		// iterate thought fileds
		foreach(JRequest::get('get') as $key => $value) {		  
			// validate required fileds			
			
			if($key == "name" || $key == "email" || $key == "message") {
				if(strlen(trim($value)) == 0) {
					$returnCode = -2; // complete required fields 
					break;
				}				  
			}
			// validate catpcha
			elseif($key == "captcha") {
				if(strlen(trim($value)) == 0) {
					$returnCode = -1; // please select no robot option 
					break;
				}					 
			}
			// append extra form params
			else {
				if(strlen(trim($value)) > 0 && $key != "g-recaptcha-response") {
					$extraFormData .= "\r\n <p><strong>" . $key . ": </strong>" . $value . "</p>";
				}
			}
			
		}
		// show error, if any
		if(strlen(trim($returnCode)) != 1) {
			$response["status"] = $returnCode;
			echo json_encode($response);
			return;
		}		
		
		// create plugin call
		JPluginHelper::importPlugin('captcha');
		$dispatcher = JDispatcher::getInstance();		
		
		// validate captcha using plugin method and passing the response ($captcha)
		$result = var_export($dispatcher->trigger('onCheckAnswer', JRequest::getVar('recaptcha', '', 'get'))[0], true);
		
		if($result != "true") {					
			$returnCode = 0; // captcha validation fail
		} 
		else { 
			$content = "<p><strong>Mensaje: </strong>" . JRequest::getVar('message', '', 'get') . "</p>";
			$content .= "\r\n" . $extraFormData;
			$config = JFactory::getConfig();
			$name = JRequest::getVar('name', '', 'get');
			$subject = "Consulta de " . (strlen($name) > 0 ? $name." - " : "") . $config->get('sitename'); 
			$utils = new Utils();
			$utils->sendMail($content, $subject, "", "", JRequest::getVar('email', '', 'get'), $name);
			// message sent
			$returnCode = 1;
		} 
		$response = array();
		$response["status"] = $returnCode;
		echo json_encode($response);
		return;
?>