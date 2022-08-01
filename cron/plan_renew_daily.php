<?php /*********** NO SPACE BEFORE THIS LINE!!!!!!!!!!!!!! *****************/
	/*
	 * Daily cron task. Check service status of customers and send email notifications when product/service is about to exipire.
	 */
	 
	//php -f /home/u510425236/public_html/cron/plan_renew_daily.php param
	$utils;
	try 
	{
		define( '_JEXEC', 1 );
		define('JPATH_ROOT', realpath(dirname(__FILE__).'/../') );
		require_once ( JPATH_ROOT .'/api/utils.php');	
		setlocale(LC_TIME, "es_AR");
				
		$utils = new Utils();
		
		$ps = (string)$argv[1];
		if(!isset($ps) || strlen($ps) == 0)
			$ps = '';		
		
		$utils->cronStart($ps);				
		work();
		
		$utils->cronEnd();
	}
	catch(Exception $ex) {
		if(isset($utils))
			$utils->raiseError($ex);
		echo $ex->getMessage();
	}
	
	function work()
	{	
		$db     = JFactory::getDBO();
		jimport( 'joomla.access.access' );	
		
		// get email content
		$query  = $db->getQuery(true);
		$query->select('introtext');
		$query->from('#__content');
		$query->where('id = 24'); // article ID with HTML mail
		$db->setQuery($query);
		$articleContent = $db->loadResult();
		//echo (str_replace('"','\"', $articleContent));
		
		$query = $db->getQuery(true);
		$query
		    ->select(array('u.id', 'u.name', 'u.username', 'u.email', "SUBSTRING_INDEX(GROUP_CONCAT(fv.value SEPARATOR ','), ',', 1) AS `domain`", "SUBSTRING_INDEX(SUBSTRING_INDEX(GROUP_CONCAT(fv.value SEPARATOR ','), ',', 2), ',',-1) AS `startdate`", "SUBSTRING_INDEX(GROUP_CONCAT(fv.value SEPARATOR ','), ',', -1) AS `duration`"))
            ->from($db->quoteName('#__users', 'u'))
            ->join('INNER', $db->quoteName('#__user_usergroup_map', 'ugm').' ON '.$db->quoteName('ugm.user_id').' = '.$db->quoteName('u.id'))
            ->join('INNER', $db->quoteName('#__fields_values', 'fv').' ON '.$db->quoteName('fv.item_id').' = '.$db->quoteName('u.id'))
            ->join('INNER', $db->quoteName('#__fields', 'f').' ON '.$db->quoteName('f.id').' = '.$db->quoteName('fv.field_id'))
            ->where($db->quoteName('ugm.group_id'). ' = 2 AND '.$db->quoteName('u.block'). ' = 0 AND ('.$db->quoteName('f.name'). ' = '."'domain'".' OR '.$db->quoteName('f.name'). ' LIKE '."'plan%')")
            ->group($db->quoteName('u.id'));
		//echo $query->dump().LB.LB;
		$db->setQuery($query);
		$customers = $db->loadRowList();

        $status = "";
		// iterate customers
		foreach($customers as $customer): 
            $status .= calculateTime($customer, $articleContent);
		endforeach;	
		
		sendStatus($status);
	}

	function calculateTime($customer, $articleContent) {	
		$utils = new Utils();
		
		$output = DIV."Customer: ".$customer[2].DIVEND;
		
		// current quantity of plan days
		$currentDays = $utils->dateDifference(date("Y-m-d"), $customer[5]);
		// quantity of remaining days
		$leftDays = (365*$customer[6])-$currentDays;
		if($currentDays < 0) {
			$output .= DIV."Status: Plan duration ended".DIVEND;
		}
		else {
		    $output .= DIV."Status: Active".DIVEND;
		}
		$output .= DIV."Duration: ".$customer[6].' year/s'.DIVEND;
		$output .= DIV."Start Date: ".date("Y-m-d",strtotime($customer[5])).DIVEND;
		$output .= DIV."End Date: ". date('Y-m-d', strtotime(date("Y-m-d") . '+ '.$leftDays.'days')).DIVEND;
		$formated = utf8_encode(strftime("%A %e de %B de %G", strtotime(date("m/d/y") . '+ '.$leftDays.'days')));
		$output .= DIV."Formated End Date: ".$formated.DIVEND;
		$output .= DIV."Remaining days: ".$leftDays.DIVEND;
		
		if($leftDays == 30 || $leftDays == 15 || $leftDays == 5 || $leftDays == 1) {
		    $output .= DIVBOTTOM."Email sent: yes".DIVEND;
		    sendMail($customer[3], $articleContent, array($customer[1], $customer[2], $customer[4], $formated, $leftDays));
		}
		else {
		    $output .= DIVBOTTOM."Email sent: no".DIVEND;
		}
		echo $output;
		return $output;
	}

	function sendMail($email, $articleContent, $arrayReplace) 
	{
		$utils = new Utils();
		$enterprise = $utils->enterpriseGet("admin-en");
		//var_dump($enterprise->customer);
		
		// notify customer
		$utils->sendMail(
			$articleContent, 		
			"Renovar Servicio",
			$email,
			"",
			"",
			"",
			array("[name]", "[username]", "[domain]", "[endDate]", "[days]"),
			$arrayReplace
		);
		
		// send copy
		$utils->sendMail(
			$articleContent, 		
			"Renovar Servicio",
			$enterprise->customer->email,
			"",
			"",
			"",
			array("[name]", "[username]", "[domain]", "[endDate]", "[days]"),
			$arrayReplace
		);
	}
	
	function sendStatus($status) {
	    $info = getdate();
		$day = $info['mday'];
		
		if((int)$day == 1) {
    	    $utils = new Utils();
    		$enterprise = $utils->enterpriseGet("admin-en");
    	    // send status
    		$utils->sendMail(
    			$status, 		
    			"Plan status report",
    			$enterprise->customer->email
    		);
    		echo DIV."Status sent: yes".DIVEND;
		}
		else {
		    echo DIV."Status sent: no".DIVEND;
		}
	}
?>