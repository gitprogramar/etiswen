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
			// words to search for
			$filters = explode(' ',$params["filter"]);
			$words = array();
			foreach($filters as $filter) {
				$formatted = preg_replace('/\s+/', '', $filter);
				if(strlen($formatted) == 0)
					continue;
				$words[] = $filter;
			}
			/*Article query*/
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
			$where .= " AND content.introtext NOT LIKE '%loadposition%'";
			$where .= " AND menu.path IS NOT NULL";
			$where .= " AND menu.published = 1"; 			
			$where .= " AND (content.language = '*' OR content.language LIKE '".$this->language->current."-%')";			
			foreach($words as $word) {				
				$where .= " AND (LOWER(introtext) LIKE LOWER('%".$word."%') OR LOWER(content.title) LIKE LOWER('%".$word."%'))";
			}	
			if(isset($params["whereClause"])) {
				$where .= " ".$params["whereClause"];
			}
			
			$query->where($where);
			$query->group('content.id');
			// order 
			/*if(!isset($params["order"])) {
				$query->order('content.created');			
			}
			elseif($params["order"] == "popular") {
				$query->order("frontpage.ordering DESC, content.created");	
			}
			else {
				$query->order($params["order"] . ", content.created");
			}*/
			
			/*Cart query*/
			if(isset($params["cart"]) && $params["cart"]) {
				$selects = array();			
				foreach($params["select"] as $param) {				
					if($param == "id" || $param == "total")
						$selects[] = "cart.id as id";
					else if($param == "title")
						$selects[] = "cart.name as title";
					else if($param == "introtext")
						$selects[] = "cart.description as introtext";
					else if($param == "created")
						$selects[] = "cart.checked_out_time as created";
					else if($param == "route")
						$selects[] = "CONCAT(menu.path,'?filter=',cart.id) as route";
				}	
				$query2  = $db->getQuery(true);
				// select
				$query2->select($selects);
				// join
				$query2->from("#__rokquickcart cart");
				$query2->join("LEFT", "#__menu menu ON LOWER(REPLACE(menu.alias, '-', ' ')) = LOWER(REPLACE(REPLACE(SUBSTRING_INDEX(SUBSTRING_INDEX(cart.params, '{\"', -1), '\":',1), '\/', ' '), '\\\', '')) ");
				// where
				/*$where .= " (content.language = '*' OR content.language LIKE '".$this->language->current."-%')";*/
				$where = "cart.published = 1"; 
				$where .= " AND menu.published = 1"; 
				$where .= " AND menu.path IS NOT NULL";			
				foreach($words as $word) {				
					$where .= " AND (LOWER(cart.name) LIKE LOWER('%".$word."%') OR LOWER(cart.description) LIKE LOWER('%".$word."%'))";
				}
				
				if(isset($params["whereClause"])) {
					$where .= " ".$params["whereClause"];
				}			
				$query2->where($where);
				
				// union
				$query->unionAll($query2);
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
			$model->words = $words;
			return $model;
			
		}
	}
	class ArticleModel {
		public $total = 0;
		public $page = 0;
		public $items = array();
		public $words = array();
	}
	
	/*QUERY*/
	/*
	SELECT SQL_CALC_FOUND_ROWS content.id as id,content.title as title,content.introtext as introtext,menu.path as route
	FROM `prog_content` content
	LEFT JOIN `prog_content_frontpage` frontpage ON content.id = frontpage.content_id
	LEFT JOIN `prog_menu` menu ON content.alias = menu.alias
	WHERE content.catid NOT IN (SELECT id FROM `prog_categories` WHERE LOWER(title) = LOWER('No Search') OR LOWER(title) = LOWER('No Buscar')) AND content.state = 1 AND content.introtext NOT LIKE '%loadposition%' AND menu.path IS NOT NULL AND menu.published = 1 AND (LOWER(introtext) LIKE LOWER('%manual%') OR LOWER(content.title) LIKE LOWER('%manual%')) AND (LOWER(introtext) LIKE LOWER('%joy%') OR LOWER(content.title) LIKE LOWER('%joy%'))
	GROUP BY content.id
	UNION ALL (
	SELECT cart.id as id,cart.name as title,cart.description as introtext,CONCAT(menu.path,'?filter=',cart.id) as route
	FROM `prog_rokquickcart` cart
	LEFT JOIN `prog_menu` menu ON LOWER(REPLACE(menu.alias, '-', ' ')) = LOWER(SUBSTRING_INDEX(SUBSTRING_INDEX(cart.params, '{"', -1), '":',1)) 
	WHERE cart.published = 1 AND menu.published = 1 
	AND menu.path IS NOT NULL 
	AND (LOWER(cart.name) LIKE LOWER('%manual%') OR LOWER(cart.description) LIKE LOWER('%manual%')) 
	AND (LOWER(cart.name) LIKE LOWER('%joy%') OR LOWER(cart.description) LIKE LOWER('%joy%')))

	LIMIT 10, 5
	*/
?>