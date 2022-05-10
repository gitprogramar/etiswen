<html><head><meta name="robots" content="noindex, nofollow"></head><body>
<?php	
		// test
		define( '_JEXEC', 1 );
		define('JPATH_ROOT', realpath(dirname(__FILE__).'/../') );
		require_once ( JPATH_ROOT .'/libraries/vendor/phpmailer/phpmailer/class.phpmailer.php');
		require_once ( JPATH_ROOT .'/libraries/vendor/phpmailer/phpmailer/class.smtp.php');
		require_once ( JPATH_ROOT .'/api/utils.php');
		//JFactory::getApplication("site");
		/*
		$type = JRequest::getVar('type', '', 'get');
		$position = JRequest::getVar('position', '', 'get');
		$menuid = JRequest::getVar('menuid', '', 'get');
	
		if(strlen($type) == 0 || strlen($position) == 0 || strlen($menuid) == 0) {		
			echo "not found";
			return;
		}
		*/
		/* 
		if($type == "menu") {
			$menu = JFactory::getApplication('site')->getMenu();
			$mainMenu = json_encode($menu->getItems("menutype", "mainmenu"));
		}
		*/
		// currency
		/*echo 'HTTP_ACCEPT_LANGUAGE: '.$_SERVER['HTTP_ACCEPT_LANGUAGE'];
		echo '<br>';
		
		
		$lang = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);
		echo 'Lang: '.$lang;
		echo '<br>';
		
		$locale = Locale::acceptFromHttp($_SERVER['HTTP_ACCEPT_LANGUAGE']);
		echo 'Locale: '.$locale;
		echo '<br>';
		
		$language = Locale::getDisplayLanguage($_SERVER['HTTP_ACCEPT_LANGUAGE']);
		echo 'Display Language: '.$language;
		echo '<br>';
			*/	
		
		//server time
		$info = getdate();
		$date = $info['mday'];
		$month = $info['mon'];
		$year = $info['year'];
		$hour = $info['hours'];
		$min = $info['minutes'];
		$sec = $info['seconds'];
		echo "$date/$month/$year $hour:$min:$sec";	
		echo "<br>";
		//----------------------------------------------------------------------------------------------
		//Crea un usuario de prueba de Mercado Pago
		//----------------------------------------------------------------------------------------------
		/*require_once (JPATH_ROOT.'/mercadopago/mercadopago.php');
		$mp = new MP ("115648826979517", "zI9e0KgvfbgsA5avYmstENIeshB23zCc");
		$mp->sandbox_mode(true);
		$accessToken = $mp->get_access_token();
		$url ="https://api.mercadolibre.com/users/test_user?access_token=".$accessToken;
		$valor1 = "MLA";
		$parametros_post = json_encode(array(
			 "site_id" => $valor1  ));
		$respuesta = $mp->post("/users/test_user", $parametros_post);
		echo "<br><br>";
		$usuario = $respuesta["response"];
		echo $usuario["nickname"];
		echo "<br>";
		echo $usuario["email"];
		echo "<br>";
		echo $usuario["password"];
		echo "<br>";
		*/
		
		/*Send email*/
		/*
		$mail = new PHPMailer;
		// headers info
		$mail->CharSet = 'UTF-8';
		$mail->Encoding = "base64";
		//Tell PHPMailer to use SMTP
		$mail->isSMTP();
		//Enable SMTP debugging
		// 0 = off (for production use)
		// 1 = client messages
		// 2 = client and server messages
		$mail->SMTPDebug = 2;
		//Set the hostname of the mail server
		$mail->Host = 'tls://smtp.gmail.com';
		//Set the SMTP port number - likely to be 25, 465 or 587
		$mail->Port = '587';
		//Whether to use SMTP authentication
		$mail->SMTPAuth = true;
		
		$mail->SMTPSecure = 'tls';
		//Setup the following configuration for smtp authentication
		$mail->SMTPOptions = array(
		'ssl' => array(
		'verify_peer' => false,
		'verify_peer_name' => false,
		'allow_self_signed' => true
		)
		);
		
		
		//Username to use for SMTP authentication
		$mail->Username = 'info@logisticashc.com.ar';
		//Password to use for SMTP authentication
		$mail->Password = 'infolog1234';
		//Set who the message is to be sent from
		$mail->setFrom('info@logisticashc.com.ar');
		//Set an alternative reply-to address
		$mail->addReplyTo('info@logisticashc.com.ar', 'SHC');
		//Set who the message is to be sent to
		$mail->addAddress('rey.nico@outlook.com', 'Nicolas Rey');
		//Set the subject line
		$mail->Subject = 'consulta de correo';
		// html
		$mail->msgHTML('<p>prueba de correo</p>');
		// attachment
		if (isset($attachment) &&
			$attachment['error'] == UPLOAD_ERR_OK) {
				$mail->AddAttachment($attachment['tmp_name'],$attachment['name']);
		}
	
		//send the message
		if (!$mail->send()) {
			echo '<br><br>Correo NO enviado: '.$mail->ErrorInfo;
		}
		else {
			echo '<br><br>Correo Enviado!!!';
			
		}
		*/
?>
</body></html>