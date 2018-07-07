<?php /*********** NO SPACE BEFORE THIS LINE!!!!!!!!!!!!!! *****************/
	/*
	 * Monthly cron task. Create sitemap.xml for robots.
	 */
	 
	//php -f /home/u750861504/public_html/cron/schedule_sitemap.php param
	
	$utils;
	try 
	{
		define( '_JEXEC', 1 );
		define('JPATH_ROOT', realpath(dirname(__FILE__).'/../') );
		require_once ( JPATH_ROOT .'/async/utils.php');			
		
		$utils = new Utils();
		$utils->cronStart((string)$argv[1], "/sitemap.xml");				
		
		dowork();
		
		$utils->cronEnd();
	}
	catch(Exception $ex) {
		if(isset($utils))
			$utils->raiseError($ex);
		echo $ex->getMessage();
	}
	
	function dowork() {	
		// domain. NOTE: change protocol as needed.
		$utils = new Utils();
		$config = JFactory::getConfig();
		$url = "http://".$utils->after('@', $config->get('mailfrom'));		
		
		// XML
		$xml = '<?xml version="1.0" encoding="UTF-8"?>';
		$xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"  xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9  http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd">';	
		
		/* menu */
		$menu = JFactory::getApplication('site')->getMenu();
		$mainMenu = $menu->getItems("menutype", "mainmenu");
		
		$modified = lastModified();
		
		// build xml
		for($x=0; $x < count($mainMenu); $x++) {
			$xml .= '<url>';
			if(array_values($mainMenu)[$x]->get("home") == 1) {
				$xml .= '<loc>' . $url . '/' . '</loc>';
				$xml .= '<priority>' . '1' . '</priority>';	
			}
			else {
				$xml .= '<loc>' . $url . '/'. array_values($mainMenu)[$x]->get("route") . '</loc>';
				$xml .= '<priority>' . '0.5' . '</priority>';
			}
			//$xml .= '<lastmod>' . $modified . '</lastmod>';
			//$xml .= '<changefreq>monthly</changefreq>';
			$xml .= '</url>';		
		}
		
		$xml .= '</urlset>';
		
		// write sitemap	
		file_put_contents(STORAGE, $xml);
		
		// write robots.txt	
		$txt = "User-agent: *".LB;
		$txt .= "Disallow: /administrator/".LB;
		$txt .= "Disallow: /bin/".LB;
		$txt .= "Disallow: /cache/".LB;
		$txt .= "Disallow: /cli/".LB;
		$txt .= "Disallow: /components/".LB;
		$txt .= "Disallow: /includes/".LB;
		$txt .= "Disallow: /installation/".LB;
		$txt .= "Disallow: /language/".LB;
		$txt .= "Disallow: /layouts/".LB;
		$txt .= "Disallow: /linq/".LB;
		$txt .= "Disallow: /logs/".LB;
		$txt .= "Disallow: /mercadopago/".LB;
		$txt .= "Disallow: /panel/".LB;
		$txt .= "Disallow: /tmp/".LB;
		$txt .= "Sitemap: ".$url."/sitemap.xml";
		file_put_contents(getcwd().'/public_html/robots.txt', $txt);
	}	
	
	function lastModified() {
		//server time
		$info = getdate();
		$date = $info['mday'];
		$month = $info['mon'];
		$year = $info['year'];
		return "$year-$month-$date";	
	}	
?>
