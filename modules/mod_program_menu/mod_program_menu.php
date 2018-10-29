<?php
	define( '_JEXEC', 1 );		
	require_once ( JPATH_ROOT.'/api/menu/menu.php' );	
	
	$lang = $_SESSION["language"];
	$menu = new Menu();
	$menuModel = $menu->get('mainmenu'.($lang->default != $lang->current ? '-'.$lang->current : ''));
	$arraySearch = array();	
	$html = '<nav id="cssmenu"><ul>';
	$homeId = 0;
	//var_dump($menuModel->items);
	foreach($menuModel->items as $menu) {
		// for language menus
		if($menu->parent_id == $homeId)
			$menu->parent_id = 1;
		
		// get visibility
		$params = $menu->params;
		$json = json_decode($params);	
		// recursive iterate 
		if($menu->parent_id == 1 && $json->{'menu_show'} != 0){			
			$html .= '<li class="menu-'.$menu->id;
			if($menu->id == $menuModel->activeId) {
				$html .= ' active';
			}
			$html .= '">';
			/*if($menu->home == 1)
				$menu->route = '';*/			
			
			$html .= '<a href="/' . $menu->route . '">' . $menu->title . '</a>';			
			if($homeId != 0)
				$html .= menuRecursiveChilds($menuModel->items, $menu->id, $menuModel->activeId, $arraySearch);
			
			if($homeId == 0)
				$homeId = $menu->id;
			$html .= '</li>';
		}		
	}
	$html .= '</ul></nav>';
	
	$html = str_replace($arraySearch, "has-sub", $html);
	
	function menuRecursiveChilds($items, $parentId, $activeMenuId, &$arraySearch) {
		$hasChilds = false;
		$html = "";
		foreach($items as $item) {
			// get visibility
			$params = $item->params;
			$json = json_decode($params);				
			if ($item->parent_id == $parentId && $json->{'menu_show'} != 0) {
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