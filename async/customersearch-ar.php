<html><head><meta name="robots" content="noindex, nofollow"></head><body>
<?php
	/*
	 * Customr search task. Searches customer web page and email using Google API tools by brand/category/business etc.
	 * 
	 * How to use:
	 * data: business word to search for
	 * group: name of joomla user group (previously created from admin)
	 * start: index start page ( &start=1&end=31 &start=41&end=61  &start=71&end=91 )
	 * end: index end page 
	 * http://programar.com.ar/async/customersearch-ar.php?data=camiones&group=Camiones&start=1&end=41
	 *
	 * Note: support 1 level of parent-children on user groups
	 
	 * Check user in multiple groups!
		SELECT user_id FROM 
		prog_user_usergroup_map
		GROUP BY user_id
		HAVING (count(user_id) > 2)
		
	* Check user WITHOUT grups
		SELECT * FROM prog_users WHERE id NOT IN (
		SELECT DISTINCT user_id FROM 
		prog_user_usergroup_map 
		)
	 
	 * Check duplicates!
	   SELECT username AS count
	   FROM prog_users
	   GROUP BY username
	   HAVING (count(username) > 1)
	 
	 */
		
	define( '_JEXEC', 1 );
	define('JPATH_ROOT', realpath(dirname(__FILE__).'/../') );
	require_once ( JPATH_ROOT .'/async/utils.php');	
	
	// storage
	//define('STORAGE', getcwd().'/public_html/async/dataImport.txt');
	define('STORAGE', getcwd().'/dataImport.txt');
	define("LB", "<br>");	
	
	// environment
	JFactory::getApplication('site');
	
	// secure the page
	$user = JFactory::getUser(); 
	$userId = $user->get('id');	
	if($userId == 0) {		
		echo "Page not available. Please login first.";
		return;
	}
	
	$data = JRequest::getVar('data', '', 'get');
	$group = JRequest::getVar('group', '', 'get');
	$start = JRequest::getVar('start', '', 'get');
	$end = JRequest::getVar('end', '', 'get');
	$language = JRequest::getVar('lang', '', 'get');
	$searchTerms = JRequest::getVar('searchTerms', '', 'get');
	$exactTerms = JRequest::getVar('exactTerms', '', 'get');
	$excludeTerms = JRequest::getVar('excludeTerms', '', 'get');
	$siteSearch = JRequest::getVar('siteSearch', '', 'get');
	$saveInFile = JRequest::getVar('saveInFile', '', 'get');
	$key = JRequest::getVar('key', '', 'get');
	$cx = JRequest::getVar('cx', '', 'get');	

	if(strlen($data) == 0 || strlen($group) == 0 || strlen($start) == 0 || strlen($end) == 0 || strlen($language) == 0
	|| strlen($key) == 0 || strlen($cx) == 0
	) {		
		echo "No data provided.";
		return;
	}	
	
	// group id logic
	$groupId = 0;
	$parentGroupId = 0;
	if(!groupExists($group, $groupId, $parentGroupId)){
		echo "Group '".$group."' does not exist on data base.";
		return;
	}
	
	// search config	
	$googleHost = 'google.com';	
	$url = 'https://www.googleapis.com/customsearch/v1?q='.urlencode($data).'&cx='.urlencode($cx).'&filter=1&googlehost='.$googleHost.'&lr='.urlencode($language).'&num=10&orTerms='.urlencode($searchTerms).(strlen($siteSearch)>0 ? '&siteSearch='.urlencode($siteSearch):'').(strlen($exactTerms)>0 ? '&exactTerms='.urlencode($exactTerms):'').(strlen($excludeTerms)>0 ? '&excludeTerms='.urlencode($excludeTerms):'').'&start=[index]&fields=items(displayLink%2Clink)%2Cqueries%2CsearchInformation(formattedSearchTime%2CformattedTotalResults)&key='.urlencode($key);	
	
	$counter = 0; // counts number of inserts
	if(!$saveInFile) {
		$counter = search($url, array($start), $groupId, $parentGroupId, $end);
	}
	else {
		$counter = saveInTextFile($url, array($start), $groupId, $parentGroupId, $end);
	}
	echo LB."--------------".LB;
	echo "<span>Total inserts: ".$counter."</span>";
	echo "<span>Group Id: ".$groupId."</span>";
	echo "<span>Parent Group Id: ".$parentGroupId."</span>";
	/*Start*/
	echo "<span>Started: " . runTime()."</span>";
	/*end*/
	echo "<span>Finished: " . runTime()."</span>".LB.LB;
	return;
	
	function search($url, $arrayReplace, $groupId, $parentGroupId, $end) {
		$arraySearch = array("[index]");
		$counter = 0;
		$urlFormatted = str_replace($arraySearch, $arrayReplace, $url);	
		$body = file_get_contents($urlFormatted);
		$json = json_decode($body);		
		
		if ($json->items){
			foreach ($json->items as $item){		
				$found = 0;			
				$matches = array(); //create array
				//$pattern = '/[A-Za-z0-9_-]+@[A-Za-z0-9_-]+\.([A-Za-z0-9_-][A-Za-z0-9_]+)/'; //regex for pattern of e-mail address
				$pattern = '/[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,4}/';
				$found = preg_match_all($pattern, file_get_contents($item->link), $matches); //find matching pattern
				//echo after("://",$item->displayLink).LB;
				echo '<p><a href="'.$item->link.'" target="_blank">'.$item->displayLink.'</a>';
				if($found != 0 || $found != false) {
					//var_dump(array_values($matches)[0]);
					foreach(array_values($matches)[0] as $match) {
						if(
						strpos($match, ".png") == false && strpos($match, ".jpg") == false && strpos($match, ".jpeg") == false && strpos($match, ".gif") == false
						&& strpos($item->displayLink, ".gob.ar") == false && strpos($item->displayLink, ".gov.ar") == false
						) {													
							echo '<span> '.$match."</span>";
							if(!userExists($match)){
								createUser($item->displayLink, $match);
								createUserGroupMapping($match, $groupId, $parentGroupId);
								$counter++;
							}
						}
					}
				}
				echo '</p>';
			}
		}
		
		// next search
		/*
		if ($json->queries->nextPage){
			// has next page
			$index = array_values($json->queries->nextPage)[0]->startIndex;
			if($index <= $end) { // restriction limit by Google API last page index 91 max
				search($url, array($index), $groupId, $parentGroupId, $end);
			}
		}	
		*/
		
		return $counter;
	}
	
	// save the url and group data into a text file to process later
	function saveInTextFile($url, $arrayReplace, $groupId, $parentGroupId, $end) {
		$arraySearch = array("[index]");
		$counter = 0;
		$urlFormatted = str_replace($arraySearch, $arrayReplace, $url);	
		$body = file_get_contents($urlFormatted);
		$json = json_decode($body);		
		createStorage(STORAGE);
		
		if ($json->items){
			foreach ($json->items as $item){						
				$row = $item->link.",".$parentGroupId.",".$groupId."\r\n";
				file_put_contents(STORAGE, strtolower($row), FILE_APPEND);
				$counter++;
			}
		}
		return $counter;
	}
	
	// create storage file if not already exists
	function createStorage($storage) {
		if(file_exists($storage) !== true){ // if file not exists
			file_put_contents($storage, ""); // create file with default content
		}
	}	
	
	function groupExists($group, &$groupId, &$parentGroupId) {
		$db = JFactory::getDBO();		
		
		// get module content
		$query  = $db->getQuery(true);
		$query->select('id, parent_id');
		$query->from('#__usergroups');
		$query->where("LOWER(title) = '".strtolower($group)."'"); 
		$db->setQuery($query);
		$userGroup = $db->loadRowList();
		// if group do not exists
		if(count($userGroup) == 0){
			return false;
		}
		$groupId = array_values(array_values($userGroup)[0])[0];
		$parentGroupId = array_values(array_values($userGroup)[0])[1];
		return true;
	}
	
	function userExists($user) {
		$db = JFactory::getDBO();		
		
		// get module content
		$query  = $db->getQuery(true);
		$query->select('id');
		$query->from('#__users');
		$query->where("LOWER(username) = '".strtolower($user)."'"); 
		$db->setQuery($query);
		$user = $db->loadRowList();
		// if user do not exists
		if(count($user) == 0){
			return false;
		}
		return true;
	}
	
	function createUser($name, $mail) {
		//server time
		$info = getdate();
		$date = $info['mday'];
		$month = $info['mon'];
		$year = $info['year'];
		$registerDate =  "$year-$month-$date";
		
		$db = JFactory::getDBO();		
	
		// columns to insert.
		$columns = array('name', 'username', 'email', 'password', 'block', 'sendEmail', 'registerDate', 'params', 'resetCount', 'requireReset');
		
		// values to insert.
		$values = array($db->quote($name), $db->quote($mail), $db->quote($mail), $db->quote('$2y$10$rDzjtiASL7U04OnPv3Pml.MQvjJzuNTtFb25aXUOo2LyDKdSmcwGy'), 0, 0, $db->quote($registerDate) ,$db->quote('{"admin_style":"","admin_language":"","language":"","editor":"","helpsite":"","timezone":""}'), 0, 0);
		
		// insert data
		$query  = $db->getQuery(true);
		$query->insert($db->quoteName('#__users'));
		$query->columns($db->quoteName($columns));
		$query->values(implode(',', $values));
		$db->setQuery($query);
		$db->execute();		
	}
	
	function createUserGroupMapping($mail, $groupId, $parentGroupId) {		
		$userId = JUserHelper::getUserId($mail);
		
		$db = JFactory::getDBO();	
	
		// columns to insert.
		$columns = array('user_id', 'group_id');
		
		// values to insert.
		$values = array($userId, $groupId);
		
		// insert data
		$query  = $db->getQuery(true);
		$query->insert($db->quoteName('#__user_usergroup_map'));
		$query->columns($db->quoteName($columns));
		$query->values(implode(',', $values));
		$db->setQuery($query);
		$db->execute();		
		
		// values to insert.
		$values = array($userId, $parentGroupId);
		
		// insert PARENT data
		$query  = $db->getQuery(true);
		$query->insert($db->quoteName('#__user_usergroup_map'));
		$query->columns($db->quoteName($columns));
		$query->values(implode(',', $values));
		$db->setQuery($query);
		$db->execute();		
	}
	
	function runTime() {
		//server time
		$info = getdate();
		$date = $info['mday'];
		$month = $info['mon'];
		$year = $info['year'];
		$hour = $info['hours'];
		$min = $info['minutes'];
		$sec = $info['seconds'];
		return "$date/$month/$year $hour:$min:$sec";	
	}
?>
</body></html>