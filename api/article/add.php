<html><head><meta name="robots" content="noindex, nofollow"></head><body></body></html>
<?php	
	// Article CRUD
	define( '_JEXEC', 1 );
	define('JPATH_ROOT', realpath(dirname(__FILE__).'/../') );
	require_once ( JPATH_ROOT .'/api/utils.php');

	// parse json
	$json = json_decode(file_get_contents('php://input'), true);
	
	create($json);
	echo "ok";
	return;

	function generateAlias($alias, $num) {
		$newAlias;
		if($num == 0) {
			$alias = JFilterOutput::stringURLSafe(strtolower($alias));
			$newAlias = $alias;
		}
		else {
			$newAlias = $alias."-".$num;
		}
		$db = JFactory::getDBO();		
		
		// get content
		$query  = $db->getQuery(true);
		$query->select('alias');
		$query->from('#__content');
		$query->where("LOWER(alias) = '".$newAlias."'"); 
		$db->setQuery($query);
		$data = $db->loadRowList();
		// if data do not exists
		if(count($data) == 0){
			return $newAlias;
		}
		$num++;
		return generateAlias($alias, $num);			
	}
	
	function create($data) {
		$db = JFactory::getDBO();		
	
		// columns to insert.
		$columns = array('title', 
						 'alias', 
						 'introtext', 
						 'catid', 
						 'created', 
						 'created_by_alias', 
						 'state', 
						 'access', 
						 'metadata', 
						 'language');
		
		// values to insert.
		$values = array($db->quote($data["name"]), 
						$db->quote(generateAlias($data["name"], 0)), 
						$db->quote($data["introtext"]), 
						$data["catid"], 
						$db->quote(JFactory::getDate()->toSQL()), 
						$db->quote('Super User'),
						1,
						1,
						$db->quote('{"page_title":"","author":"","robots":""}'),
						$db->quote('*')
						);
		
		// insert data
		$query  = $db->getQuery(true);
		$query->insert($db->quoteName('#__content'));
		$query->columns($db->quoteName($columns));
		$query->values(implode(',', $values));
		$db->setQuery($query);
		$db->execute();		
	}
	
	
?>