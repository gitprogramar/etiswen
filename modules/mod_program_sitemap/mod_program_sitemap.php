<?php
	define( '_JEXEC', 1 );	
	require_once ( JPATH_ROOT.'/async/utils.php' );	
		
	$html = "";	
	$firstLetter = "";
	$letter = "";
	$sitemenu = JFactory::getApplication('site')->getMenu();
	$mainmenu = $sitemenu->getItems("menutype", "mainmenu");
	$menuSorted = array();
	
	foreach($mainmenu as $menuItem) {
		$sitemap = new stdClass();
		$sitemap->title = $menuItem->title;
		$sitemap->route = $menuItem->route;
		$menuSorted[] = $sitemap;
	}
	$utils = new Utils();
	$menuSorted = $utils->sortArray($menuSorted, 'title');
	
	//var_dump($menuSorted);
	//return;
	
	foreach($menuSorted as $menuItem) {
		$firstLetter = strtoupper(substr($menuItem->title,0,1));
		if($letter == "") {
			$letter = $firstLetter;
			$html .= '<h2>' . $letter . '</h2>';
			$html .= '<ul>';
		}
		if($letter != $firstLetter) {
			$letter = $firstLetter;
			$html .= '</ul>';
			$html .= '<h2>' . $letter . '</h2>';
			$html .= '<ul>';
		}
		$html .= '<li><a href="/'.$menuItem->route.'">'.$menuItem->title.'</a></li>';
	}
	$html .= '</ul>';
	
	require JModuleHelper::getLayoutPath('mod_program_sitemap');
?>