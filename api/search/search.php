<?php
	define( '_JEXEC', 1 );
	define('JPATH_ROOT', realpath(dirname(__FILE__).'/../../') );
	require_once ( JPATH_ROOT .'/api/utils.php');
    new Utils();
    
	class Search {
		protected $language;
		
		public function __construct() {
		    JFactory::getApplication("site");
			$this->language = $_SESSION["language"];
		}
		
		function get($params){
			
			$selects = array();			
			foreach($params["select"] as $param) {
				if($param == "total")
					$selects[] = "SQL_CALC_FOUND_ROWS content.id as id";
				else if($param == "id")
					$selects[] = "content.id as id";
				else if($param == "title")
					$selects[] = "content.title as title";
				else if($param == "introtext")
					$selects[] = "content.introtext as introtext";
				else if($param == "created")
					$selects[] = "content.created as created";
				else if($param == "route")
					$selects[] = "menu.path as route";
			}			
			$db = JFactory::getDBO();
			$query  = $db->getQuery(true);
			// select
			$query->select($selects);
			// join
			$query->from("#__content content");
			$query->join("LEFT", "#__content_frontpage frontpage ON content.id = frontpage.content_id");
			$query->join("LEFT", "#__menu menu ON content.alias = menu.alias");
			// where
			$where = "content.catid NOT IN (SELECT id FROM #__categories WHERE LOWER(title) = LOWER('No Search') OR LOWER(title) = LOWER('No Buscar'))";
			$where .= " AND content.state = 1";
			$where .= " AND content.introtext NOT LIKE '{loadposition%'";
			$where .= " AND menu.path IS NOT NULL";			
			$where .= " AND (content.language = '*' OR content.language LIKE '".$this->language->current."-%')";
			if(isset($params["whereClause"])) {
				$where .= " ".$params["whereClause"];
			}
			
			$query->where($where);
			$query->group('content.id');
			// order 
			if(!isset($params["order"])) {
				$query->order('content.created');			
			}
			elseif($params["order"] == "popular") {
				$query->order("frontpage.ordering DESC, content.created");	
			}
			else {
				$query->order($params["order"] . ", content.created");
			}
			/*
			$response = array();
			$response["value"] = $query->__toString();
			echo json_encode($response);
			return;
			*/
			// paging
			if(isset($params["paging"])) {
				// not working on joomla 3
				//$query->setLimit($params["paging"]["limit"].','.$params["paging"]["limit"]*$params["paging"]["page"]); //  LIMIT,PAGE
				$db->setQuery($query, $params["paging"]["page"]*$params["paging"]["limit"],$params["paging"]["limit"]);
			}
			else {
				$db->setQuery($query);
			}
			
			$items = $db->loadAssocList();
			
			$utils = new Utils();
			/*strip out html*/
			$itemsParsed = array();
			foreach($items as $item) {
				$output = $utils->htmlParse($item['introtext']);				
				$item['introtext'] = $output;
				$itemsParsed[] = $item;
			}
			
			if(!in_array("total", $params["select"])){
				$response = array();
				$response["value"] = $itemsParsed;
				echo json_encode($response);
				return;
			}
			
			// total items
			$db->setQuery("SELECT FOUND_ROWS()");
			$total = $db->loadResult();
			// return model
			$model = new ArticleModel();
			$model->total = $total;
			$model->page = $params["paging"]["page"];
			$model->items = $itemsParsed;
			return $model;
			
		}
	}
	class ArticleModel {
		public $total = 0;
		public $page = 0;
		public $items = array();
	}
	
	/*QUERY*/
	/*
	SELECT content.id as id,content.title as title, menu.path
	FROM `nub_content` content
	LEFT JOIN `nub_content_frontpage` frontpage ON content.id = frontpage.content_id
	LEFT JOIN `nub_menu` menu ON content.alias = menu.alias
	WHERE catid NOT IN (SELECT id FROM `nub_categories` WHERE LOWER(title) = LOWER('No Search') OR LOWER(title) = LOWER('No Buscar')) 
	AND content.state = 1 
	AND (content.language = '*' OR content.language LIKE 'en-%')
	AND menu.path IS NOT NULL
	GROUP BY content.id
	ORDER BY frontpage.ordering DESC, content.created
	*/
?>