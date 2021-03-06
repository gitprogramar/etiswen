<?php
	define( '_JEXEC', 1 );
	define('JPATH_ROOT', realpath(dirname(__FILE__).'/../../') );
	require_once ( JPATH_ROOT .'/api/utils.php');
	new Utils();

	class Article {
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
					$selects[] = "SUBSTR(content.introtext,1,50) as introtext";
				else if($param == "created")
					$selects[] = "content.created as created";
				elseif($param == "precio")
					$selects[] = "0+REPLACE(GROUP_CONCAT(IF(field.name = '".$param."',fieldvalue.value,'')),',','') as ".$param;
				else
					$selects[] = "REPLACE(GROUP_CONCAT(IF(field.name = '".$param."',fieldvalue.value,'')),',','') as ".str_replace("-","",$param);
			}			
			$db = JFactory::getDBO();
			$query  = $db->getQuery(true);
			// select
			$query->select($selects);
			// join
			$query->from("#__content content");
			$query->join("LEFT", "#__content_frontpage frontpage ON content.id = frontpage.content_id");
			$query->join("LEFT", "#__fields_values fieldvalue ON content.id = fieldvalue.item_id");
			$query->join("LEFT", "#__fields field ON field.id = fieldvalue.field_id");
			// where
			$where = "catid IN (SELECT id FROM #__categories WHERE LOWER(title) = LOWER('Filtro'))";
			$where .= " AND content.state = 1";					
			if(isset($params["where"])) {
				foreach($params["where"] as $param) {
					if($param["operand"] == "=")
						$where .= " AND fieldvalue.item_id IN (SELECT item_id FROM #__fields_values WHERE LOWER(value) = LOWER('".$param["value"]."'))";
					elseif($param["operand"] == "like")
						$where .= " AND fieldvalue.item_id IN (SELECT item_id FROM #__fields_values WHERE LOWER(value) LIKE LOWER('%".$param["value"]."%'))";
					else
						$where .= " AND fieldvalue.item_id IN (SELECT item_id FROM #__fields_values WHERE value ".$param["operand"]." ".$param["value"].")";
				}
			}
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
			
			//return ($query->__toString());
			
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
			if(!in_array("total", $params["select"])){				
				$response = array();
				$response["value"] = $items;
				echo json_encode($response);
				return;				
			}
			
			// total items
			$db->setQuery("SELECT FOUND_ROWS()");
			$total = $db->loadResult();
			// return model
			$model = new ArticleModel();
			$model->total = $total;
			$model->items = $items;
			echo json_encode($model);
			return;
			
			/*Query*/
			/*
				SELECT content.id, content.title, SUBSTR(content.introtext,1,50) as introtext, 			 
				 REPLACE(GROUP_CONCAT(IF(field.name = 'precio',fieldvalue.value,'')),',','') as precio,
				 REPLACE(GROUP_CONCAT(IF(field.name = 'ubicacion',fieldvalue.value,'')),',','') as ubicacion,
				 REPLACE(GROUP_CONCAT(IF(field.name = 'operacion',fieldvalue.value,'')),',','') as operacion,
				 CONCAT('{',GROUP_CONCAT(CONCAT('"',field.name,'":"', fieldvalue.value,'"')),'}') as options
				 FROM `prog_content` content LEFT JOIN `prog_content_frontpage` frontpage ON content.id = frontpage.content_id
				LEFT JOIN `prog_fields_values` fieldvalue ON content.id = fieldvalue.item_id
				LEFT JOIN `prog_fields` field ON field.id = fieldvalue.field_id
				WHERE catid IN (SELECT id FROM  `prog_categories` WHERE LOWER(title) = LOWER('Filtro'))
				#AND fieldvalue.item_id IN (SELECT item_id FROM `prog_fields_values` WHERE LOWER(value) = LOWER('Cariló'))
				AND content.state = 1
				GROUP By content.id
				ORDER BY precio DESC, frontpage.ordering DESC, content.created
				LIMIT 0, 20
			*/
		}
		
		function insert($params) {
			$utils = new Utils();
			$db = JFactory::getDBO();		
			$values = array();
			// avoid repeating alias
			$aliasNumber = 0;
			$ordering = 0;
			// handle multiple insert at once
			foreach($params['articles'] as $item) {
				$name = array_values($item)[0];
				$alias = $this->generateAlias(array_values($item)[0], $aliasNumber);	
				if(strpos($alias, '-') !== false) {
					$aliasNumber = $utils->after_last('-',$alias);
					$name = $name.' '.$aliasNumber;
				}
				// handles ordering
				if($ordering == 0) {
					$ordering = (int)$this->ordering(array_values($item)[2]);					
				}
				$ordering++;
				$array = array($db->quote($name), 
								$db->quote($alias), 
								$db->quote(array_values($item)[1]), 
								array_values($item)[2], 
								$db->quote(JFactory::getDate()->toSQL()), 
								$db->quote('Super User'),
								1,
								$ordering,
								1,
								$db->quote('{"page_title":"","author":"","robots":""}'),
								$db->quote('*')
								);
				$values[] = implode(',', $array);
				$aliasNumber++;
			}
			// columns to insert.
			$columns = array('title', 
							 'alias', 
							 'introtext', 
							 'catid', 
							 'created', 
							 'created_by_alias', 
							 'state', 
							 'ordering',
							 'access', 
							 'metadata', 
							 'language');

			$query  = $db->getQuery(true);		
			// insert data
			$query->insert($db->quoteName('#__content'));
			$query->columns($db->quoteName($columns));
			$query->values($values);
			$db->setQuery($query);
			/*
			$response = array();
			$response["value"] = ($query->__toString());
			echo json_encode($response);
			return;	
			*/
			$rows = $db->execute();	
			$response = array();
			$response["value"] = count($params['articles']) . ' rows inserted on data base!';
			echo json_encode($response);
			return;				
		}
		
		function generateAlias($alias, $num) {
			$newAlias;
			$alias = JFilterOutput::stringURLSafe(strtolower($alias));
			if($num == 0) {
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
			return $this->generateAlias($alias, $num);			
		}
		
		function ordering($catId) {
			$db = JFactory::getDBO();
			$query  = $db->getQuery(true);
			$query->select('MAX(ordering)');
			$query->from('#__content');
			$query->where("catid = ".$catId); 
			$db->setQuery($query);
			$data = $db->loadResult();
			return $data;
		}
	}
	class ArticleModel {
		public $total = 0;
		public $items = array();
	}
?>