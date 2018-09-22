<?php
	define( '_JEXEC', 1 );	
	require_once ( JPATH_ROOT.'/api/menu/menu.php' );
		
	$html = "";	
	$firstLetter = "";
	$letter = "";
	$utils = new Utils();
	$langCurrent = $utils->languageGetCurrent($_SESSION["language"]);
	$langDefault = $utils->languageGetDefault();
	$menu = new Menu();
	$menuModel = $menu->get('mainmenu'.($langDefault != $langCurrent ? '-'.$langCurrent : ''));
	$menuSorted = array();
	
	foreach($menuModel->items as $menuItem) {
		$sitemap = new stdClass();
		$sitemap->title = $menuItem->title;
		$sitemap->route = $menuItem->route;
		$menuSorted[] = $sitemap;
	}
	
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