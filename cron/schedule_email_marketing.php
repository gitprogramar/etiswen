<?php /*********** NO SPACE BEFORE THIS LINE!!!!!!!!!!!!!! *****************/
	/*
	 * Daily cron task. Email Marketing: Send articles as emails campaings for 'Social Marketing' category. Based on specific date and user category.
	 */
	 
	//php -f /home/u383829915/public_html/cron/schedule_email_marketing.php param

	$utils;
	try 
	{
		define( '_JEXEC', 1 );
		define('JPATH_ROOT', realpath(dirname(__FILE__).'/../') );
		require_once ( JPATH_ROOT .'/async/utils.php');	
		
		$utils = new Utils();
		$utils->cronStart((string)$argv[1], "/cron/email_marketing_counter.txt");
		
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
		$categories = array();
		getCategories(9, $categories);
		$campaings = getCampaings($categories);		
		
		// create storage for very first time
		createStorage(STORAGE);				

		// control variables
		$storageValues = explode(",", file_get_contents(STORAGE));
		$sentCounter = 0; // total emails sent. Also where to start sending the eamils.
		$currentCampaing = ""; // campaing running
		$currentCategory = ""; // category running
		$currentArticleId = ""; // article id on the campaing used for
		if(count($storageValues) == 4) {			
			$sentCounter = $storageValues[0];
			$currentCampaing = $storageValues[1];
			$currentCategory = $storageValues[2];
			$currentArticleId = $storageValues[3];
		}
		
		$utils = new Utils();		
		/*
		// if hour 0 && min 0 then is the very first time
		if(getdate()['hours'] == 0 && getdate()['minutes'] == 0 && strlen($currentCampaing) > 0){
			// reset counter only at start of day and when sent counter equal than total users
			if($sentCounter >= (count($utils->getUsersByGroup($currentCategory))-1)) {
				// reset values
				file_put_contents(STORAGE, "");
				$sentCounter = 0;
				$currentCampaing = "";
				$currentCategory = "";
				$currentArticleId = "";
			}
		}
		*/
		
		//echo "file_get_contents 1: ".$sentCounter;
		$usersCounter = 0; // to limit 15 users max.
		$currentUserIndex = 0; // the current user
		
		echo "Email Marketing Campaing Configuration".LB.LB;
		foreach($campaings as $campaing) {
			echo "Category/Group: ".$campaing[0].LB;
			echo "Name: ".$campaing[1].LB;
			echo "Programed Date: ".$campaing[2].LB;
			if(isCampaingSend($campaing)){
				// send the campaing if users exists				
				$users = $utils->getUsersByGroup($campaing[0]);				
				
				// saves new campaing if not setted
				/*if(strlen($currentCampaing) == 0) {
					$currentCampaing = $campaing[1];					
					$currentCategory = $campaing[0];
				}*/
				
				// this is a new campaing
				if($campaing[5] != $currentArticleId) {
					$sentCounter = 0;
					$currentCampaing = $campaing[1];					
					$currentCategory = $campaing[0];
					$currentArticleId = $campaing[5];
				}
				
				if($currentCategory == $campaing[0] && $currentCampaing == $campaing[1]) {				
					//var_dump($users);
					//$users = array();
					if(count($users) > 0){
						foreach($users as $id): 
							// user
							$user = JFactory::getUser($id);						
							$currentUserIndex++;
							// split every 15 users logic
							if($usersCounter < 15 && $currentUserIndex > $sentCounter):	
								// skip blocked or blacklisted users
								if($user->block == 0 /*&& $user->blacklist == 0*/):
									// to personalize with name
									$name = "";
									if(strpos($user->email, $user->name) !== false) {
										$name = " ".$user->name;
									}
								
									// send notification. //El limite es de 15 por minuto y 60 por hora. (max. 15 email every 15 minutes = 60 email per hour = 1440 email per day)								
									send_mail($campaing[1], $user->email, $campaing[3], array($name, $campaing[0], $user->name, $user->email));
								endif;
								$usersCounter++;							
								$sentCounter++;
							endif;						
						endforeach;						
						
						echo "Qty. Users: ".$usersCounter.LB;
						echo "Sent Today? YES".LB;		
						$usersCounter = 0;
					}
					else {
						echo "Qty. Users: 0".LB;
						echo "Sent Today? NO".LB;
					}
				}
				else {
					echo "Sent Today? NO".LB;
				}				
			}
			else {
				echo "Sent Today? NO".LB;
			}
			echo "-------------------".LB.LB;
			//var_dump(isCampaingSend($campaing));
		}		
		
		if($sentCounter >= (count($utils->getUsersByGroup($currentCategory))) && strlen($currentCategory) > 0 ) {
			echo "Finished: Emails Sent for ".$currentCampaing.": ".$sentCounter." from Total Users: ".(count($utils->getUsersByGroup($currentCategory)));
		}
		else {			
			echo "Emails sent today: ".$sentCounter." from Total Users: ".(count($utils->getUsersByGroup($currentCategory)));
		}		
		// save actual counter
		if(strlen($currentCampaing) > 0 && strlen($currentCategory) > 0) {
			file_put_contents(STORAGE, $sentCounter.",".$currentCampaing.",".$currentCategory.",".$currentArticleId);			
		}
	}

	// recursively get all categories for Email Marketing
	function getCategories($categoryId, &$categories){
		// get categories
		$db = JFactory::getDBO();
		$query  = $db->getQuery(true);
		$query->select('id');
		$query->from('#__categories');
		$query->where('parent_id = '.$categoryId); // category ID Email Marketing
		$db->setQuery($query);
		$childs = $db->loadRowList();
		// iterate and get the childs
		for($i=0; $i <= count($childs); $i++){
			if(!isset($childs[$i]) || count(array_values($childs)[$i]) == 0){
				continue;
			}
			$categories[count($categories)] = array_values($childs)[$i];
			getCategories(array_values(array_values($childs)[$i])[0], $categories);
		}		
	}
	
	// get email campaing data by category id
	function getCampaings($categories){
		$db = JFactory::getDBO();
		$campaings = array();
		// get categories
		foreach($categories as $category) {
			if(!is_array($category) || count($category) == 0){
				continue;
			}			
			// get aticles by category
			$query  = $db->getQuery(true);
			$query->select('category.title, content.title, tags.title, content.introtext, content.alias, content.id');
			$query->from('#__content content INNER JOIN #__categories category ON content.catid = category.id'.
			' INNER JOIN #__contentitem_tag_map map ON content.id = map.content_item_id'.
			' INNER JOIN #__tags tags ON map.tag_id = tags.id');
			$query->where('content.catid = '.array_values($category)[0]); // category ID
			/*
			SELECT `category`.title, `content`.title, `tags`.title FROM `prog_content` content INNER JOIN `prog_categories` category ON content.`catid` = category.`id` INNER JOIN `prog_contentitem_tag_map` map ON content.`id` = map.`content_item_id` INNER JOIN `prog_tags` tags ON map.`tag_id` = tags.`id` WHERE content.`catid` = 14
			*/			
			$db->setQuery($query);
			$list = $db->loadRowList();
			// iterate throught all campaings
			for($i=0; $i <= count($list); $i++){
				if(!isset($list[$i]) || count(($list)[$i]) == 0){
					continue;
				}
				$campaings[count($campaings)] = $list[$i];
			}
		}
		return $campaings;
	}
	
	// check if send the campaing based on the frecuency
	function isCampaingSend($campaing) {
		//fill in with server time
		$info = getdate();
		$serverDate = $info['mday'];
		$serverMonth = $info['mon'];
		$serverYear = $info['year'];
		$fullServerDate = "$serverDate-$serverMonth-$serverYear";
		
		// campaing date
		$date = str_replace("/", "-", $campaing[2]);
		$splitDate = explode("-", $date);
		
		if(count($splitDate) == 2 && strlen($splitDate[1]) == 0){
			$date .= "$serverMonth-$serverYear";
			$splitDate[1] = $serverMonth;
			array_push($splitDate, $serverYear);
		}
		elseif(count($splitDate) == 2){
			$date .= "-$serverYear";
			array_push($splitDate, $serverYear);
		}
		// validate is date and compare with current server date
		if(checkdate($splitDate[1], $splitDate[0], $splitDate[2]) && strtotime($date) == strtotime($fullServerDate) || campaingDateDifference($date, $fullServerDate) == "1"){
			return true;
		}
		
		return false;
	}
	
	//El limite es de 15 por minuto y 60 por hora.
	function send_mail($title, $email, $htmlMail, $arrayReplace) 
	{
		$utils = new Utils();
		// notify customer
		/*$utils->sendMail(
			$htmlMail, 		
			$title,
			$email,
			"",
			"",
			"",
			array("[Usuario]", "[Grupo]", "[Dominio]", "[Email]"),
			$arrayReplace
		);*/
	}

	// create storage file if not already exists
	function createStorage($storage) {
		if(file_exists($storage) !== true){ // if file not exists
			file_put_contents($storage, ""); // create file with default content
		}
	}	
	
	//////////////////////////////////////////////////////////////////////
	//PARA: Date Should In YYYY-MM-DD Format
	//RESULT FORMAT:
	// '%y Year %m Month %d Day %h Hours %i Minute %s Seconds'        =>  1 Year 3 Month 14 Day 11 Hours 49 Minute 36 Seconds
	// '%y Year %m Month %d Day'                                    =>  1 Year 3 Month 14 Days
	// '%m Month %d Day'                                            =>  3 Month 14 Day
	// '%d Day %h Hours'                                            =>  14 Day 11 Hours
	// '%d Day'                                                        =>  14 Days
	// '%h Hours %i Minute %s Seconds'                                =>  11 Hours 49 Minute 36 Seconds
	// '%i Minute %s Seconds'                                        =>  49 Minute 36 Seconds
	// '%h Hours                                                    =>  11 Hours
	// '%a Days                                                        =>  468 Days
	//////////////////////////////////////////////////////////////////////
	function campaingDateDifference($date_1 , $date_2 , $differenceFormat = '%a' )
	{
		$datetime1 = date_create($date_1);
		$datetime2 = date_create($date_2);
		
		if($datetime1 > $datetime2) {
			return -1;			
		}
		
		$interval = date_diff($datetime1, $datetime2);
		
		return $interval->format($differenceFormat);
		
	}
?>
