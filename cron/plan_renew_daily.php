<?php /*********** NO SPACE BEFORE THIS LINE!!!!!!!!!!!!!! *****************/
	/*
	 * Daily cron task. Check service status of customers and send email notifications when product/service is about to exipire.
	 */
	 
	//php -f /home/u383829915/public_html/cron/plan_renew_daily.php param
	$utils;
	try 
	{
		define( '_JEXEC', 1 );
		define('JPATH_ROOT', realpath(dirname(__FILE__).'/../') );
		require_once ( JPATH_ROOT .'/async/utils.php');	
				
		$utils = new Utils();
		$utils->cronStart((string)$argv[1]);
		
		dowork();
		
		$utils->cronEnd();
	}
	catch(Exception $ex) {
		if(isset($utils))
			$utils->raiseError($ex);
		echo $ex->getMessage();
	}
	
	function dowork()
	{	
		$db     = JFactory::getDBO();
		jimport( 'joomla.access.access' );	
		
		// get email content
		$query  = $db->getQuery(true);
		$query->select('introtext');
		$query->from('#__content');
		$query->where('id = 24'); // article ID with HTML mail
		$db->setQuery($query);
		$articleContent = $db->loadRowList();
		$htmlMail = array_values(array_values($articleContent)[0])[0]; //echo (str_replace('"','\"', $htmlMail));
			
		$domain; 		
		$datewebdesign;
		$datesocialnetwork;
		$dateemailmarketing;
		$timewebdesign;
		$timesocialnetwork;
		$timeemailmarketing;	
			
		// customer id group
		$customerGroupId = 2; 
		$customers = JAccess::getUsersByGroup($customerGroupId); //var_dump($customers); //echo $customers[0];	
		// iterate customers
		foreach($customers as $id): 
			// user
			$customer = JFactory::getUser($id);	 //var_dump($customer);
			// skip blocked users		
			if($customer->block != 0) {
				continue;
			}
			// get custom fields 
			$query  = $db->getQuery(true);
			$query->select('field.name, field.label, fieldvalue.value');
			$query->from('#__fields field INNER JOIN #__fields_values fieldvalue ON field.id = fieldvalue.field_id');
			$query->where('fieldvalue.item_id = '.$customer->id);
			$db->setQuery($query);
			$list = $db->loadRowList();
			//var_dump($list);
			
			$domain = "";
			$datewebdesign = "";
			$datesocialnetwork = "";
			$dateemailmarketing = "";
			$timewebdesign = "";
			$timesocialnetwork = "";
			$timeemailmarketing = "";
			
			foreach($list as $field) {
				if(strlen(trim($field[2])) == 0) {
					continue;
				}
				if($field[0] == "domain") {
					$domain = $field[2]; 						
				}
				elseif($field[0] == "datewebdesign") {
					$datewebdesign = $field[2];
				}
				elseif($field[0] == "datesocialnetwork") {
					$datesocialnetwork = $field[2];
				}
				elseif($field[0] == "dateemailmarketing") {
					$dateemailmarketing = $field[2];
				}
				elseif($field[0] == "timewebdesign") {
					$timewebdesign = $field[2];
				}
				elseif($field[0] == "timesocialnetwork") {
					$timesocialnetwork = $field[2];
				}
				elseif($field[0] == "timeemailmarketing") {
					$timeemailmarketing = $field[2];
				}
			}
			
			if(strlen(trim($datewebdesign)) > 0 && strlen(trim($timewebdesign))) {	
				calculate_time("Dise&#241;o Web", $datewebdesign, $timewebdesign, $customer, $htmlMail);
			}
			if(isset($datesocialnetwork) && isset($timesocialnetwork)) {			
				calculate_time("Redes Sociales", $datesocialnetwork, $timesocialnetwork, $customer, $htmlMail);
			}
			if(isset($dateemailmarketing) && isset($timeemailmarketing)) {			
				calculate_time("Campa&#241;as de Correo", $dateemailmarketing, $timeemailmarketing, $customer, $htmlMail);
			}
		endforeach;	
	}

	function calculate_time($service, $date, $time, $customer, $htmlMail) {	
		$times = explode(" ", $time);
		// calculates quantity of days
		$utils = new Utils();
		$datediff = $utils->dateDifference(date("Y-m-d"), $date); 	
		if(count($times) == 0 || count($times) == 1) {
			return;
		}
		//var_dump($customer);
		//echo $domain.": ".$times[0]." ".mb_strtolower($times[1], "UTF-8").LB;

		if(mb_strtolower($times[1], "UTF-8") == "gratis") {
			echo $customer->username.": "."Service: ".$service." > Period: FREE > ".$datediff." days of service. ".LB;		
		}
		elseif(mb_strtolower($times[1], "UTF-8") == "mes" || mb_strtolower($times[1], "UTF-8") == "meses") {
			echo $customer->username.": "."Service: ".$service." > Period: ".$times[0]." MONTH/S > ".$datediff." days of service. ".LB;
			if($times[0] == 1 && $datediff == 25 	    // 1 month
			   || $times[0] == 2 && $datediff == 55	  	// 2 months
			   || $times[0] == 3 && $datediff == 85		// 3 months
			   || $times[0] == 4 && $datediff == 115	// 4 months
			   || $times[0] == 5 && $datediff == 145	// 5 months
			   || $times[0] == 6 && $datediff == 175	// 6 months		  
			   ) {
				send_mail($customer->email, $htmlMail, array($customer->username, $service, "5 d&#237;as"));			
				echo "Email Sent: ".$service." expire in 5 days!".LB;
			}
		}
		elseif(mb_strtolower($times[1], "UTF-8") == "año" || mb_strtolower($times[1], "UTF-8") == "años") {
			echo $customer->username.": "."Service: ".$service." > Period: ".$times[0]." YEAR/S > ".$datediff." days of service. ".LB;
			if($times[0] == 1 && $datediff == 360 		// 1 year
			   || $times[0] == 2 && $datediff == 725	// 2 years
			   || $times[0] == 3 && $datediff == 1090	// 3 years
			   ) {
				send_mail($customer->email, $htmlMail, array($customer->username, $service, "5 d&#237;as"));			
				echo "Email Sent: ".$service." expire in 5 days!".LB;
			}
		}
	}

	function send_mail($email, $htmlMail, $arrayReplace) 
	{
		$utils = new Utils();
		// notify customer
		$utils->sendMail(
			$htmlMail, 		
			"Renovar Servicio - Programar.com.ar",
			$email,
			"",
			"",
			array("[customer]", "[service]", "[days]"),
			$arrayReplace
		);
		
		$html = '<h2>Vencimiento de '.$arrayReplace[1].'</h2>';
		$html .= '<p><strong>Usuario: '.$arrayReplace[0].'</strong></p>';
		$html .= '<p><strong>Correo: '.$email.'</strong></p>';
		$html .= '<p><strong>'.$arrayReplace[2].' restantes.</strong></p>';
		
		// notify PROGRAMAR
		$utils->sendMail(
			$html, 		
			"Vencimiento de ".$arrayReplace[1]			
		);
	}
?>