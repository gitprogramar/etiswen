<html><head><meta name="robots" content="noindex, nofollow"></head><body>
<?php	
		// test
		define( '_JEXEC', 1 );
		define('JPATH_ROOT', realpath(dirname(__FILE__).'/../') );
		require_once ( JPATH_ROOT .'/async/utils.php');

		/*
		$type = JRequest::getVar('type', '', 'get');
		$position = JRequest::getVar('position', '', 'get');
		$menuid = JRequest::getVar('menuid', '', 'get');
	
		if(strlen($type) == 0 || strlen($position) == 0 || strlen($menuid) == 0) {		
			echo "not found";
			return;
		}
		*/
		/* 
		if($type == "menu") {
			$menu = JFactory::getApplication('site')->getMenu();
			$mainMenu = json_encode($menu->getItems("menutype", "mainmenu"));
		}
		*/
		
		//server time
		$info = getdate();
		$date = $info['mday'];
		$month = $info['mon'];
		$year = $info['year'];
		$hour = $info['hours'];
		$min = $info['minutes'];
		$sec = $info['seconds'];
		echo "$date/$month/$year $hour:$min:$sec";	
?>
</body></html>