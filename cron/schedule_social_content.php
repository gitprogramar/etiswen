<?php /*********** NO SPACE BEFORE THIS LINE!!!!!!!!!!!!!! *****************/
	/*
	 * Daily cron task. Post article content to social networks: facebook, twitter, google plus.
	 */
	 
	//php -f /home/u383829915/public_html/cron/schedule_social_content.php param category_id published client_id client_secret access_token posts_count every_each_day
	
	$utils;
	try 
	{
		define( '_JEXEC', 1 );
		define('JPATH_ROOT', realpath(dirname(__FILE__).'/../') );
		require_once ( JPATH_ROOT .'/async/utils.php');			
		
		$utils = new Utils();
		$utils->cronStart((string)$argv[1], "/cron/social_content_storage_".(string)$argv[2].".txt");
		
		/* get params */
		$category_id = (string)$argv[2]; // category_id
		$published = (string)$argv[3]; // published article?		
		$client_id = (string)$argv[4]; // client_id	
		$client_secret = (string)$argv[5]; // client_secret	
		$access_token = (string)$argv[6]; // access_token
		$posts_count = (string)$argv[7]; // posts_count
		$every_each_day = (string)$argv[8]; // every_each_day

		// domain. NOTE: change protocol as needed.
		$config = JFactory::getConfig();
		$url = "http://".$utils->after('@', $config->get('mailfrom'));	
		
		if(checkTaskMustRun($every_each_day)) {
			dowork($category_id, $published, $client_id, $client_secret, $url, $access_token, $posts_count);	
		}
		
		$utils->cronEnd();
	}
	catch(Exception $ex) {
		if(isset($utils))
			$utils->raiseError($ex);
		echo $ex->getMessage();
	}
	
	// logic to start processing the posts
	function dowork($category_id, $published, $client_id, $client_secret, $callback_url, $access_token, $posts_count)
	{	
		// try access buffer API
		$buffer = new BufferApp($client_id, $client_secret, $callback_url, $access_token); //var_dump($buffer);
		$profiles = array();
		if (!$buffer->ok) {
			echo 'Can not connect to Buffer!';
			var_dump($buffer);
			return false;
		} else {
			$profiles = $buffer->go('/profiles');				
			if (!is_array($profiles) || count($profiles) == 0) {
				echo "No profiles available. Please checkout in https://buffer.com/app/profile/";
				return false;
			}
		}	
	
		$db     = JFactory::getDBO();
		jimport( 'joomla.access.access' );
		
		// get article content
		$query  = $db->getQuery(true);
		$query->select('id, introtext');
		$query->from('#__content');		
		$query->where('catid = '.$category_id); // social Category ID
		$query->where('state = '.$published); // published/not published articles		
		
		$db->setQuery($query);
		$articles = $db->loadRowList();
		
		// return if no articles found
		if(count($articles) == 0){
			echo 'No articles found for Social category Id = '.$category_id;
			return;
		}
					
		//filter articles randomly
		$quantity = intval($posts_count);
		if(count($articles) < intval($posts_count)){
			$quantity = count($articles);
		}
		$randomArticles = array_random_assoc($articles, $quantity);
		
		// get postImage from /images/social_logo
		$socialLogos = array();
		/*
		$directory	= JPath::clean( JPATH_BASE . "/images/social_logo" );
		// directory exists
		if(is_dir($directory)){
			$filter		= '([^\s]+(\.(?i)(jpg|png|gif|bmp))$)';
			$exclude	= array('index.html', '.svn', 'CVS', '.DS_Store', '__MACOSX', '.htaccess');
			$excludefilter = array();			
			// Get all images in the directory
			$files	= JFolder::files($directory, $filter, true, true, $exclude, $excludefilter);
			foreach($files as $key=>$path)
			{
				$path = substr($path, strlen(JPATH_BASE) - strlen($path) + 1);
				$path = JPath::clean( $path, "/" );
				$files[$key] = $callback_url."/$path";
			}			
			//echo "Files: ";
			//var_dump($files);
			$socialLogos = $files;
		}
		*/

		// UTF-8 format html article
		$htmlStart = "<!DOCTYPE html><html lang=\"es\"><head><meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\"><meta charset=\"UTF-8\"><meta http-equiv=\"content-type\" content=\"text/html;charset=UTF-8\"></head><body>" . "\r\n";
		$htmlEnd = "</body></html>";
		foreach($randomArticles as $article): 			
			if(strlen($article[1]) == 0) {
				continue;
			}
			// exit on post count
			
			// clear variables
			$postTitle = ""; 
			$postDesc = "";	
			$postText = "";
			$postLink = "";
			$postImage = "";
			// read html
			$dom = new DOMDocument;
			$dom->loadHTML($htmlStart . $article[1] . $htmlEnd);
			
			recursiveChildNode($dom, $postTitle, $postDesc, $postLink, $postImage);
			
			// set postlink if not defined
			if(strlen($postLink) == 0){
				// get aticle link
				$query  = $db->getQuery(true);
				$query->select('path');
				$query->from('#__menu');
				$query->where("link like '%id=" . $article[0] . "'");
				$query->where("published = 1");
				$query->where("LOWER(note) <> 'oculto'");
				$db->setQuery($query);
				$menus = $db->loadRowList();
				if(count($menus) > 0) {
					$postLink .= $callback_url . "/" . array_values(array_values($menus)[0])[0];				
				}
				else {
					$postLink = "";
				}
			}
			
			// set postImage if not defined
			if(strlen($postImage) == 0){
				if(count($socialLogos) > 0){
					$postImage = array_rand(array_flip($socialLogos), 1);
					//echo "Image: ".$postImage;
				}				
			}
			
			//echo "Title:" . $postTitle;
			//echo "<br> Desc:" . $postDesc;
			send_posts($buffer, $profiles, $postTitle, $postDesc, $postLink, $postImage);
			
		endforeach;	
	}	
	
	// send each post to Buffer profiles (Face, Twitter, Google)
	/*
	Youtube
		-Link
		-Link, Text

	Image
		-Image
		-Image, Text
		-Image, Link
		-Image, Text, Link

	Text
		-Text
		-Text, Link
		
	Link
		-Link
	*/
	function send_posts($buffer, $profiles, $postTitle, $postDesc, $postLink, $postImage) {		
		foreach ($profiles as $profile) {
			//var_dump($profile);
			$options = array();
			$postText =  formatPostbyProfile($profile, $postTitle, $postDesc); 
			if(strpos($postLink, "youtube.com/") !== false && strlen($postText) == 0) {
				// only youtube playlist
				$options = array('media[link]' => $postLink, 'profile_ids[]' => $profile->id, 'shorten' => false);
			}
			elseif(strpos($postLink, "youtube.com/") !== false && strlen($postText) != 0){
				// only youtube playlist and text
				$options = array('text' => $postText, 'media[link]' => $postLink, 'profile_ids[]' => $profile->id, 'shorten' => false);
			}
			elseif(strlen($postImage) != 0 && strlen($postText) == 0 && strlen($postLink) == 0) {
				// only image
				$options = array('media[picture]' => $postImage, 'profile_ids[]' => $profile->id);
			}
			elseif(strlen($postImage) != 0 && strlen($postText) != 0 && strlen($postLink) == 0) {
				// only image and text
				$options = array('text' => $postText, 'media[picture]' => $postImage, 'media[thumbnail]' => $postImage, 'profile_ids[]' => $profile->id);
			}
			elseif(strlen($postImage) != 0 && strlen($postText) == 0 && strlen($postLink) != 0) {
				// only image and link
				$options = array('text' => $postText, 'media[link]' => $postLink, 'media[picture]' => $postImage, 'profile_ids[]' => $profile->id);
			}
			elseif(strlen($postImage) != 0 && strlen($postText) != 0 && strlen($postLink) != 0) {
				// All: image and text and link
				$options = array('text' => $postText, 'media[link]' => $postLink, 'media[picture]' => $postImage, 'profile_ids[]' => $profile->id);
			}
			elseif(strlen($postText) != 0 && strlen($postImage) == 0 && strlen($postLink) == 0) {
				// only text
				$options = array('text' => $postText, 'profile_ids[]' => $profile->id);
			}					
			elseif(strlen($postText) != 0 && strlen($postImage) == 0 && strlen($postLink) != 0) {
				// only text and link
				$options = array('text' => $postText, 'media[link]' => $postLink, 'profile_ids[]' => $profile->id);
			}
			elseif(strlen($postText) == 0 && strlen($postImage) == 0 && strlen($postLink) != 0) {
				// only link
				$options = array('media[link]' => $postLink, 'profile_ids[]' => $profile->id);
			}
			else {
				// default
				$options = array('text' => $postText, 'media[link]' => $postLink, 'profile_ids[]' => $profile->id);
			}

			// send to buffer api
			$buffer->go('/updates/create', $options);
			
			// print restuls
			echo LB.'Posted to '.$profile->service.' profile: ';
			echo LB."Text: ".$postText;
			echo LB."Link: ".$postLink;
			if(strpos($postLink, "youtube.com/") === false) {
				if(strlen($postImage) != 0) {
					echo LB."Image: ".$postImage;
				}
			}
			echo LB.'--------------'.LB;
		}
	}
	
	// format the post, every social network has limitations
	/*
	Twitter: 140
	Pinterest: 500
	Instagram: 2,200
	Facebook Profiles: 5,000
	Facebook Pages: 5,000
	Facebook Groups: 5,000
	LinkedIn Profiles: 700
	LinkedIn Pages: 600
	Google+ Profiles: 5,000
	Google+ Pages: 5,000
	*/	
	function formatPostbyProfile($profile, $postTitle, $postDesc) {		
		$text = "";
		if($profile->service == "twitter") {
			$text = $postTitle . " " . $postDesc;
			correctLenght(130, $text);
		}
		elseif($profile->service == "facebook" || $profile->service == "google") {
			$text = $postTitle . "\r\n" . $postDesc;
			correctLenght(4900, $text);
		}
		else {
			$text = $postTitle . " " . $postDesc;
		}
		return $text;
	}
	
	// recursively substring the value based on max length and removing from the last "." when necesary
	function correctLenght($length, &$text) {		
		$utils = new Utils();
		if(strlen($text) > $length) {
			$dot = strrpos($text, ".");
			if($dot !== false){
				$text = $utils->before_last(".", $text);
				if(strlen($text) > $length) {
					correctLenght($length, $text);
				}				
			}
			else {
				$text = substr($text, 0, $length);
			}
		}
	}
	
	// recursively iterates throght all html dom elements and set the title and desc
	function recursiveChildNode($element, &$postTitle, &$postDesc, &$postLink, &$postImage) {
		$utils = new Utils();
		foreach($element->childNodes as $child) {
			//echo "<br>Child: <br>";
			//var_dump($child);
			if(isset($child->tagName)) {
				if($child->tagName == 'h1' || ($child->tagName == 'p' && strlen($postTitle) == 0)) {
					$postTitle =  strip_tags($child->textContent);
				}
				elseif($child->tagName == 'p' && strlen($postDesc) == 0) {
					if(!$utils->endsWith($postTitle, ".")) {
						$postTitle .= "."; // append '.' if not have it
					}
					$postDesc = strip_tags($child->textContent);
				}
				elseif($child->tagName == 'iframe' && strlen($postLink) == 0) {
					//echo 'iframe: ';
					//var_dump($child->attributes);
					foreach($child->attributes as $attribute) {
						if($attribute->name == "src"){
							// overwrite postLink only for youtube playlists
							if(strpos($attribute->nodeValue, "youtube.com/") !== false && strpos($attribute->nodeValue, "list=") !== false) {
								$postLink = "https://www.youtube.com/playlist?list=" . $utils->after_last("=", $attribute->nodeValue);
							}
						}
					}
				}
				elseif($child->tagName == 'img' && strlen($postImage) == 0) {
					//echo 'img: ';
					//var_dump($child->attributes);
					foreach($child->attributes as $attribute) {
						if($attribute->name == "src"){
							$postImage = $attribute->nodeValue;
							//echo $postImage;
						}
					}
				}
				else {
					recursiveChildNode($child, $postTitle, $postDesc, $postLink, $postImage);
				}
			}			
		}	
	}
	
	// randomly selects X items of an array
	function array_random_assoc($arr, $num = 1) {
		$keys = array_keys($arr);
		shuffle($keys);
		
		$r = array();
		for ($i = 0; $i < $num; $i++) {
			$r[$keys[$i]] = $arr[$keys[$i]];
		}
		return $r;
	}
	
	// check wheter a task must run based on last run date and every day variables
	function checkTaskMustRun($everyEachDays){
		createStorage(STORAGE, $everyEachDays);
		
		$currentDate = JFactory::getDate()->format('Y-m-d');
		$prevDate = new DateTime(file_get_contents(STORAGE));
		$prevDate = $prevDate->format('Y-m-d');
		
		// day difference
		$utils = new Utils();
		$interval = $utils->dateDifference($prevDate, $currentDate);
		
		// check with the every day parameter
		if($interval >= $everyEachDays) {
			// update storage			
			file_put_contents(STORAGE, $currentDate);
			return true;
		}
		
		echo LB."This task runs every ".$everyEachDays." day/s".LB;
		echo "Last time it ran: ".$prevDate.LB;
		
		return false;
	}
	
	// create storage file if not already exists
	function createStorage($everyEachDays) {
		if(file_exists(STORAGE) !== true){ // if file not exists
			$info = getdate();	
			$currentDate = date_create($info['year'].'-'.$info['mon'].'-'.$info['mday']); // server date
			date_sub($currentDate, date_interval_create_from_date_string($everyEachDays.' days'));	// substract N days	
			$logDate = $currentDate->format('Y-m-d');			
			file_put_contents(STORAGE, $logDate); // create file with default content
		}
	}	
	
	class BufferApp {
		private $client_id;
		private $client_secret;
		private $code;
		private $access_token;
		
		private $callback_url;
		private $authorize_url = 'https://bufferapp.com/oauth2/authorize';
		private $access_token_url = 'https://api.bufferapp.com/1/oauth2/token.json';
		private $buffer_url = 'https://api.bufferapp.com/1';
		
		public $ok = false;
			
		private $endpoints = array(
			'/user' => 'get',
			
			'/profiles' => 'get',
			'/profiles/:id/schedules/update' => 'post',	// Array schedules [0][days][]=mon, [0][times][]=12:00
			'/profiles/:id/updates/reorder' => 'post',	// Array order, int offset, bool utc
			'/profiles/:id/updates/pending' => 'get',
			'/profiles/:id/updates/sent' => 'get',
			'/profiles/:id/schedules' => 'get',
			'/profiles/:id' => 'get',
			
			'/updates/:id/update' => 'post',						// String text, Bool now, Array media ['link'], ['description'], ['picture'], Bool utc
			'/updates/create' => 'post',								// String text, Array profile_ids, Aool shorten, Bool now, Array media ['link'], ['description'], ['picture']
			'/updates/:id/destroy' => 'post',
			'/updates/:id' => 'get',
			
			'/links/shares' => 'get',
		);
		
		public $errors = array(
			'invalid-endpoint' => 'The endpoint you supplied does not appear to be valid.',
			'400' => 'Error code undefined. Check API documentation.',
			'403' => 'Permission denied.',
			'404' => 'Endpoint not found.',
			'405' => 'Method not allowed.',
			'1000' => 'An unknown error occurred.',
			'1001' => 'Access token required.',
			'1002' => 'Not within application scope.',
			'1003' => 'Parameter not recognized.',
			'1004' => 'Required parameter missing.',
			'1005' => 'Unsupported response format.',
			'1010' => 'Profile could not be found.',
			'1011' => 'No authorization to access profile.',
			'1012' => 'Profile did not save successfully.',
			'1013' => 'Profile schedule limit reached.',
			'1014' => 'Profile limit for user has been reached.',
			'1020' => 'Update could not be found.',
			'1021' => 'No authorization to access update.',
			'1022' => 'Update did not save successfully.',
			'1023' => 'Update limit for profile has been reached.',
			'1024' => 'Update limit for team profile has been reached.',
			'1028' => 'Update soft limit for profile reached.',
			'1030' => 'Media filetype not supported.',
			'1031' => 'Media filesize out of acceptable range.',
		);
		
		public $responses = array(
			'403' => 'Permission denied.',
			'404' => 'Endpoint not found.',
			'405' => 'Method not allowed.',
			'500' => 'An unknown error occurred.',
			'403' => 'Access token required.',
			'403' => 'Not within application scope.',
			'400' => 'Parameter not recognized.',
			'400' => 'Required parameter missing.',
			'406' => 'Unsupported response format.',
			'404' => 'Profile could not be found.',
			'403' => 'No authorization to access profile.',
			'400' => 'Profile did not save successfully.',
			'403' => 'Profile schedule limit reached.',
			'403' => 'Profile limit for user has been reached.',
			'404' => 'Update could not be found.',
			'403' => 'No authorization to access update.',
			'400' => 'Update did not save successfully.',
			'403' => 'Update limit for profile has been reached.',
			'403' => 'Update limit for team profile has been reached.',
			'403' => 'Update soft limit for profile reached.',
			'400' => 'Media filetype not supported.',
			'400' => 'Media filesize out of acceptable range.',
		);
		
		function __construct($client_id = '', $client_secret = '', $callback_url = '', $access_token = '') {
			if ($client_id) $this->set_client_id($client_id);
			if ($client_secret) $this->set_client_secret($client_secret);
			if ($callback_url) $this->set_callback_url($callback_url);
			if ($access_token) $this->create_access_token_url($access_token);
			
			if (isset($_GET['code'])) {
				$this->code = $_GET['code'];
				$this->create_access_token_url($access_token);
			}
			
			$this->retrieve_access_token();
		}
		
		function go($endpoint = '', $data = '') {
			if (in_array($endpoint, array_keys($this->endpoints))) {
				$done_endpoint = $endpoint;
			} else {
				$ok = false;
				
				foreach (array_keys($this->endpoints) as $done_endpoint) {
					if (preg_match('/' . preg_replace('/(\:\w+)/i', '(\w+)', str_replace('/', '\/', $done_endpoint)) . '/i', $endpoint, $match)) {
						$ok = true;
						break;
					}
				}
				
				if (!$ok) return $this->error('invalid-endpoint');
			}
			
			if (!$data || !is_array($data)) $data = array();
			$data['access_token'] = $this->access_token;
			
			$method = $this->endpoints[$done_endpoint]; //get() or post()
			return $this->$method($this->buffer_url . $endpoint . '.json', $data);
		}
		
		function store_access_token() {
			$_SESSION['oauth']['buffer']['access_token'] = $this->access_token;
		}
		
		function retrieve_access_token() {
			$this->access_token = $_SESSION['oauth']['buffer']['access_token'];
			
			if ($this->access_token) {
				$this->ok = true;
			}
		}
		
		function error($error) {
			return (object) array('error' => $this->errors[$error]);
		}
		
		function create_access_token_url($access_token = '') {
			/*
			$data = array(
				'code' => $this->code,
				'grant_type' => 'authorization_code',
				'client_id' => $this->client_id,
				'client_secret' => $this->client_secret,
				'redirect_uri' => $this->callback_url,
			);
			
			$obj = $this->post($this->access_token_url, $data);
			$this->access_token = $obj->access_token;
			*/
			$this->access_token = $access_token;
			$this->store_access_token();
		}
		
		function req($url = '', $data = '', $post = true) {
			$utils = new Utils();
			if (!$url) return false;
			if (!$data || !is_array($data)) $data = array();
						
			$options = array(CURLOPT_RETURNTRANSFER => true, CURLOPT_HEADER => false);
			
			if ($post) {
				$options += array(
					CURLOPT_POST => $post,
					CURLOPT_POSTFIELDS => $data
				);
			} else {
				$url .= '?' . http_build_query($data);
			}			
			
			$ch = curl_init($url);
			curl_setopt_array($ch, $options);
			$rs = curl_exec($ch);			
			$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);			
			
			//retry logic			
			$retry = 0;
			while($code != 200 && $retry < 3) {
				// close prev curl
				curl_close($ch);
				// append characters to make it a different post
				if(isset($options[CURLOPT_POSTFIELDS]["text"])) {
					$postText = $options[CURLOPT_POSTFIELDS]["text"];
					if($retry == 0){
						if($utils->endsWith($postText, '.')){
							// removes last '.'
							$postText = rtrim($postText, '.');
						}
						else{
							// append '.'
							$postText .= '.';
						}						
					}
					elseif($retry == 1){
						if($utils->endsWith($postText, '.')){
							// removes last '.' and append ' .'
							$postText = rtrim($postText, '.');							
						}
						$postText .= ' .';
					}
					elseif($retry == 2){
						if($utils->endsWith($postText, '.')){
							// removes last '.' and append '  .'
							$postText = rtrim($postText, '.');							
						}
						$postText .= '  .';
					}
					$options[CURLOPT_POSTFIELDS]["text"] = $postText;
				}				
				
				$ch = curl_init($url);
				curl_setopt_array($ch, $options);
				$rs = curl_exec($ch);				
				$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
				$retry++;
			}			
			
			curl_close($ch);
			
			if($retry > 0){
				echo LB."Retried " . $retry . " times.".LB;
				echo LB."Error code: ".$code.LB;
				echo "ch: ".$ch.LB;
				echo "url: ".$url.LB;
				echo "post: ".$post.LB;
				echo "rs: ".$rs.LB;
				//echo "CURLINFO_HTTP_CODE: ".CURLINFO_HTTP_CODE;
				echo "Data: ".LB;
				foreach($data as $d){
					echo $d.LB;
				}
			}	

			if ($code >= 400) {				
				return $this->error($code);
			}
			
			return json_decode($rs);
		}
		
		function get($url = '', $data = '') {
			return $this->req($url, $data, false);
		}
		
		function post($url = '', $data = '') {
			return $this->req($url, $data, true);
		}
		
		function get_login_url() {
			return $this->authorize_url . '?'
    		. 'client_id=' . $this->client_id
    		. '&redirect_uri=' . urlencode($this->callback_url)
    		. '&response_type=code';
		}
		
		function set_client_id($client_id) {
			$this->client_id = $client_id;
		}
		
		function set_client_secret($client_secret) {
			$this->client_secret = $client_secret;
		}

		function set_callback_url($callback_url) {
			$this->callback_url = $callback_url;
		}
	}
	
?>
