<?php /*********** NO SPACE BEFORE THIS LINE!!!!!!!!!!!!!! *****************/
	// Utils Library
	define( '_JEXEC', 1 );
	define('JPATH_ROOT', realpath(dirname(__FILE__).'/../') );
	require_once ( JPATH_ROOT .'/includes/defines.php');
	require_once ( JPATH_ROOT .'/includes/framework.php');
	require_once ( JPATH_ROOT .'/libraries/joomla/database/factory.php');
	require_once ( JPATH_ROOT .'/libraries/joomla/filesystem/folder.php');
	require_once ( JPATH_ROOT .'/libraries/vendor/phpmailer/phpmailer/class.phpmailer.php');
	require_once ( JPATH_ROOT .'/libraries/vendor/phpmailer/phpmailer/class.smtp.php');
	
	class Utils {
		protected $timer;

		public function __construct() {
			
		}
		
		function sendMail($content, $subject="", $to="", $toName="", $from="", $fromName="", $arraySearch=array(), $arrayReplace=array()) 
		{
			// default data
			$config = JFactory::getConfig();
			if(!isset($to) || strlen(trim($to)) == 0)
				$to = $config->get('mailfrom');
			if(!isset($from) || strlen(trim($from)) == 0)
				$from = $config->get('mailfrom');
			if(!isset($fromName) || strlen(trim($fromName)) == 0)
				$fromName = $config->get('fromname');			
			if(!isset($subject) || strlen(trim($subject)) == 0)
				$subject = "Mensaje del sitio web ".$config->get('sitename');
			// body
			$html = "<!DOCTYPE html><html lang=\"es\"><head><meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\"><meta charset=\"UTF-8\"><meta http-equiv=\"content-type\" content=\"text/html;charset=UTF-8\"><meta name=\"viewport\" content=\"width=device-width, initial-scale=1, maximum-scale=1\"></head><body>" . "\r\n";
			$html .= str_replace($arraySearch, $arrayReplace, $content) . "\r\n";	
			$html .= "</body></html>";			
			
			/*
			// header
			$headers  = "MIME-Version: 1.0" . "\r\n";
			$headers .= "Content-type: text/html; charset=UTF-8" . "\r\n";
			$headers .= "Content-Transfer-Encoding: base64". "\r\n";
			$headers .= "From: ". $from;
			// send
			mail($to,$subject,chunk_split(base64_encode($html)),$headers);
			*/
			
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
			$mail->SMTPDebug = 0;
			//Set the hostname of the mail server
			$mail->Host = $config->get('smtphost');
			//Set the SMTP port number - likely to be 25, 465 or 587
			$mail->Port = $config->get('smtpport');
			//Whether to use SMTP authentication
			$mail->SMTPAuth = true;
			//Username to use for SMTP authentication
			$mail->Username = $config->get('smtpuser');
			//Password to use for SMTP authentication
			$mail->Password = $config->get('smtppass');
			//Set who the message is to be sent from
			$mail->setFrom($config->get('mailfrom'), $fromName);
			//Set an alternative reply-to address
			$mail->addReplyTo($from, $fromName);
			//Set who the message is to be sent to
			$mail->addAddress($to, $toName);
			//Set the subject line
			$mail->Subject = $subject;
			// html
			$mail->msgHTML($html);
			//Attach an image file
			//$mail->addAttachment('images/phpmailer_mini.png');
		
			//send the message
			if (!$mail->send()) {
				throw new Exception($mail->ErrorInfo);
			}				
		}
		
		function login($user, $pass, $application) { // $application: 'site' or 'administrator'			
			// Create the Application			
			$app = JFactory::getApplication($application);
			jimport('joomla.plugin.helper');
			
			$credentials = array();
			$credentials['username'] = $user;
			$credentials['password'] = $pass;
			$credentials['secretkey'] = '';
			
			//perform the login action
			$result;
			if($application == "site")
				$result = $app->login($credentials);
			else
				$result = $app->login($credentials, array('action' => 'core.login.admin'));	
			if(!$result) {
				echo "Access denied.";
				exit();
				//return false;
			}		
			return true;
		}
		
		function logout($application){			
			$app = JFactory::getApplication($application);
			$app->logout();			
			exit();
		}
		
		function cronStart($pass, $storage = "") {
			// static vars
			if(substr(php_sapi_name(), 0, 3) == 'cli' || empty($_SERVER['REMOTE_ADDR'])) {
				// crontask
				define("LB", "\r\n"); 
				if(strlen(trim($storage)) > 0)
					define('STORAGE', getcwd().'/public_html'.$storage);				
			}				
			else {
				// webserver
				define("LB", "<br>");
				define('STORAGE', '/home/u383829915/public_html/cron/test_storage.txt');
				// skip robots
				echo '<html><head><meta name="robots" content="noindex, nofollow"></head><body></body></html>'.LB;
			}
			$this->timer = LB.LB."Task performance:";
			$this->timer .= LB."Started: ".JFactory::getDate()->toSQL();
			
			$this->login("cron", $pass, "site");
		}
		
		function cronEnd() {
			$this->timer .= LB."Finished: ".JFactory::getDate()->toSQL();
			echo $this->timer;
			$this->logout("site");
		}
		
		/* Get default editor customer */
		function customerGet($user="Editor", $template=null) {
			$id = 569;
			if($user == "Programar")
				$id = 556;
			$user = JFactory::getUser($id);
			$fields = FieldsHelper::getFields('com_users.user',  $user);
			$parses = array(' ', '(', ')', '+', '-');
			$customer = new stdClass();
			$customer->email = $user->email;
			$customer->customername = "";
			$customer->customernamePrsed = "";
			$customer->domain = "";
			$customer->templateId = "1";
			$customer->style = "";
			$customer->font1 = "";
			$customer->font2 = "";			
			$customer->fontweight1 = "";
			$customer->fontweight2 = "";
			$customer->primarycolor = "";
			$customer->secondarycolor = "";
			$customer->color1 = "";
			$customer->color2 = "";
			$customer->color3 = "";
			$customer->color4 = "";
			$customer->background1 = "";
			$customer->background2 = "";
			$customer->background3 = "";
			$customer->description = "";
			$customer->address = "";
			$customer->zone = "";
			$customer->postal = "";
			$customer->phone = "";
			$customer->cel = "";
			$customer->cel2 = "";
			$customer->phoneParsed = "";
			$customer->celParsed = "";
			$customer->cel2Parsed = "";
			$customer->facebook = "";
			$customer->twitter = "";
			$customer->youtube = "";
			$customer->instagram = "";
			$customer->linkedin = "";
			$customer->google  = "";
			foreach($fields as $field) {
				if($field->name == "customername") {
					$customer->customername = $field->value;
					$customer->customernameParsed = str_replace(' ', '%20', $field->value);
				}
				elseif($field->name == "domain")
					$customer->domain = $field->value;
				elseif($field->name == "description")
					$customer->description = $field->value;
				elseif($field->name == "templateid")
					$customer->templateId = isset($template) ? $template : $field->value;
				elseif($field->name == "style-".$customer->templateId)
					$customer->style = $field->value;
				elseif($field->name == "font1-".$customer->templateId)
					$customer->font1 = $field->value;
				elseif($field->name == "font2-".$customer->templateId)
					$customer->font2 = $field->value;
				elseif($field->name == "fontweight1-".$customer->templateId)
					$customer->fontweight1 = $field->value;
				elseif($field->name == "fontweight2-".$customer->templateId)
					$customer->fontweight2 = $field->value;
				elseif($field->name == "primarycolor-".$customer->templateId)
					$customer->primarycolor = $field->value;
				elseif($field->name == "secondarycolor-".$customer->templateId)
					$customer->secondarycolor = $field->value;
				elseif($field->name == "color1-".$customer->templateId)
					$customer->color1 = $field->value;
				elseif($field->name == "color2-".$customer->templateId)
					$customer->color2 = $field->value;
				elseif($field->name == "color3-".$customer->templateId)
					$customer->color3 = $field->value;
				elseif($field->name == "color4-".$customer->templateId)
					$customer->color4 = $field->value;
				elseif($field->name == "background1-".$customer->templateId)
					$customer->background1 = $field->value;
				elseif($field->name == "background2-".$customer->templateId)
					$customer->background2 = $field->value;
				elseif($field->name == "background3-".$customer->templateId)
					$customer->background3 = $field->value;
				elseif($field->name == "description")
					$customer->description = $field->value;
				elseif($field->name == "address")
					$customer->address = $field->value;
				elseif($field->name == "zone")
					$customer->zone = $field->value;
				elseif($field->name == "postal")
					$customer->postal = $field->value;
				elseif($field->name == "phone") {
					$customer->phone = $field->value;
					$customer->phoneParsed = str_replace($parses, "", $field->value);
				}
				elseif($field->name == "cel") {
					$customer->cel = $field->value;
					$customer->celParsed = str_replace($parses, "", $field->value);
				}
				elseif($field->name == "cel2") {
					$customer->cel2 = $field->value;
					$customer->cel2Parsed = str_replace($parses, "", $field->value);
				}
				elseif($field->name == "facebook")
					$customer->facebook = $field->value;
				elseif($field->name == "twitter")
					$customer->twitter = $field->value;
				elseif($field->name == "youtube")
					$customer->youtube = $field->value;
				elseif($field->name == "instagram")
					$customer->instagram = $field->value;
				elseif($field->name == "linkedin")
					$customer->linkedin = $field->value;
				elseif($field->name == "google")
					$customer->google = $field->value;
			}
			return $customer;
		}
		
		function getUsersByGroup($groupName){				
			$groupId = $this->getIdByGroupName($groupName); // customer id group
			$users = array();
			if($groupId != false) {
				$users = JAccess::getUsersByGroup($groupId); //var_dump($customers); //echo $customers[0];
			}
			return $users;		
		}
		
		function getIdByGroupName($groupName){
			$db = JFactory::getDBO();
			$db->setQuery($db->getQuery(true)
				->select('id')
				->from("#__usergroups")
				->where("title = '".$groupName."'")
				->setLimit(1)
			);
			return $db->loadResult();
		}
		
		function raiseError($ex) {
			//send 
			//sendMail($ex->getMessage(), "Error en el sitio web", $to);						
		}		
		
		// Sort an array by a specific key. Maintains index association
		/* Usage:
			$people = array(
				'id' => 12345,
				'first_name' => 'Joe',
				'surname' => 'Bloggs',
				'age' => 23,
				'sex' => 'm'
			};
			sortArray($people, 'age', SORT_DESC); // Sort by oldest first
		*/
		function sortArray($array, $on, $order=SORT_ASC)
		{
			$new_array = array();
			$sortable_array = array();

			if (count($array) > 0) {
				foreach ($array as $k => $v) {
					if (is_array($v)) {
						foreach ($v as $k2 => $v2) {
							if ($k2 == $on) {
								$sortable_array[$k] = $v2;
							}
						}
					} else {
						$sortable_array[$k] = $v;
					}
				}

				switch ($order) {
					case SORT_ASC:
						asort($sortable_array);
					break;
					case SORT_DESC:
						arsort($sortable_array);
					break;
				}

				foreach ($sortable_array as $k => $v) {
					$new_array[$k] = $array[$k];
				}
			}

			return $new_array;
		}
		
		/* Display child links */
		function childLinks($pageTitle) {
			$html = '<div class="column-center a-start column-pad"><p>Encontrá toda la información sobre ';
			$html .= $pageTitle . ' navegando los siguientes links</p>';
			$html .= '<div id="showChildPages" class="row-center j-start">';
			$html .= '<ul style="padding-top:10px;">';
			$sitemenu = JFactory::getApplication('site')->getMenu();
			$mainmenu = $sitemenu->getItems("menutype", "mainmenu");
				
			foreach($mainmenu as $menu) {	
				if ($menu->parent_id == 1 && strtolower($menu->title) == strtolower($pageTitle)) {
					$html .= '<li>';
					if ($menu->home == "1") {
						$menu->route = "";
					}
					$html .= '<a href="/' . $menu->route . '">' . $menu->title . '</a>';
					$html .= $this->recursiveChildLinks($mainmenu, $menu->id);
					$html .= '</li>';
				}
			}
			return $html .= '</div></div>';
		}
		
		function recursiveChildLinks($items, $parentId) {
			$hasChilds = false;
			$html = "";
			foreach($items as $item) {
				if ($item->parent_id == $parentId) {
					if (!$hasChilds) {
						$html .= '<ul style="padding-top:10px;">';
						$hasChilds = true;
					}
					$html .= '<li>';
					$html .= '<a href="/' . $item->route . '">' . $item->title . '</a>';
					$html .= $this->recursiveChildLinks($items, $item->id);
					$html .= '</li>';
				}
			}
			if ($hasChilds) {
				$html .= '</ul>';
			}
			return $html;
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
		
		function serverInfo(){
			/*
			echo("site config:".LB);
			$config = JFactory::getConfig();
			var_dump($config);
			echo LB.LB;
			*/
			$indicesServer = array('PHP_SELF', 
				'argv', 
				'argc', 
				'GATEWAY_INTERFACE', 
				'SERVER_ADDR', 
				'SERVER_NAME', 
				'SERVER_SOFTWARE', 
				'SERVER_PROTOCOL', 
				'REQUEST_METHOD', 
				'REQUEST_TIME', 
				'REQUEST_TIME_FLOAT', 
				'QUERY_STRING', 
				'DOCUMENT_ROOT', 
				'HTTP_ACCEPT', 
				'HTTP_ACCEPT_CHARSET', 
				'HTTP_ACCEPT_ENCODING', 
				'HTTP_ACCEPT_LANGUAGE', 
				'HTTP_CONNECTION', 
				'HTTP_HOST', 
				'HTTP_REFERER', 
				'HTTP_USER_AGENT', 
				'HTTPS', 
				'REMOTE_ADDR', 
				'REMOTE_HOST', 
				'REMOTE_PORT', 
				'REMOTE_USER', 
				'REDIRECT_REMOTE_USER', 
				'SCRIPT_FILENAME', 
				'SERVER_ADMIN', 
				'SERVER_PORT', 
				'SERVER_SIGNATURE', 
				'PATH_TRANSLATED', 
				'SCRIPT_NAME', 
				'REQUEST_URI', 
				'PHP_AUTH_DIGEST', 
				'PHP_AUTH_USER', 
				'PHP_AUTH_PW', 
				'AUTH_TYPE', 
				'PATH_INFO', 
				'ORIG_PATH_INFO') ; 

			$html = "";
			$html .= '<table cellpadding="10">' ; 
			foreach ($indicesServer as $arg) { 
				if (isset($_SERVER[$arg])) { 
					$html .= '<tr><td>'.$arg.'</td><td>' . $_SERVER[$arg] . '</td></tr>' ; 
				} 
				else { 
					$html .= '<tr><td>'.$arg.'</td><td>-</td></tr>' ; 
				} 
			} 
			$html .= '</table>' ;
			echo $html;
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
		function dateDifference($date_1 , $date_2 , $differenceFormat = '%a' )
		{
			$datetime1 = date_create($date_1);
			$datetime2 = date_create($date_2);
			
			$interval = date_diff($datetime1, $datetime2);
			
			return $interval->format($differenceFormat);
			
		}
		
		// Smart Sub-string privates
		
		//after ('@', 'biohazard@online.ge');
		//returns 'online.ge'
		//from the first occurrence of '@'

		//before ('@', 'biohazard@online.ge');
		//returns 'biohazard'
		//from the first occurrence of '@'

		//between ('@', '.', 'biohazard@online.ge');
		//returns 'online'
		//from the first occurrence of '@'

		//after_last ('[', 'sin[90]*cos[180]');
		//returns '180]'
		//from the last occurrence of '['

		//before_last ('[', 'sin[90]*cos[180]');
		//returns 'sin[90]*cos['
		//from the last occurrence of '['

		//between_last ('[', ']', 'sin[90]*cos[180]');
		//returns '180'
		//from the last occurrence of '['
		function after ($inThis, $inthat)
		{
			if (!is_bool(strpos($inthat, $inThis)))
			return substr($inthat, strpos($inthat,$inThis)+strlen($inThis));
		}

		function after_last ($inThis, $inthat)
		{
			if (!is_bool($this->strrevpos($inthat, $inThis)))
			return substr($inthat, $this->strrevpos($inthat, $inThis)+strlen($inThis));
		}

		function before ($inThis, $inthat)
		{
			return substr($inthat, 0, strpos($inthat, $inThis));
		}

		function before_last ($inThis, $inthat)
		{
			return substr($inthat, 0, $this->strrevpos($inthat, $inThis));
		}

		function between ($inThis, $that, $inthat)
		{
			return $this->before($that, $this->after($inThis, $inthat));
		}

		function between_last ($inThis, $that, $inthat)
		{
		 return $this->after_last($inThis, $this->before_last($that, $inthat));
		}
		
		// use strrevpos private in case your php version does not include it
		function strrevpos($instr, $needle)
		{
			$rev_pos = strpos (strrev($instr), strrev($needle));
			if ($rev_pos===false) return false;
			else return strlen($instr) - $rev_pos - strlen($needle);
		}
		
		/*
		Usage: 
		$str = '|apples}';
		echo startsWith($str, '|'); //Returns true
		echo endsWith($str, '}'); //Returns true	
		*/
		function startsWith($haystack, $needle)
		{
			 $length = strlen($needle);
			 return (substr($haystack, 0, $length) === $needle);
		}

		function endsWith($haystack, $needle)
		{
			$length = strlen($needle);
			if ($length == 0) {
				return true;
			}

			return (substr($haystack, -$length) === $needle);
		}
	}
?>