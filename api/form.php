<?php	
		// Async Form logic		
		define( '_JEXEC', 1 );
		define('JPATH_ROOT', realpath(dirname(__FILE__).'/../') );
		require_once ( JPATH_ROOT .'/api/utils.php');	
		new Utils();
		// environment
		JFactory::getApplication('site');
	
		/*
		$version = JRequest::getVar('version', '', 'get');		
	
		$recaptcha = array();		
		if($version == 2) {
			JPluginHelper::importPlugin( 'captcha');
			$dispatcher=JDispatcher::getInstance();
			// This will return the array of HTML code.
			$recaptcha=$dispatcher->trigger('onDisplay', array(null, 'dynamic_recaptcha_1', 'class='''));			
		}
		
		echo (isset($recaptcha[0])) ? $recaptcha[0] : 'Plugin not found or not configured';
		return;
		*/
		//$path=getcwd(); 
		//echo 'Your Absolute Path is: '; 
		//echo $path;

		// handle error codes
		/*
		1 = Ok
		0 = Error
		-1 = Captcha Error
		-2 = Required Field Error
		*/
		$returnCode = 1;
		$extraFormData = '';		
		$response = array();
		$formSubject = '';
		$formEmail = '';
		$customer = $_SESSION['customer'];
		
		// iterate thought fileds
		foreach(JRequest::get('get') as $key => $value) {		  
			// validate required fileds			
			
			if($key == 'name' || $key == 'email' || $key == 'message') {
				if(strlen(trim($value)) == 0) {
					$returnCode = -2; // complete required fields 
					break;
				}				  
			}
			// validate catpcha
			elseif($key == 'captcha') {
				if(strlen(trim($value)) == 0) {
					$returnCode = -1; // please select no robot option 
					break;
				}							
			}
			// custom form subject
			elseif($key == 'form-subject') {
				$formSubject = $value;				
			}
			// custom form email
			elseif($key == 'form-email') {
				if($value == 'email-2')
					$formEmail = $customer->email2;
				if($value == 'email-3')
					$formEmail = $customer->email3;
				if($value == 'email-4')
					$formEmail = $customer->email4;
			}
			// append extra form params
			else {
				if(strlen(trim($value)) > 0 && $key != 'g-recaptcha-response') {
					$extraFormData .= '<br> <p><strong>' . $key . ': </strong>' . $value . '</p>';
				}
			}
			
		}
		
		// validate attachment
		if($_FILES['file']) {
			// type .PDF .DOC .DOCX
			if($_FILES['file']['type'] != 'application/pdf' 
				&& $_FILES['file']['type'] != 'application/msword'
				&& $_FILES['file']['type'] != 'application/vnd.openxmlformats-officedocument.wordprocessingml.document') {
					$returnCode = -2;
				}			
			// lenght 5Mb max.
			if((intval($_FILES['file']['size'])/1024/1024) > 5) {
				$returnCode = -2;
			}
		}
		
		// show error, if any
		if(strlen(trim($returnCode)) != 1) {
			$response['status'] = $returnCode;
			echo json_encode($response);
			return;
		}		
		
		// create plugin call
		JPluginHelper::importPlugin('captcha');
		$dispatcher = JDispatcher::getInstance();		
		
		// validate captcha using plugin method and passing the response ($captcha)
		$result = var_export($dispatcher->trigger('onCheckAnswer', JRequest::getVar('recaptcha', '', 'get'))[0], true);
		
		if($result != 'true') {					
			$returnCode = 0; // captcha validation fail
		} 
		else { 
			$content = '<p>' . JRequest::getVar('message', '', 'get') . '</p>';
			$content .= $extraFormData;
			$config = JFactory::getConfig();			
			$name = JRequest::getVar('name', '', 'get');
			$subject = (strlen($formSubject) > 0 ? $formSubject.' ' : (isset($customer) ? $customer->subject.' ' : '')) . (strlen($name) > 0 ? $name.' - ' : '') . $config->get('sitename'); 
			$utils = new Utils();
			$utils->sendMail($content, $subject, (strlen($formEmail) > 0 ? $formEmail : '' ), '', JRequest::getVar('email', '', 'get'), $name, array(), array(), $_FILES['file']);
			// message sent
			$returnCode = 1;
		} 
		$response = array();
		$response['status'] = $returnCode;
		echo json_encode($response);
		return;
?>