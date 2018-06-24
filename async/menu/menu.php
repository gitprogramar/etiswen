<html><head><meta name="robots" content="noindex, nofollow"></head><body></body></html>
<?php
	// Load menu
	define( '_JEXEC', 1 );
	define('JPATH_ROOT', realpath(dirname(__FILE__).'/../') );
	require_once ( JPATH_ROOT .'/async/utils.php');
	// fields
	require_once ( JPATH_ROOT .'/async/field/field.php' );
	
	ini_set('memory_limit', '-1');
	
	class Menu {
		protected $utils;

		public function __construct() {
			$this->utils = new Utils();
		}
		
		function get($type = "mainmenu") {
			$menu = JFactory::getApplication('site')->getMenu();
			return $menu->getItems("menutype", $type);
		}
		
		function getCustom() {
			$db = JFactory::getDBO();
			$query  = $db->getQuery(true);
			// select
			$selects = array();
			$selects[] = "id";
			$selects[] = "title";
			$selects[] = "parent_id";
			$selects[] = "level";
			$selects[] = "home";
			$selects[] = "link";
			$selects[] = "note";
			$selects[] = "SUBSTRING_INDEX(path,'/',-1) path";
			$selects[] = "SUBSTRING(params,POSITION('menu-anchor_css\":\"' IN params)+18,POSITION('\",\"menu_image' IN params)-(POSITION('menu-anchor_css\":\"' IN params)+18)) cssParent";
			$selects[] = "SUBSTRING(params,POSITION('menu-anchor_css\":\"' IN params)+18,POSITION('\",\"menu-anchor_rel' IN params)-(POSITION('menu-anchor_css\":\"' IN params)+18)) css";
			$selects[] = "SUBSTRING(params,POSITION('menu-anchor_title\":\"' IN params)+20,POSITION('\",\"menu-anchor_css' IN params)-(POSITION('menu-anchor_title\":\"' IN params)+20)) filter";
			$query->select($selects);
			// from
			$query->from("#__menu");
			$query->where("menutype = 'mainmenu' AND published = 1 AND note <> 'oculto'");
			$query->order('lft');
			//return ($query->__toString());
			$db->setQuery($query);
			$menus = $db->loadAssocList();					
			return $menus;
			/*
			$menu = JFactory::getApplication('site')->getMenu();
			$mainmenu = $menu->getItems("menutype", "mainmenu");
			$homeId = "";
			foreach($mainmenu as $menuitem) {
				if($menuitem->home == 1) {
					$homeId = $menuitem->id;
				}
			}*/
			
			
			/*
			$homeId = 10;
			$mainmenu = array();			
			// filter
			$mainmenu[] = json_decode('{"id":"10","menutype":"mainmenu","title":"FILTRAR","home":1,"route":"inicio","link": "","parent_id": "1","level":1,"note": "","css":"fa fa-bolt menu-icon"}');
			// popular
			$mainmenu[] = json_decode('{"id":"12","menutype":"mainmenu","title":"DESTACADOS","home":0,"route":"destacados","link": "index.php?option=com_content&view=article&id=6","parent_id": "1","level":1,"note": "","css":"fa fa-star menu-icon"}');
			// more specifications
			$moreSpec = json_decode('{"id":"1000_10","menutype":"fieldmenu","title":"Más filtros","home":0,"route":"destacados","link": "","parent_id": "10","level":2,"note": "","css":""}');
			*/
			
			/*
			$field = new Field();
			$menuFields = $field->get(json_decode('{"select":["id","title","name","type","params","fieldparams","note"],"whereClause":"AND (LOWER(fieldsgroups.title) = \'filterroute\' OR LOWER(fieldsgroups.title) = \'filterquerystring\')","order":"ordering"}', true));			
			*/
			
			/*$fieldParam = json_decode($menuFields[0]["fieldparams"]);
			foreach($fieldParam->options as $option){
				return $option->name;
			}*/
			/*
			$param = json_decode($menuFields[2]["params"]);
			echo ($param->show_on == "2" ? "es dos" : "no es dos");
			return;
			*/
			/*
			
			foreach($menus as $menu) {
				// field options
				$params = json_decode($menu["params"]);				
				$menuModel = new MenudModel();
				$menuModel->id = $menu["id"];
				$menuModel->parent_id = $menu["parent_id"];
				$menuModel->title = $menuField["title"];
				$menuModel->level = 2;
				$menuModel->css = $params->menu-anchor_css;
				if(strpos($menuField["note"],'morespec') !== false) {
					$menuModel->parent_id = "1000_10";
					$menuModel->level = 3;
				}					
				$mainmenu[] = $menuModel;
				
				// css clases
				$css = explode(",", $params->class);
				
				// child filters
				$fieldParam = json_decode($menuField["fieldparams"]);
				$index = 0;
				if($menuField["type"] == "list"){
					foreach($fieldParam->options as $option){						
						$fieldChildModel = new menuModel();
						$fieldChildModel->id = $index."_".$menuModel->id;
						$fieldChildModel->parent_id = $menuModel->id;
						$fieldChildModel->title = $option->name;
						$fieldChildModel->level = 3;
						if(isset($css[$index]))
							$fieldChildModel->css = $css[$index];
						else
							$fieldChildModel->css = "";						
						$mainmenu[] = $fieldChildModel;
						$index++;
					}
				}
			}
			$mainmenu[] = $moreSpec;
			return $mainmenu;
			*/
		}
		function add($params) {
			$db = JFactory::getDBO();		
		
			// columns to insert.
			$columns = array('menutype',
							 'title', 
							 'alias', 
							 'note', 
							 'path',
							 'link',
							 'type',
							 'published',  
							 'level',
							 'component_id',
							 'access',
							 'params',
							 'language');
			
			// values to insert.
			$values = array($db->quote('mainmenu'), 
							$db->quote(ucfirst(str_replace("-", " ", $params["title"]))),
							$db->quote($params["alias"]), 
							$db->quote($params["note"]),
							$db->quote($params["path"]),
							$db->quote($params["link"]),
							$db->quote($params["type"]),
							1, 
							$params["level"],
							22,
							1,
							$db->quote('{}'),							
							$db->quote('*')
							);
			
				/*
				$menu = array();
				$menu["title"] = str_replace("/", " ",$this->utils->after('/',$routes[count($routes)-1]));
				$menu["alias"] = $this->utils->after('/',$routes[count($routes)-1]);
				$menu["note"] = "oculto";
				$menu["path"] = $this->utils->after('/',$routes[count($routes)-1]);
				$menu["link"] =	'index.php?option=com_content&view=article&id=1';
				$menu["type"] =	'filter';
				$menu["level"] = 1;
				$this->add($menu);	
				*/					
					
			// insert data
			$query  = $db->getQuery(true);
			$query->insert($db->quoteName('#__menu'));
			$query->columns($db->quoteName($columns));
			$query->values(implode(',', $values));
			$db->setQuery($query);
			//return ($query->__toString());
			$db->execute();		
		}
		
		function delete($conditions) {
			if(!isset($conditions) || count($conditions) == 0){
				return;
			}
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);			

			$query->delete($db->quoteName('#__menu'));
			$query->where($conditions);

			$db->setQuery($query);

			$result = $db->execute();
			
		}

		function createRoutes() {
			// delete existing filter routes
			/*$db = JFactory::getDbo();
			$conditions = array(
				$db->quoteName('type') . ' = ' . $db->quote('filter')
			);
			$this->delete($conditions);
			*/
			$field = new Field();
			$menuFields = $field->get(json_decode('{"select":["title","type","fieldparams","id"],"whereClause":"AND LOWER(fieldsgroups.title) = \'route\'","order":"ordering"}', true));						
			$allFields = array();
			foreach($menuFields as $menuField) {					
				if($menuField["type"] == "list"){
					// child filters
					$childs = array();
					$fieldParam = json_decode($menuField["fieldparams"]);	
					foreach($fieldParam->options as $option){											
						$childs[] = "/".$option->value . "#". $menuField["id"] . "#";
					}
					$allFields[] = $childs;
				}
			}
			
			// save json
			$json = json_encode($allFields, JSON_UNESCAPED_UNICODE);
			$json = str_replace('"', '""', $json);
			$json = str_replace('\\', '', $json);
			file_put_contents(getcwd().'/menus_query.txt', $json);
			
			//return $this->mix($allFields);
			return "Ok!. Data copied into menus_query.txt";
		}
	}
	class MenuModel {
		public $id = 0;
		public $menutype = "custom";
		public $title = "";
		public $route = "";	
		public $parent_id = 0;
		public $level = 1;
		public $note = "";
	}
?>