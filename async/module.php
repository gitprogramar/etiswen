<html><head><meta name="robots" content="noindex, nofollow"></head><body></body></html>
<?php	
	// Load Async Custom Html Module to specified position		
	define( '_JEXEC', 1 );
	define('JPATH_ROOT', realpath(dirname(__FILE__).'/../') );
	require_once ( JPATH_ROOT .'/async/utils.php');
	// include for custom menus
	require_once ( JPATH_ROOT .'/async/menu/menu.php' );
	

	$type = JRequest::getVar('type', '', 'get');
	$position = JRequest::getVar('position', '', 'get');
	$menuid = JRequest::getVar('menuid', '', 'get');

	if(strlen($type) == 0 || strlen($position) == 0 || strlen($menuid) == 0) {		
		echo "not found";
		return;
	}

	/* menu */
	if($type == "menu") {
		$menu = new Menu();
		$mainMenu = json_encode($menu->get());
	}
	elseif($type == "custom-menu"){
		$menu = new Menu();
		$mainMenu = json_encode($menu->getCustom());
	}
	$utils = new Utils();
	$db = JFactory::getDBO();		
	
	// get module content
	$query  = $db->getQuery(true);
	$query->select('module.content, module.params, menu.menuid');
	$query->from('#__modules module INNER JOIN #__modules_menu menu ON module.id = menu.moduleid');
	$query->where("module.position = '" . $position . "'"); 
	$query->where('module.published = 1'); // only published
	$query->where(" (menu.menuid = " . $menuid . " OR menu.menuid = 0 )"); // displays on this menu
	$db->setQuery($query);
	$module = $db->loadRowList();
	
	// return if no modules found
	if(count($module) == 0){
		echo $menuid;
		return;
	}
	
	$content = array();
	foreach ($module as $item):			
		$content["html"] = $utils->between('<html>', '</html>',$item[0]);
		//$content["html"] = count($module);
		$content["css"] = $utils->between('<style>', '</style>',$item[0]);
		/*
		$json = json_decode($item[1]);
		$moduleClass = $json->{'moduleclass_sfx'};	
		*/
		if($type == "menu" || $type == "custom-menu") {
			$arraySearch = array("/*jsonMenu*/", "/*menuId*/");			
			$content["js"] = str_replace($arraySearch, array($mainMenu, $menuid), $utils->between('<script>', '</script>', $item[0]));
		}
		else {				
			$content["js"] = $utils->between('<script>', '</script>', $item[0]);
		}
	endforeach;
	// return html
	echo json_encode($content);
	return;	
?>