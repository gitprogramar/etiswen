<?php /*********** NO SPACE BEFORE THIS LINE!!!!!!!!!!!!!! *****************/
	/*
	 * Monthly cron task. Create sitemap.xml for robots.
	 */
	 
	//php -f /home/u510425236/public_html/cron/schedule_sitemap.php param user subdomain(optional)
	
	$utils;
	try 
	{
		define( '_JEXEC', 1 );
		define('JPATH_ROOT', realpath(dirname(__FILE__).'/../') );
		require_once ( JPATH_ROOT .'/api/utils.php');	
		require_once ( JPATH_ROOT .'/api/menu/menu.php');		

		$utils = new Utils();	
		$user = (string)$argv[2]; 
		$subdomain = isset($argv[3]) ? "/".(string)$argv[3] : "";
		$utils->cronStart((string)$argv[1], $subdomain."/sitemap.xml");
		
		dowork($user, $subdomain);
		
		$utils->cronEnd();		
	}
	catch(Exception $ex) {
		if(isset($utils))
			$utils->raiseError($ex);
		echo $ex->getMessage();
	}
	
	function dowork($user, $subdomain) {
		$utils = new Utils();				
		// domain	
		$enterprise = $utils->enterpriseGet($user);	
		$url = $enterprise->customer->domain;

		echo "User: ".$user.LB;
		echo "Domain: ".$url.LB;
		echo "Path: ".STORAGE.LB;
				
		// XML
		$xml = '<?xml version="1.0" encoding="UTF-8"?>';
		$xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"  xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9  http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd">';	
		
		/* menu */
		$menu = new Menu();
		$menuModel = $menu->getAll();
		
		$modified = lastModified();
		
		// build xml
		for($x=0; $x < count($menuModel->items); $x++) {
			$xml .= '<url>';
			if(array_values($menuModel->items)[$x]->get("home") == 1) {
				$xml .= '<loc>' . $url . '/' . '</loc>';
				$xml .= '<priority>' . '1' . '</priority>';	
			}
			else {
				$xml .= '<loc>' . $url . '/'. array_values($menuModel->items)[$x]->get("route") . '</loc>';
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
		$txt .= "Disallow: /api/".LB;
		$txt .= "Sitemap: ".$url."/sitemap.xml";
		
		// write robots.txt
		file_put_contents($utils->before("sitemap",STORAGE)."robots.txt", $txt);
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
