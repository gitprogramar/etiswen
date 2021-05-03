<?php /*********** NO SPACE BEFORE THIS LINE!!!!!!!!!!!!!! *****************/
	// Utils Library
	define( '_JEXEC', 1 );
	define('JPATH_ROOT', realpath(dirname(__FILE__).'/../') );	
	
	class Utils {
		protected $start;
		protected $startWatch;
		protected $endWatch;

		public function __construct($base=null) {
			if(isset($base))
				define('JPATH_BASE', $base);
			elseif(isset($_SERVER['REQUEST_URI'])) {
				define('JPATH_BASE', $_SERVER['REQUEST_URI']);				
			}	
			require_once ( JPATH_ROOT .'/includes/defines.php');
			require_once ( JPATH_ROOT .'/includes/framework.php');
			require_once ( JPATH_ROOT .'/libraries/joomla/database/factory.php');
			require_once ( JPATH_ROOT .'/libraries/vendor/joomla/filesystem/src/Folder.php');
			require_once ( JPATH_ROOT .'/libraries/vendor/phpmailer/phpmailer/class.phpmailer.php');
			require_once ( JPATH_ROOT .'/libraries/vendor/phpmailer/phpmailer/class.smtp.php');
			require_once ( JPATH_ROOT .'/administrator/components/com_fields/helpers/fields.php');	
			require_once ( JPATH_ROOT .'/mercadopago/mercadopago.php');			
			if (!class_exists("Thumbnail")) {
				require_once(JPATH_ROOT .'/libraries/thumbnail/thumbnail.inc.php');
			}
			jimport('joomla.filesystem.folder');
			jimport('joomla.filesystem.file');
			jimport('joomla.filesystem.path');
			
			$this->start = JFactory::getDate()->toSQL();
		}
		
		function sendMail($content, $subject="", $to="", $toName="", $from="", $fromName="", $arraySearch=array(), $arrayReplace=array()) 
		{
			// default data
			$config = JFactory::getConfig();
			$customer = $_SESSION["customer"];
			if(!isset($to) || strlen(trim($to)) == 0)
				$to = $config->get('mailfrom');
			if(!isset($from) || strlen(trim($from)) == 0)
				$from = $config->get('mailfrom');
			if(!isset($fromName) || strlen(trim($fromName)) == 0)
				$fromName = $config->get('fromname');			
			if(!isset($subject) || strlen(trim($subject)) == 0) {
				$subject =  $customer->subject.' - '.$config->get('sitename');
			}
			else if(strpos($subject, $config->get('sitename')) == false) {
				$subject =  $subject.' - '.$config->get('sitename');
			}
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
				define('STORAGE', $storage);
				// skip robots
				echo '<html><head><meta name="robots" content="noindex, nofollow"></head><body></body></html>'.LB;
			}
			
			$this->startWatch = microtime(true);					
			$this->login("cron", $pass, "site");
		}
		
		function cronEnd() {
			$this->endWatch = microtime(true);
			$execTime = round($this->endWatch - $this->startWatch, 2);
			
			$result = LB.LB."Task performance:";
			$result .= LB."Started: ".$this->start;						
			$result .= LB."Execution Time: ".$execTime .' seconds.';
			echo $result;
			$this->logout("site");
		}
		
		function userIsLogin($application) {
			$app = JFactory::getApplication($application);
			$user = JFactory::getUser(); 
			$userId = $user->get('id');	
			if($userId == 0) {		
				echo "Access denied.";
				exit();
			}
		}
		
		/* Set enterprise data session */
		function enterpriseSession($profile="manager", $templateId=null, $themeId=null) {
			if (session_status() == PHP_SESSION_NONE) {
				session_start();
			}
			$customer = $_SESSION["customer"];			
			if($profile == "admin") {
				$url = strtok($_SERVER["REQUEST_URI"],'?');
				if(strpos($url, "plantilla-") === false) {
					if(isset($customer) && $customer->groupId == 8) {
						$language = $this->languageSetFromURL();
						if($customer->language == $language->current)
							return;
					}					
					// load enterprise admin
					$enterprise = $this->enterpriseGet($profile, 0, 0);					
				}
				else {
					// enterprise logic manager
					$urls = explode("-", $url);
					$enterprise = $this->enterpriseGet($profile, $urls[count($urls)-1], rand (1, 4)); /* get template id from the url */
				}				
			}
			else {
				if(isset($customer)) {
					$language = $this->languageSetFromURL();
					if($customer->language == $language->current)
						return;
				}
				// load regular enterprise
				$enterprise = $this->enterpriseGet($profile, $templateId, $themeId);
			}
			
			$_SESSION["customer"] = $enterprise->customer;
			$_SESSION["template"] = $enterprise->template;
			$_SESSION["theme"] = $enterprise->theme;
			$_SESSION["language"] = $enterprise->language;						
		}
		
		/* Get enterprise data */
		function enterpriseGet($profile="manager", $templateId=null, $themeId=null) {
			// language
			$language = new stdClass();			
			$language->default = $this->languageGetDefault();						
			// installed languages			
			$language->installed = $this->languagesGet();
			// get from url
			$language->current = $this->languageGetCurrent($language);
			// preferred language
			$language->browser = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);
			
			if(strpos($profile, '-') !== false) // fix user from cron task
				$user = $this->userGetByName($profile);
			else 
				$user = $this->userGetByName($profile.(isset($language->current) ? '-'.$language->current : ''));
			$fields = FieldsHelper::getFields('com_users.user',  $user);
			$parses = array(' ', '(', ')', '+', '-');	
			$json = json_decode($user->params);
			// customer
			$customer = new stdClass();
			$customer->id = $user->id;
			$customer->email = $user->email;			
			$customer->username = $user->username;
			$customer->groupId = array_values($user->groups)[0];
			$customer->language = $this->before('-', $json->{'language'});
			$menu = JFactory::getApplication('site')->getMenu();			
			$customer->homemenu = $menu->getDefault();
			$customer->customername = "";
			$customer->customernameParsed = "";
			$customer->domain = "";
			$customer->templateId = "1";
			$customer->themeId = "1";
			$customer->message = "";
			$customer->subject = "";
			$customer->bufferId = "";
			$customer->bufferToken = "";
			$customer->bufferSecret = "";
			$customer->dollar = "";
			
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
			
			// template
			$template = new stdClass();			
			
			// theme
			$theme = new stdClass();			

			foreach($fields as $field) {
				// customer
				if($field->name == "templateid")
					$customer->templateId = isset($templateId) ? $templateId : $field->value;
				if($field->name == "themeid")
					$customer->themeId = isset($themeId) ? $themeId : $field->value;
				elseif($field->name == "default-message")
					$customer->message = $field->value;
				elseif($field->name == "default-subject")
					$customer->subject = $field->value;
				elseif($field->name == "customername") {
					$customer->customername = $field->value;
					$customer->customernameParsed = str_replace(' ', '%20', $field->value);
				}
				elseif($field->name == "domain")
					$customer->domain = $field->value;
				elseif($field->name == "buffer-id")
					$customer->bufferId = $field->value;
				elseif($field->name == "buffer-token")
					$customer->bufferToken = $field->value;
				elseif($field->name == "buffer-secret")
					$customer->bufferSecret = $field->value;
				elseif($field->name == "dollar")
					$customer->dollar = $field->value;
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
					
				// template				
				elseif($field->name == "head".$customer->templateId)
					$template->head = $field->value;
				elseif($field->name == "fontfirst".$customer->templateId)
					$template->fontfirst = $field->value;
				elseif($field->name == "fontsecond".$customer->templateId)
					$template->fontsecond = $field->value;
				elseif($field->name == "weightfirst".$customer->templateId)
					$template->weightfirst = $field->value;
				elseif($field->name == "weightsecond".$customer->templateId)
					$template->weightsecond = $field->value;	
				
				// theme				
				elseif($field->name == "first".$customer->themeId)
					$theme->first = $field->value;
				elseif($field->name == "second".$customer->themeId)
					$theme->second = $field->value;
				elseif($field->name == "third".$customer->themeId)
					$theme->third = $field->value;
				elseif($field->name == "menu".$customer->themeId)
					$theme->menu = $field->value;
				elseif($field->name == "bgheader".$customer->themeId)
					$theme->bgheader = $field->value;
				elseif($field->name == "bgbody".$customer->themeId)
					$theme->bgbody = $field->value;
				elseif($field->name == "bgfooter".$customer->themeId)
					$theme->bgfooter = $field->value;
				elseif($field->name == "extra".$customer->themeId) {
					$theme->extra = $field->value;
					//break;
				}
			}
			$enterprise = new stdClass();
			$enterprise->customer = $customer;
			$enterprise->template = $template;
			$enterprise->theme = $theme;			
			$enterprise->language = $language;
			return $enterprise;
		}	
		
		function isHomePage() {
			$customer = $_SESSION["customer"];
			$homes = array();
			$homes = explode(',',$customer->homemenu->note);			
			$url = substr(strtok($_SERVER["REQUEST_URI"],'?'),1);
			if($url == "" || $url == $customer->homemenu->alias || in_array($url, $homes))
				return true;
			return false;
		}
		
		function themeGet($themeId) {
			JFactory::getApplication("site");
			$id = $this->userGetByName("manager");
			$user = JFactory::getUser($id);
			$fields = FieldsHelper::getFields('com_users.user',  $user);
			
			// theme
			$theme = new stdClass();
			$theme->first = "";
			$theme->second = "";
			$theme->third = "";
			$theme->menu = "";
			$theme->bgheader = "";
			$theme->bgbody = "";
			$theme->bgfooter = "";
			$theme->extra = "";

			foreach($fields as $field) {
				// theme		
				if($field->name == "first".$themeId)
					$theme->first = $field->value;
				elseif($field->name == "second".$themeId)
					$theme->second = $field->value;
				elseif($field->name == "third".$themeId)
					$theme->third = $field->value;
				elseif($field->name == "menu".$themeId)
					$theme->menu = $field->value;
				elseif($field->name == "bgheader".$themeId)
					$theme->bgheader = $field->value;
				elseif($field->name == "bgbody".$themeId)
					$theme->bgbody = $field->value;
				elseif($field->name == "bgfooter".$themeId) {
					$theme->bgfooter = $field->value;
				}
				elseif($field->name == "extra".$themeId) {
					$theme->extra = $field->value;
					//break;
				}
			}		
			$response = array();
			$response["value"] = $theme;
			echo json_encode($response);
			return;
		}
		
		function rgbColor($hexColor) {
			list($r, $g, $b) = sscanf($hexColor, "#%02x%02x%02x");
			return "$r, $g, $b";			
		}
		
		function userGetByName($name) {			
			$db = JFactory::getDBO();
			$db->setQuery($db->getQuery(true)
				->select('*')
				->from("#__users")
				->where("LOWER(name) = LOWER('".$name."')")
			);
			$id = $db->loadResult();
			return JFactory::getUser($id);
		}
		
		function fileGet($directory, $filter="jpg|png|gif|bmp|mp4|webm|ogg") {			
			if(!$directory) return false;
			$directory = JPath::clean(JPATH_BASE."/$directory");
			// Not found the directory
			if(!is_dir($directory)) return false;
			// Get all files in the directory
			$files	= JFolder::files($directory, '([^\s]+(\.(?i)('.$filter.'))$)', true, true,
									 array('index.html', '.svn', 'CVS', '.DS_Store', '__MACOSX', '.htaccess'), array());
			foreach($files as $key=>$path)
			{
				$path = substr($path, strlen(JPATH_BASE) - strlen($path) + 1);
				$path = JPath::clean($path, "/");
				$files[$key] = rtrim(JURI::base(true), "/")."/$path";
			}

			// Get files
			return $files;			
		}
		
		function fileGetPaged($page, $size, $directory, $filter="jpg|png|gif|bmp|mp4|webm|ogg") {
			JFactory::getApplication("site");			
			$realpath = JPath::clean($this->before_last('/',getcwd())."/$directory");						
			// Not found the path
			if(!is_dir($realpath)) return false;
			// Get all files in the realpath
			$files	= JFolder::files($realpath, '([^\s]+(\.(?i)('.$filter.'))$)', true, true,
									 array('index.html', '.svn', 'CVS', '.DS_Store', '__MACOSX', '.htaccess'), array());
			$total = count($files);
			$totalPages = ceil($total/$size);
			$pagedFiles = array();
			$counter = 0;
			$pageCounter = 1;
			foreach($files as $key=>$name)
			{
				$pagedFiles[] = $this->after_last('/',$name); 
				$counter++;				
				if($size == $counter) {
					if($page == $pageCounter) {
						break;
					}
					$pageCounter++;
					$counter = 0;
					$pagedFiles = [];
				}
			}
			$response = array();
			$response["files"] = $pagedFiles;
			$response["total"] = $total;
			$response["page"] = $pageCounter;
			$response["pages"] = $totalPages;
			echo json_encode($response);
			return;
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
		
		function sessionClear() {
			JFactory::getApplication("site");
			session_unset();
			$response = array();
			$response["value"] = "Session cleared";
			echo json_encode($response);			
			return;
		}		
		
		function raiseError($ex) {
			//send 
			//sendMail($ex->getMessage(), "Error en el sitio web", $to);						
		}				
		
		/*Get all installed laguages. 		
		  Note: used because JLanguage::getKnownLanguages or JLanguageHelper::getKnownLanguages() could
		  be empty outside the index.php page call.
		*/
		function languagesGet() {			
			$db = JFactory::getDBO();
			$db->setQuery($db->getQuery(true)
				->select('lang_code')
				->from("#__languages")
			);
			$installed = array();
			// installed languages
			foreach($db->loadColumn() as $lang)
				$installed[] = $this->before('-', $lang);
			return $installed;
		}
		
		function languageGetDefault() {
			$doc = JFactory::getDocument();
			return  $this->before('-', $doc->language);			
		}
		
		function languageGetCurrent($language) {
			$url = $_SERVER['REQUEST_URI'];			
			foreach($language->installed as $lang) {
				if($url == '/'.$lang || strpos($url, '/'.$lang.'/') !== false)
					return $lang;
			}
			return $language->default;
		}
		
		function languageSetFromURL() {
			$language = $_SESSION["language"];
			$url = $_SERVER['REQUEST_URI'];
			foreach($language->installed as $lang) {
				if($url == '/'.$lang || strpos($url, '/'.$lang.'/') !== false) {
					$language->current = $lang;
					$_SESSION["language"] = $language;
					return;
				}
			}
			$language->current = $language->default;
			$_SESSION["language"] = $language;
			return $language;
		}
		
		/* bind language combo and alternate lang link*/
		function localizationGet() {
			$language = $_SESSION["language"];
			$menu = JFactory::getApplication('site')->getMenu();
			$active = $menu->getActive();			
			//current
			$current = JUri::base().($active->home == 1?'':$active->route);
			$html = '<link rel="alternate" hreflang="'.$language->current.'" href="'.$current.'" />';
			$default = '';
			if(strlen(trim($active->note))>0) {
				$paths = explode(',',$active->note);
				foreach($language->installed as $lang) {						
					if($lang == $language->current)
						continue;
					foreach($paths as $path) {
						$alternative = JUri::base().($path=='/'?'':$path);
						$html .= '<link rel="alternate" hreflang="'.$lang.'" href="'.$alternative.'" />';
						if(strlen($default) == 0)
							$default = $alternative;
					}
				}
			}
			//default
			$html .= '<link rel="alternate" hreflang="x-default" href="'.($language->current == $language->default ? $current : $default).'" />';
			
			echo $html;
		}
		
		function mercadopago() {
			$db = JFactory::getDBO();
			$db->setQuery($db->getQuery(true)
				->select('params')
				->from("#__extensions")
				->where("element = 'com_rokquickcart'")
				->setLimit(1)
			);
			$result = json_decode($db->loadResult());
			$checkout_mode = $result->{'checkout_mode'};
			
			// mercado pago credentials
			$mp;
			if($checkout_mode == 'sandbox') {
				// sandbox credentials
				$mp = new MP ("4464481893117718", "a3OWd0dDQx4ZMD8ihqotWUasoeH6UsHH");
			}
			else {
				// add production credentials
				$mp = new MP ($result->{'client_id'}, $result->{'client_secret'});
			}
			return $mp;
		}
		
		/*Removes all html tags, javascript and styles elements*/
		function htmlParse($html) {			
			return strip_tags(preg_replace('/(<script[^>]*>.+?<\/script>|<style[^>]*>.+?<\/style>)/s', '',  str_replace('><', '> <', $html)));
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
		
		/* Validate string is a valid date */
		/*
			validateDate('2012-02-28 12:12:12'); # true
			validateDate('2012-02-30 12:12:12'); # false
		
		*/
		function dateIsValid($date, $format = 'Y-m-d H:i:s')
		{
			$d = DateTime::createFromFormat($format, $date);
			return $d && $d->format($format) == $date;
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
		/**
	 * @param $full_image_path
	 * @param $thumb_image_path
	 * @param $image_width
	 *
	 * @return bool
	 */
	public static function createThumbImage($full_image_path, $thumb_image_path, $image_width)
	{

		if (!JFile::exists($full_image_path)) {
			return false;
		}
		if (!JFolder::exists(dirname($thumb_image_path))) {
			JFolder::create(dirname($thumb_image_path));
		}
		$current_size = 0;

		if (JFile::exists($thumb_image_path)) {
			$existing_thumb = new Thumbnail($thumb_image_path);
			$current_size   = $existing_thumb->getCurrentWidth();
			$existing_thumb->destruct();
		}

		// create a new shelf image if the it doesnt exists, is older the full image, of if the image size has changed
		if (!JFile::exists($thumb_image_path) || (filemtime($full_image_path) > filemtime($thumb_image_path)) || $current_size != $image_width) {
			$thumb = new Thumbnail($full_image_path);

			if ($thumb->error) {
				echo "ERROR: " . $thumb->errmsg . ": " . $full_image_path;
				return false;
			}
			$thumb->resize($image_width);
			if (!is_writable(dirname($thumb_image_path))) {
				$thumb->destruct();
				return false;
			}
			$thumb->save($thumb_image_path);
			$thumb->destruct();
		}
		return true;
	}
}
?>