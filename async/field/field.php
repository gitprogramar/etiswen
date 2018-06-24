<html><head><meta name="robots" content="noindex, nofollow"></head><body></body></html>
<?php
	define( '_JEXEC', 1 );
	define('JPATH_ROOT', realpath(dirname(__FILE__).'/../') );
	require_once ( JPATH_ROOT .'/async/utils.php');
	
	class Field {
		function get($params) {
			$selects = array();
			foreach($params["select"] as $param) {			
				if($param == "id")
					$selects[] = "fields.id as id";
				elseif(strpos($param,'fieldsvalues') !== false)
					$selects[] = $param;
				elseif(strpos($param,'fieldsgroups') !== false)
					$selects[] = $param;
				else
					$selects[] = "fields.".str_replace("-","",$param);
			}
			
			$db = JFactory::getDBO();
			$query  = $db->getQuery(true);
			// select
			$query->select($selects);
			// join
			$query->from("#__fields fields");
			$query->join("INNER", "#__fields_groups fieldsgroups ON fields.group_id = fieldsgroups.id");
			$query->join("INNER", "#__fields_values fieldsvalues ON fields.id = fieldsvalues.field_id");
			
			// where
			$where = "fields.state = 1";		
			if(isset($params["where"])) {
				foreach($params["where"] as $param) {
					/*
					if($param["operand"] == "=")
						$where .= " AND fieldvalue.item_id IN (SELECT item_id FROM #__fields_values WHERE LOWER(value) = LOWER('".$param["value"]."'))";
					elseif($param["operand"] == "like")
						$where .= " AND fieldvalue.item_id IN (SELECT item_id FROM #__fields_values WHERE LOWER(value) LIKE LOWER('%".$param["value"]."%'))";
					else
						$where .= " AND fieldvalue.item_id IN (SELECT item_id FROM #__fields_values WHERE value ".$param["operand"]." ".$param["value"].")";
					*/
				}
			}
			if(isset($params["whereClause"])) {
				$where .= " ".$params["whereClause"];
			}
			$query->where($where);
			// group
			if(!isset($params["group"])) {
				$query->group('fields.id');
			}
			else{
				$query->group($params["group"]);
			}
			// order 
			if(!isset($params["order"])) {
				$query->order('fields.id');			
			}
			elseif($params["order"] == "ordering") {
				$query->order("fields.ordering");	
			}
			else {
				$query->order($params["order"]);	
			}
			// paging
			if(isset($params["paging"])) {
				$query->setLimit($params["paging"]["limit"].','.$params["paging"]["page"]); //  LIMIT,PAGE
			}
			//return ($query->__toString());
			$db->setQuery($query);
			return $db->loadAssocList();
		}	
	}
?>