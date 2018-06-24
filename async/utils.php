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
		
		function sendMail($content, $subject="", $to="", $toName="", $fromName="", $arraySearch=array(), $arrayReplace=array()) 
		{
			// default data
			$config = JFactory::getConfig();
			if(!isset($to) || strlen(trim($to)) == 0)
				$to = $config->get('mailfrom');
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
			//$mail->addReplyTo($config->get('mailfrom'), $fromName);
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
			$to="info@programar.com.ar";
			//send 
			//sendMail($ex->getMessage(), "Error en el sitio web", $to);						
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