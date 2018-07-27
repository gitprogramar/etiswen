<?php
	define( '_JEXEC', 1 );	
	require_once ( JPATH_ROOT.'/api/utils.php' );	
	
	
	$utils = new Utils();
	
	// get params inside module	
	//echo json_decode($params->get("params"), true)["position"];
	//return;
	
	// get module content
	$db = JFactory::getDBO();
	$query  = $db->getQuery(true);
	$query->select('module.content');
	$query->from('#__modules module');
	$query->where("module.position = '". json_decode($params->get("params"), true)["position"] ."'"); 
	$query->where('module.published = 1'); // only published	
	$db->setQuery($query);
	$moduleHtml = $db->loadResult();
	
	//echo $moduleHtml;
	//return;
	
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
	$html = $utils->before('<nav id="cssmenu">', $moduleHtml) .  $html . $utils->after('</nav>', $moduleHtml);
	
	if(strpos($html, "<?php") !== false)
	{
		eval('?>'.$html.'<?php;');
	}
	
	
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