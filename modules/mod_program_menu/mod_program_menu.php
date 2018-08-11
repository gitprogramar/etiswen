<?php
	define( '_JEXEC', 1 );	
	require_once ( JPATH_ROOT.'/api/utils.php' );	
	
	$arraySearch = array();	
	$html = '<nav id="cssmenu"><ul>';	
	$sitemenu = JFactory::getApplication('site')->getMenu();
	$mainmenu = $sitemenu->getItems("menutype", "mainmenu");
		
	foreach($mainmenu as $menu) {		
		if($menu->parent_id == 1 && strtolower($menu->note) != 'oculto'){
			$html .= '<li class="menu-'.$menu->id;
			if($menu->id == $sitemenu->getActive()->id) {
				$html .= ' active';
			}
			$html .= '">';
			if($menu->home == 1){
				$menu->route = '';
				$menu->title = 'Inicio';
			}
			$html .= '<a href="/' . $menu->route . '">' . $menu->title . '</a>';
			$html .= menuRecursiveChilds($mainmenu, $menu->id, $sitemenu->getActive()->id, $arraySearch);
			$html .= '</li>';
		}		
	}
	$html .= '</ul></nav>';
	
	$html = str_replace($arraySearch, "has-sub", $html);
	
	function menuRecursiveChilds($items, $parentId, $activeMenuId, &$arraySearch) {
		$hasChilds = false;
		$html = "";
		foreach($items as $item) {
			if ($item->parent_id == $parentId) {
				if (!$hasChilds) {
					$html .= '<ul class="dropdown">';
					$arraySearch[] = "menu-".$parentId;
					$hasChilds = true;
				}
				$html .= '<li class="menu-'.$item->id;
				if ($item->id == $activeMenuId) {
					$html .= ' active';
				}
				$html .= '">';
				$html .= '<a href="/' . $item->route . '">' . $item->title . '</a>';
				$html .= menuRecursiveChilds($items, $item->id, $activeMenuId, $arraySearch);
				$html .= '</li>';
			}
		}
		if ($hasChilds) {
			$html .= '</ul>';
		}
		return $html;
	}

	require JModuleHelper::getLayoutPath('mod_program_menu');
?>