<?php
	try 
	{
		define( '_JEXEC', 1 );
		define('JPATH_ROOT', realpath(dirname(__FILE__).'/../') );
		define('JPATH_BASE', $_SERVER['REQUEST_URI']);
		require_once ( JPATH_ROOT .'/includes/defines.php');
		require_once ( JPATH_ROOT .'/includes/framework.php');
		require_once ( JPATH_ROOT .'/api/utils.php');		

		
		// Create the Application			
		$app = JFactory::getApplication("site");
		jimport('joomla.plugin.helper');
		
		//var_dump($app);
		
		$credentials = array();
		$credentials['username'] = 'cron';
		$credentials['password'] = '';
		//$credentials['secretkey'] = '';
				
		$result = $app->login($credentials);						
		var_dump($result);
		if(!$result) {
			echo "<br> Access denied.";
		}
		else {
			echo "<br> Access OK.";
		}
		$user = JFactory::getUser();
		echo "<br>UserId: ".$user->id;
		
		$utils = new Utils();
		echo "<br>Current path:".$utils->before('public_html',dirname(__FILE__));
		
		$app->logout();	
	}
	catch(Exception $ex) {		
		echo $ex->getMessage();
	}
	
?>