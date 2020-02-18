<?php /*********** NO SPACE BEFORE THIS LINE!!!!!!!!!!!!!! *****************/
	/*
	 * Daily cron task. Post article content to social networks: facebook, twitter, google plus.
	 */
	 
	//php -f /home/u510425236/public_html/cron/schedule_social_content.php param user categoryPostId categoryAdvertiseId totalPosts advertiseEvery runsEveryEachDay ordered
	//php -f /home/u510425236/public_html/cron/schedule_social_content.php param admin-es 8 49 2 10 0 1
	$utils;
	class CronModel {
		public function __construct($data) {			
		    // properties from params
			$this->param = (string)$data[1];
			$this->userName = (string)$data[2];			
			$this->categoryPostId = (string)$data[3]; // category_id for posts
			$this->categoryAdvertiseId = (string)$data[4]; // category_id for advertising
			$this->totalPosts = (string)$data[5]; // total posts sent to buffer
			$this->advertiseEvery = (string)$data[6]; // insert advertise after how many posts
			$this->runsEveryEachDay = (string)$data[7]; // task run every these days
			$this->ordered = (string)$data[8]; // ordered or random			
			// storage
			if(substr(php_sapi_name(), 0, 3) == 'cli' || empty($_SERVER['REMOTE_ADDR'])) {
				$this->storage = "/cron/social_content_storage_".$this->categoryPostId.".txt";
			}
			else {			
				$this->storage = "/home/".(string)$data[0]."/public_html/cron/test_storage.txt";
			}			
		}		
	}
	try 
	{
		define( '_JEXEC', 1 );
		define('JPATH_ROOT', realpath(dirname(__FILE__).'/../') );
		require_once ( JPATH_ROOT .'/api/utils.php');			
				
		//$argv = array("u510425236", "", "admin-es", "8", "49", "1", "2", "0", "1");		
		$cronModel = new CronModel($argv);
		
		$utils = new Utils();		
		$utils->cronStart($cronModel->param, $cronModel->storage);				
		
		if(checkTaskMustRun($cronModel->runsEveryEachDay)) {			
			$enterprise = $utils->enterpriseGet($cronModel->userName);	
			dowork($cronModel, $enterprise);
		}
		
		$utils->cronEnd();
	}
	catch(Exception $ex) {
		if(isset($utils))
			$utils->raiseError($ex);
		echo $ex->getMessage();
	}	
	
	// logic to start processing the posts
	function dowork($cronModel, $enterprise)
	{	
		$host = $enterprise->customer->domain;
		
		// access buffer API
		$buffer = new BufferApp($enterprise->customer->bufferId, $enterprise->customer->bufferSecret, $host, $enterprise->customer->bufferToken); //var_dump($buffer);
		$profiles = array();
		if (!$buffer->ok) {
			echo 'Can not connect to Buffer!';
			var_dump($buffer);
			return false;
		} 
		$profiles = $buffer->go('/profiles');				
		if (!is_array($profiles) || count($profiles) == 0) {
			echo "No profiles available. Please checkout in https://buffer.com/app/profile/";
			return false;
		}
		echo 'Successfully connected to Buffer'.LB.LB;
		
		// clean list: delete previous posts		
		echo 'Deleting previous posts....'.LB;		
		foreach ($profiles as $profile) {			
			echo 'Profile '.$profile->service.':';
			$result = $buffer->go('/profiles/'.$profile->id.'/updates/pending');			
			$deleteCounter = 0;			
			foreach($result->updates as $update) {				
				$options = array();
				$options["id"] = $update->id;
				$deleted = $buffer->go('/updates/'.$update->id.'/destroy');
				if(!isset($deleted) || !$deleted) {
					echo LB.'Could not delete post id: '.$update->id;
				}
				else {
					$deleteCounter++;
				}
			}
			echo LB.$deleteCounter.' post deleted.'.LB.LB;
		}	
		
		// get posts
		$articles = getArticles(true, $cronModel);						
		$postCounter = 0;
		foreach($articles as $article): 									
			$storageValues = explode(";", file_get_contents(STORAGE));			
			// get number of post published
			$posted = intval($storageValues[1]);			
			
			// insert advertise every a pre-defined number of posts
			if($cronModel->advertiseEvery <= $posted) {		
				$posted = 0;
				$advertises = getArticles(false, $cronModel);				
				if(count($advertises) > 0) {
					// update advertises
					$postCounter++;		
					$storageValues = explode(";", file_get_contents(STORAGE));								
					file_put_contents(STORAGE, $storageValues[0].';0;'.$storageValues[2].','.$advertises[0][0].';'.$storageValues[3]);
					if(strlen($advertises[0][1]) > 0) {
							// parse advertise
							$articleParsed = parseArticle($advertises[0], $cronModel, $host);					
							//var_dump($articleParsed);
							// send to buffer
							publish($buffer, $profiles, $articleParsed, $host);							
					}		
				}
			}		

			if($postCounter == $cronModel->totalPosts) {
				break;
			}
			
			//update article id		
			$postCounter++;
			$storageValues = explode(";", file_get_contents(STORAGE));			
			file_put_contents(STORAGE, $storageValues[0].';'.($posted+1).';'.$storageValues[2].';'.$storageValues[3].','.$article[0]);
			
			if(strlen($article[1]) == 0) {
				continue;
			}
			
			// parse post
			$articleParsed = parseArticle($article, $cronModel, $host);
			//var_dump($articleParsed);
			// send to buffer
			publish($buffer, $profiles, $articleParsed, $host);
		endforeach;	
	}	
	
	function getArticles($isPost, $cronModel) {
		$db     = JFactory::getDBO();
		jimport( 'joomla.access.access' );
		$posts = array();
		$storageValues = explode(";", file_get_contents(STORAGE));
		
		// define post or advertise category
		$categoryId = $isPost ? $cronModel->categoryPostId : $cronModel->categoryAdvertiseId;
		// get post or advertise article's id
		$ids = $isPost ? $storageValues[3] : $storageValues[2];
		
		// get posts
		$query  = $db->getQuery(true);
		$query->select('id, introtext');
		$query->from('#__content');
		$query->where('catid = '.$categoryId); // social Category ID
		$query->where('state = 1'); // published only
		if($cronModel->ordered == '1') {
			
			$query->where('id NOT IN ('.$ids.')');
			$query->order('ordering DESC');
			$db->setQuery($query, 0, $cronModel->totalPosts);
			$articles = $db->loadRowList();
			if(count($articles) == 0){				
				// reach the end of articles: try to start again
				// clear prev articles ids
				if($isPost) {
					file_put_contents(STORAGE, $storageValues[0].';'.$storageValues[1].';'.$storageValues[2].';0');
				}
				else {
					file_put_contents(STORAGE, $storageValues[0].';'.$storageValues[1].';0;'.$storageValues[3]);
				}
								
				$query  = $db->getQuery(true);
				$query->select('id, introtext');
				$query->from('#__content');		
				$query->where('catid = '.$categoryId); // social Category ID
				$query->where('state = 1'); // published only
				$query->order('ordering DESC');
				$db->setQuery($query, 0, $cronModel->totalPosts);
				$articles = $db->loadRowList();
				// return if no articles found
				if(count($articles) == 0){
					echo 'No articles found for Social category Id = '.$categoryId;
					return;
				}
			}
			return $articles;
		}
		else {
			// TODO: test the random functionality
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
			$articles = array_random_assoc($articles, $quantity);
			return $articles;			
		}		
	}
	
	function parseArticle($article, $cronModel, $host) {		
		// UTF-8 format html article
		$htmlStart = "<!DOCTYPE html><html lang=\"es\"><head><meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\"><meta charset=\"UTF-8\"><meta http-equiv=\"content-type\" content=\"text/html;charset=UTF-8\"></head><body>" . "\r\n";
		$htmlEnd = "</body></html>";		

		// read article html
		$dom = new DOMDocument;
		$dom->loadHTML($htmlStart . $article[1] . $htmlEnd);
		
		$parsed = new ArticleParsedModel();				
		return recursiveChildNode($dom, $parsed, $host);		
	}
	
	class ArticleParsedModel {
		public $postTitle = ""; 
		public $postDesc = "";	
		public $postText = "";
		public $postLink = "";
		public $postImage = "";
	}
	
	// send each post to Buffer profiles (Face, Twitter, Instagram)
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
	function publish($buffer, $profiles, $articleParsed, $host) {		
		foreach ($profiles as $profile) {
			//var_dump($profile);
			
			// instagram config
			if($profile->service == 'instagram'){
				// default instagram image
				if(strlen($articleParsed->postImage) == 0) {
					$articleParsed->postImage = $host . '/images/instagram.jpg';
				}
				// instagram does not accept link
				// so removes link and append to post desc
				if(strlen($articleParsed->postLink) != 0) {
					$articleParsed->postDesc = $articleParsed->postLink . "\r\n" . $articleParsed->postDesc;
					$articleParsed->postLink = '';
				}
			}

			// parse post text
			$postText =  parseTextByProfile($profile, $articleParsed->postTitle, $articleParsed->postDesc); 			
			
			$options = array();
			// image
			if(strlen($articleParsed->postImage) != 0) {
				if(strlen($articleParsed->postLink) == 0 || $profile->service != 'facebook' && $profile->service != 'twitter') {
					$options['media[photo]'] = $articleParsed->postImage;
				}
				$options['media[thumbnail]'] = $articleParsed->postImage;
			}			
			// link
			if(strlen($articleParsed->postLink) != 0) {
				$options['media[link]'] = $articleParsed->postLink;
				if(!array_key_exists('media[photo]', $options)) {
					$options['media[picture]'] = $articleParsed->postImage;
					//$options['media[title]'] = 'Mi titulo';
					//$options['media[description]'] = 'Mi descripción';					
				}				
			}
			// shorten link only for twitter and NOT youtube video link
			if($profile->service != 'twitter' || strpos($articleParsed->postLink, "youtube.com/") !== false) {
				$options['shorten'] = false;
			}
			// text
			if(strlen($postText) != 0) {
				$options['text'] = $postText;
			}
			$options['profile_ids[]'] = $profile->id;

			// send to buffer api
			$buffer->go('/updates/create', $options);
			
			// print restuls
			echo LB.'Posted to '.$profile->service.' profile: ';
			echo LB."Text: ".$postText;
			echo LB."Link: ".$articleParsed->postLink;			
			echo LB."Image: ".$articleParsed->postImage;
			
			echo LB.'--------------'.LB;
		}
	}
	
	// format the post text, every social network has length limitations
	/*
	Twitter: 140
	Pinterest: 500
	Instagram: 2,200
	Facebook Profiles: 5,000
	Facebook Pages: 5,000
	Facebook Groups: 5,000
	LinkedIn Profiles: 700
	LinkedIn Pages: 600
	*/	
	function parseTextByProfile($profile, $postTitle, $postDesc) {		
		$text = "";
		if($profile->service == "twitter") {
			$text = $postTitle . " " . $postDesc;
			correctLenght(130, $text);
		}
		elseif($profile->service == "facebook") {
			$text = $postTitle . "\r\n" . $postDesc;
			correctLenght(4900, $text);
		}
		elseif($profile->service == "instagram") {
			$text = $postTitle . "\r\n" . $postDesc;
			correctLenght(2100, $text);
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
	function recursiveChildNode($element, $parsed, $host) {
		$utils = new Utils();
		foreach($element->childNodes as $child) {
			if(isset($child->tagName)) {
				if($child->tagName == 'h1' || ($child->tagName == 'p' && strlen($parsed->postTitle) == 0)) {
					$parsed->postTitle =  strip_tags($child->textContent);
				}				
				elseif($child->tagName == 'p' && strlen($parsed->postDesc) == 0) {
					if(!$utils->endsWith($parsed->postTitle, ".")) {
						$parsed->postTitle .= "."; // append '.' if not have it
					}
					$parsed->postDesc = strip_tags($child->textContent);
				}
				elseif($child->tagName == 'iframe' && strlen($parsed->postLink) == 0) {
					//echo 'iframe: ';
					//var_dump($child->attributes);
					foreach($child->attributes as $attribute) {
						if($attribute->name == "src"){
							// youtube playlists
							if(strpos($attribute->nodeValue, "youtube.com/") !== false && strpos($attribute->nodeValue, "list=") !== false) {
								$parsed->postLink = "https://www.youtube.com/playlist?list=" . $utils->after_last("=", $attribute->nodeValue);
							} // youtube videos
							elseif(strpos($attribute->nodeValue, "youtu") !== false) {
								$parsed->postLink = $attribute->nodeValue;
							}
						}
					}
				}
				elseif($child->tagName == 'a' && strlen($parsed->postLink) == 0) {
					foreach($child->attributes as $attribute) {
						if($attribute->name == "href"){
							if(strpos($attribute->nodeValue, $host) !== false || strpos($attribute->nodeValue, "//") !== false) {
								$parsed->postLink = $attribute->nodeValue;
							}
							else {
								if($utils->startsWith($attribute->nodeValue, '/')) {
									$parsed->postLink = $host . $attribute->nodeValue;
								}
								else {
									$parsed->postLink = $host . '/' .$attribute->nodeValue;
								}
							}
						}
					}
				}
				elseif($child->tagName == 'img' && strlen($parsed->postImage) == 0) {
					//echo 'img: ';
					//var_dump($child->attributes);
					foreach($child->attributes as $attribute) {
						if($attribute->name == "src"){
							if(strpos($attribute->nodeValue, $host) !== false || strpos($attribute->nodeValue, "//") !== false) {								
								$parsed->postImage = $attribute->nodeValue;
							}
							else {
								if($utils->startsWith($attribute->nodeValue, '/')) {
									$parsed->postImage = $host . $attribute->nodeValue;
								}
								else {
									$parsed->postImage = $host . '/' .$attribute->nodeValue;
								}
							}
							//echo $postImage;
						}
					}
				}				
				$parsed = recursiveChildNode($child, $parsed, $host);				
			}			
		}
		return $parsed;
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
	function checkTaskMustRun($runsEveryEachDay){
		createStorage(STORAGE, $runsEveryEachDay);
		$storageValues = explode(";", file_get_contents(STORAGE));
		
		$currentDate = JFactory::getDate()->format('Y-m-d');		
		$prevDate = new DateTime($storageValues[0]);
		$prevDate = $prevDate->format('Y-m-d');
		
		// day difference
		$utils = new Utils();
		$interval = $utils->dateDifference($prevDate, $currentDate);
		
		// check with the every day parameter
		if($interval >= $runsEveryEachDay) {
			// update storage			
			file_put_contents(STORAGE, $currentDate.';'.$storageValues[1].';'.$storageValues[2].';'.$storageValues[3]);
			return true;
		}
		
		echo LB."This task runs every ".$runsEveryEachDay." day/s".LB;
		echo "Last time it ran: ".$prevDate.LB;
		
		return false;
	}
	
	// create storage file if not already exists
	function createStorage($everyEachDays) {
		if(file_exists(STORAGE) !== true){ // if file not exists
			$currentDate = JFactory::getDate()->format('Y-m-d');
			$newdate = strtotime('-'.$everyEachDays.' hour', strtotime($currentDate));
			file_put_contents(STORAGE, date('Y-m-d', $newdate).';0;0;0'); // create file with default content
		}
	}	
	
	/*
		// OLD functionality commented out
	
		// get postImage from /images/social_logo
		$socialLogos = array();
		
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
		
		// set postImage if not defined
		if(strlen($postImage) == 0){
			if(count($socialLogos) > 0){
				$postImage = array_rand(array_flip($socialLogos), 1);
				//echo "Image: ".$postImage;
			}				
		}
	*/
	
	/*
	// set postlink if not defined
			if(strlen($postLink) == 0){
				// get aticle link
				$query  = $db->getQuery(true);
				$query->select('path');
				$query->from('#__menu');
				$query->where("link like '%id=" . $post[0] . "'");
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
	*/
	
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
