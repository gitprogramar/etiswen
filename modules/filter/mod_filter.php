<?php
	/*
	// call module with params
	if ($this->countModules('filter')) { 
		$module  = JModuleHelper::getModules("filter")[0];  
		$module->params = array('params' => json_encode($query));
		echo JModuleHelper::renderModule($module);
	}	
	
	// get params inside module
	//echo $params->get("params");
	//echo json_decode($params->get("params"), true);
	*/	
	define( '_JEXEC', 1 );	
	require_once ( JPATH_ROOT.'/api/utils.php' );
	require_once ( JPATH_ROOT.'/api/article/article.php' );
	require_once ( JPATH_ROOT.'/api/menu/menu.php' );
	setlocale(LC_MONETARY, 'es_AR');
	
	// filter i.e. //lacosta.xyz?entre=1000-2000
	$queryStrings = array();
	parse_str($_SERVER['QUERY_STRING'], $queryStrings);
	$routes = explode("/",strtok($_SERVER["REQUEST_URI"],'?'));
	$html = '<style>@media screen and (max-width: 600px) { div[itemprop="articleBody"] {padding-top:5%;} div[itemprop="articleBody"] > div:not(.searchword) {padding-bottom:0% !important;padding-top:0% !important;}} .article-box {    background-color: #fff;     color: #333;     -webkit-border-radius: 4px;     border-radius: 4px;     -webkit-box-shadow: 0 1px 1px 0 rgba(0,0,0,.1), 0 -1px 2px 0 rgba(0,0,0,.1);     box-shadow: 0 1px 1px 0 rgba(0,0,0,.1), 0 -1px 2px 0 rgba(0,0,0,.1);} .article-box:hover {    -webkit-box-shadow: 0 12px 25px 0 rgba(0,0,0,.16), 0 -1px 2px 0 rgba(0,0,0,.1);     box-shadow: 0 12px 25px 0 rgba(0,0,0,.16), 0 -1px 2px 0 rgba(0,0,0,.1);     transition: box-shadow .3s ease-out;} @media screen and (min-width: 1000px) {.searchword{ padding-top:0% !important;padding-bottom: 0% !important;}}.articleTitle {font-size:.7em;} .articleAmbience:before, .articleBedroom:before {    -webkit-transform: translateY(-50%);     transform: translateY(25%);     content: \'\';     display: inline-block;     height: 16px;     width: 1px;     margin: 0px 7px;     background-color: black;     background-color: var(--color-4);} .articlePeople,.articleAmbience,.articleBedroom{font-size:.6em} .articleZone{ text-transform:capitalize;font-size:.8em}</style>';
	$html .= '<div itemprop="articleBody">';
	
	if(isset($queryStrings["articulo"]) && count($queryStrings) == 1){
		/* Single Article */
		if((count($routes) == 2 && strlen(trim($routes[1]))) == 0 
			|| (count($routes) == 1 && strlen(trim($routes[0])) == 0) 
			|| (count($routes) == 0)){
			$html .= processArticle($queryStrings["articulo"]);
		}		
	}
	else {	
		/* Article List */
		$html .= processArticleList($queryStrings, $routes);
	}
	$html .= '</div>';
	
	function processArticle($id) {		
		$query = array("select" => array("id", "title", "foto1", "foto2", "foto3", "foto4", "foto5", "foto6", "foto7", "foto8", "foto9", "foto10", "inmueble", "operacion", "moneda", "precio", "personas", "ambientes", "superficie-cubierta", "dormitorios", "ubicacion")
						,"whereClause" => "AND content.id=".$id
		); 
		
		$article = new Article();
		$result = $article->get(json_decode(json_encode($query),true));	

		if(count($result) == 0) {
			$html .= processEmptyResults();
		}
		else {
			$items = $result[0];	
			$html = '<style>.box{background-color: #fff; border-radius: 4px; box-shadow: 0 1px 1px 0 rgba(0, 0, 0, .1), 0 -1px 2px 0 rgba(0, 0, 0, .1);}.box-pad{padding: 3% 0%;}.single-article-title{font-size: .8em;}.single-article-price{font-size: 1.2em; color: var(--background-1);}.single-article-prop{font-size: .6em;margin-top: 5px;}.single-article-prop svg{color: #b0b2b9;}.single-article-zone{text-transform: capitalize; font-size: .7em}.single-article-zone > svg{color: #b0b2b9;margin-right: 6px;font-size: 1.5em;}.single-article-zone > span{text-decoration: underline;cursor:pointer;}#slider{position: relative;}.slider-btn{border-radius: 4px; display: inline-block; padding: 8px 16px; vertical-align: middle; overflow: hidden; text-decoration: none; text-align: center; cursor: pointer; white-space: nowrap; transition: all .3s ease-out; color: #fff; background-color: #000;}.slider-btn:hover{background-color: #564a4a!important;}.slider-btn-left{position: absolute; top: 50%; left: -1%; transform: translate(0%, -50%); -ms-transform: translate(-0%, -50%)}.slider-btn-right{position: absolute; top: 50%; right: -1%; transform: translate(0%, -50%); -ms-transform: translate(0%, -50%)}.animate-opacity{animation: opac 0.8s}@keyframes opac{from{opacity: 0}to{opacity: 1}}.item-value{font-size:1.2em;} #map-container a {font-size: .8em !important;} .single-article-title-map {font-size:1em;} @media screen and (min-width: 1000px) { .slider-container {height: 83vh;} #slider, .slide {height: inherit !important;} #map-container {height: 300px;} }</style>';
			$html .= '<div class="row-space-around a-start slider-container">';
			$html .= '<div id="slider" class="row-center w-65">';
			for($x=1; $x<=10; $x++) {		
				if(array_key_exists("foto".$x,$items) && strlen(trim($items["foto".$x])) > 0){
					$html .= '<img class="slide animate-opacity box" src="'.$items["foto".$x].'"/>';
				}			
			}
			$html .= '<div class="slider-btn slider-btn-left" onclick="plusDivs(-1)"><i class="fas fa-chevron-left"></i></div><div class="slider-btn slider-btn-right" onclick="plusDivs(1)"><i class="fas fa-chevron-right"></i></div>';
			$html .= '</div>';
			$html .= '<div class="column-space-between column-vertical w-30 column-pad">';
			$html .= '<div class="column-center w-100 box box-pad">';
			$html .= '<p class="single-article-title">'.ucwords(formatNames($items["inmueble"])).' en '.formatNames($items["operacion"]).'</p>';			
			$html .= '<p class="single-article-price">'.formatNames($items["moneda"]).formatNumber($items["precio"]).'</p>';			
			$html .= '<div class="row-space-between w-80 single-article-prop">';
			$html .= '<div class="column-center no-column-pad">';
			$html .= '<i class="fas fa-male fa-2x"></i><p class="no-column-pad">Personas</p>'.'<p class="item-value no-column-pad">'.explode("-",$items["personas"])[0].'</p>';
			$html .= '</div>';
			$html .= '<div class="column-center no-column-pad">';
			$html .= '<i class="far fa-clone fa-2x"></i><p class="no-column-pad">Ambientes</p>'.'<p class="item-value no-column-pad">'.explode("-",$items["ambientes"])[0].'</p>';
			$html .= '</div>';
			$html .= '<div class="column-center no-column-pad">';
			$html .= '<i class="far fa-object-group fa-2x"></i><p class="no-column-pad">Sup. Cubierta</p>'.'<p class="item-value no-column-pad">'.$items["superficiecubierta"].' m<sup>2</sup></p>';
			$html .= '</div>';	
			$html .= '</div>';			
			$html .= '<p class="single-article-zone  no-column-pad row-center w-80"><i class="fas fa-map-marker-alt fa-3x"></i> <a rel="no-follow" class="link no-column-pad">'.formatNames($items["ubicacion"]).'</a></p>';
			$html .= '</div>';
			$contact  = JModuleHelper::getModules("contactmini")[0];
			$html .= '<div class="column-center w-100 box box-pad no-column-pad">'.JModuleHelper::renderModule($contact).'</div>';
			$html .= '</div>';
			$html .= '</div>';			
			$html .= '<script>var slideIndex=1; showDivs(slideIndex); function plusDivs(n){showDivs(slideIndex +=n);}function showDivs(n){var i; var x=document.getElementsByClassName("slide"); if (n > x.length){slideIndex=1}if (n < 1){slideIndex=x.length}for (i=0; i < x.length; i++){x[i].style.display="none";}x[slideIndex-1].style.display="block";}</script>';
			
			// map
			$html .= '<div id ="map-container" class="row-space-around">';
			$html .= '<div class="row-center w-65 box">';
			$html .= '<iframe width="100%" height="300" style="border: 0px solid #000000" src="//www.google.com/maps?q='.str_replace("-","+",$items["ubicacion"]).'&z=15&t=m&output=embed"></iframe>';
			$html .= '</div>';
			$html .= '<div class="column-space-between column-vertical w-30 column-pad box">';
			$html .= '<div class="column-center column-vertical box-pad w-80">';
			$html .= '<p class="single-article-title-map">Ubicación</p>';
			$html .= '<br>';
			$html .= '<p class="single-article-zone no-column-pad row-center w-80"><i class="fas fa-map-marker-alt fa-3x"></i>'.formatNames($items["ubicacion"]).'</p>';
			$html .= '<br>';
			$html .= '<a class="link" target="_blank" href="//www.google.com/maps?q='.str_replace("-","+",$items["ubicacion"]).'&z=15&t=m&output=embed">Ampliar mapa</a>';
			$html .= '</div>';		
			$html .= '</div>';			
			$html .= '</div>';
			
			
			// paging			
			$paging = new stdClass();
			$paging->page = 0;
			$paging->limit = 10;
			// where
			$wheres = array();
			$where = new stdClass();
			$where->operand = "=";
			$where->value = $items["moneda"];
			$wheres[] = $where;
			$where->value = $items["operacion"];
			$wheres[] = $where;
			$where->value = $items["inmueble"];
			$wheres[] = $where;
			$query = array("select" => array("id", "title", "foto1" ,"inmueble", "operacion", "moneda", "precio", "personas", "ambientes", "dormitorios", "ubicacion")
							,"where" => $wheres
							,"order" => "popular"
							,"paging" => $paging
			); 		
			$html .= processEmptyResults($query, false);
		}
		return $html;
	}
	
	function processArticleList($queryStrings, $routes) {
		$order = "popular";
		// where
		$wheres = array();
		foreach($routes as $route) {		
			if(strlen(trim($route)) == 0) {
				continue;
			}
			$where = new stdClass();
			$where->operand = "=";
			$where->value = $route;
			$wheres[] = $where;
		}
		
		foreach($queryStrings as $key => $value){
			$where = new stdClass();
			$where->value = "";
			if($key == "mayor-que"){
				$where->operand = ">";
				$where->value = $value;
			}
			elseif($key == "menor-que") {
				$where->operand = "<";
				$where->value = $value;
			}
			elseif($key == "entre") {
				$where->operand = "BETWEEN";
				$where->value = str_replace("-", " AND ", $value);
			}
			elseif($key == "mayor-precio"){
				$order = "precio DESC";
			}
			elseif($key == "menor-precio") {
				$order = "precio ASC";
			}
			elseif($key == "destacados") {
				$order = "popular";
			}
			else {
				$where->operand = "=";
				$where->value = $key;
			}
			if(strlen($where->value) > 0){		
				$wheres[] = $where;
			}
		}

		// paging for server query
		$paging = new stdClass();
		$paging->page = 0;
		$paging->limit = 4;		
		
		$query = array("select" => array("id", "title", "foto1", "inmueble", "operacion", "moneda", "precio", "personas", "ambientes", "dormitorios", "ubicacion")
						,"where" => $wheres
						,"order" => $order
						,"paging" => $paging
		); 

		// query for async scrolling
		$jsonQuery = array("select" => array("total", "title", "foto1", "inmueble", "operacion", "moneda", "precio", "personas", "ambientes", "dormitorios", "ubicacion")
						,"where" => $wheres
						,"order" => $order
		); 		
		
		// call server query
		$article = new Article();
		$result = $article->get(json_decode(json_encode($query),true));	
				
		if(count($result) == 0) {		
			$html .= processEmptyResults($query);
		}
		else {
			$html .= processResults($result);
		}	
		
		// scrolling and pagination
		//$html .= '<script>program.query = JSON.parse(\''.$jsonQuery.'\'); program.query.action = "get";</script>';
		$html .= '<script>program.query = JSON.parse(\''.json_encode($jsonQuery).'\'); program.query.action = "get"; program.loadMore=function(){ 		 	if ((window.innerHeight + window.pageYOffset) >=document.body.offsetHeight && document.querySelector("#footer-content").children.length == 0) { 		program.paging.defaults.page++; 		program.paging({ 			container: "div[itemprop=\'articleBody\']", 			url: "/async/article/post.php", 			callback: "buildHtml", 			size: 4, 			hide: true, 			data: program.query }); 	} };  function buildHtml(data) { 	program.modalHide("loading-simple"); 	if(data.length == 0) { 		/* show footer when no more items*/ 		program.getAsync("html", "footer-content" + program.templateId, footerContentCallback); 		return; 	} 	     var html = \'\';     for (var x = 0; x < data.length; x++) {         if (x % 4 == 0) {             html += \'</div><div class="row-space-between">\';         }         html += \'<div class="column-center w-20 column-pad article-box">\';         html += \'<img class="articleImage" src="\' + data[x].foto1 + \'"/>\';         html += \'<p class="articleTitle">en \' + formatNames(data[x].operacion) + \'</p>\';         html += \'<p class="articlePrice">\' + formatNames(data[x].moneda) + data[x].precio + \'</p>\';         html += \'<p><span class="articlePeople">\' + formatNames(data[x].personas) + \'</span><span class="articleAmbience">\' + formatNames(data[x].ambientes) + \'</span><span class="articleBedroom">\' + formatNames(data[x].dormitorios) + \'</span></p>\';         html += \'<p class="articleZone">\' + formatNames(data[x].ubicacion) + \'</p>\';         html += \'</div>\';         if (x == data.length - 1) {             var blankItems = (x + 1) % 4;             if (blankItems == 1) {                 html += \'<div class="column-center w-20"></div><div class="column-center w-20"></div><div class="column-center w-20"></div>\';             } else if (blankItems == 2) {                 html += \'<div class="column-center w-20"></div><div class="column-center w-20"></div>\';             } else if (blankItems == 3) {                 html += \'<div class="column-center w-20"></div>\';             }             html += \'</div>\';         }     }     document.querySelector(\'div[itemprop="articleBody"]\').innerHTML += html; }  function footerContentCallback() { 	window.setTimeout(function() { 		document.getElementById("footer-content").classList.toggle("footer-content-load"); 	}, 600); }  function formatNames(name) {     if (name.indexOf(\'ambiente\') != -1) {         name = name.replace("ambientes", "ambs.").replace("ambiente", "ambs.");     } else if (name.indexOf(\'dormitorio\') != -1) {         name = name.replace("dormitorios", "dor.").replace("dormitorio", "dor.");     } else if (name.indexOf(\'pesos\') != -1) {         name = "$ ";     } else if (name.indexOf(\'dolares\') != -1) {         name = "U$D ";     }     if (name.indexOf(\'mas-de\') != -1) {         name = name.replace("mas-de", "+");     }     return name.replace(/-/g, " "); } </script>';
		//$html .= '<script>program.loadMore=function(){ 		 	if ((window.innerHeight + window.pageYOffset) >=document.body.offsetHeight && document.querySelector("#footer-content").children.length == 0) { 		program.paging.defaults.page++; 		program.paging({ 			container: "div[itemprop=\'articleBody\']", 			url: "/async/article/post.php", 			callback: "buildHtml", 			size: 4, 			hide: true, 			data: { 				action: "get", 				select: ["total", "title", "foto1", "inmueble", "operacion", "moneda", "precio", "personas", "ambientes", "dormitorios", "ubicacion"],				 				order: "popular" 			} 		}); 	} };  function buildHtml(data) { 	program.modalHide("loading-simple"); 	if(data.length == 0) { 		/* show footer when no more items*/ 		program.getAsync("html", "footer-content" + program.templateId, footerContentCallback); 		return; 	} 	     var html = \'\';     for (var x = 0; x < data.length; x++) {         if (x % 4 == 0) {             html += \'</div><div class="row-space-between">\';         }         html += \'<div class="column-center w-20 column-pad article-box">\';         html += \'<img class="articleImage" src="\' + data[x].foto1 + \'"/>\';         html += \'<p class="articleTitle">en \' + formatNames(data[x].operacion) + \'</p>\';         html += \'<p class="articlePrice">\' + formatNames(data[x].moneda) + data[x].precio + \'</p>\';         html += \'<p><span class="articlePeople">\' + formatNames(data[x].personas) + \'</span><span class="articleAmbience">\' + formatNames(data[x].ambientes) + \'</span><span class="articleBedroom">\' + formatNames(data[x].dormitorios) + \'</span></p>\';         html += \'<p class="articleZone">\' + formatNames(data[x].ubicacion) + \'</p>\';         html += \'</div>\';         if (x == data.length - 1) {             var blankItems = (x + 1) % 4;             if (blankItems == 1) {                 html += \'<div class="column-center w-20"></div><div class="column-center w-20"></div><div class="column-center w-20"></div>\';             } else if (blankItems == 2) {                 html += \'<div class="column-center w-20"></div><div class="column-center w-20"></div>\';             } else if (blankItems == 3) {                 html += \'<div class="column-center w-20"></div>\';             }             html += \'</div>\';         }     }     document.querySelector(\'div[itemprop="articleBody"]\').innerHTML += html; }  function footerContentCallback() { 	window.setTimeout(function() { 		document.getElementById("footer-content").classList.toggle("footer-content-load"); 	}, 600); }  function formatNames(name) {     if (name.indexOf(\'ambiente\') != -1) {         name = name.replace("ambientes", "ambs.").replace("ambiente", "ambs.");     } else if (name.indexOf(\'dormitorio\') != -1) {         name = name.replace("dormitorios", "dor.").replace("dormitorio", "dor.");     } else if (name.indexOf(\'pesos\') != -1) {         name = "$ ";     } else if (name.indexOf(\'dolares\') != -1) {         name = "U$D ";     }     if (name.indexOf(\'mas-de\') != -1) {         name = name.replace("mas-de", "+");     }     return name.replace(/-/g, " "); } </script>';
		return $html; 
	}
	
	function processResults($result) {		
		$index=0;
		foreach($result as $item) {
			if($index%4 == 0 && $index == 0){
				$html .= '<div class="row-space-between">';
			}
			elseif($index%4 == 0 && $index != 0){
				$html .= '</div><div class="row-space-between">';
			}
			$html .= '<div class="column-center w-20 column-pad article-box">';
			$html .= '<img class="articleImage" src="'.$item["foto1"].'"/>';
			$html .= '<p class="articleTitle">'.ucwords(formatNames($item["inmueble"])).'</p>';
			$html .= '<p class="articleTitle">en '.formatNames($item["operacion"]).'</p>';
			$html .= '<p class="articlePrice">'.formatNames($item["moneda"]).formatNumber($item["precio"]).'</p>';
			$html .= '<p><span class="articlePeople">'.formatNames($item["personas"]).'</span><span class="articleAmbience">'.formatNames($item["ambientes"]).'</span><span class="articleBedroom">'.formatNames($item["dormitorios"]).'</span></p>';
			$html .= '<p class="articleZone">'.formatNames($item["ubicacion"]).'</p>';
			$html .= '</div>';
			if($index == count($result)-1){
				$blankItems = ($index+1)%4;
				if($blankItems==1){
					$html .= '<div class="column-center w-20"></div><div class="column-center w-20"></div><div class="column-center w-20"></div>';
				}
				else if($blankItems==2){
					$html .= '<div class="column-center w-20"></div><div class="column-center w-20"></div>';
				}
				else if($blankItems==3){
					$html .= '<div class="column-center w-20"></div>';
				}
				$html .= '</div>';
			}
			$index++;
		}
		return $html;
	}
	
	function processEmptyResults($query = null, $noResults = true) {
		if(!isset($query)){
			// paging			
			$paging = new stdClass();
			$paging->page = 0;
			$paging->limit = 10;

			$query = array("select" => array("id", "title", "foto1" ,"inmueble", "operacion", "moneda", "precio", "personas", "ambientes", "dormitorios", "ubicacion")
							,"order" => "popular"
							,"paging" => $paging
			); 			
		}
	
		if(isset($query["where"]) && count($query["where"]) >= 1){
			array_pop($query["where"]);			
		}

		$article = new Article();
		$result = $article->get(json_decode(json_encode($query),true));
		if(count($result) == 0) {
			return processEmptyResults($query);
		}
		else {
			$searchWord = "También puede interesarte";
			if(isset($query["where"]) && count($query["where"]) >=1){
				$searchWord = "Resultados similares de ".ucwords(str_replace("-"," ",array_values($query["where"])[0]->value)). " ";
			}
			if($noResults) 
				$html = '<div class="row-center">Tu búsqueda no arrojó resultados <i class="far fa-frown" style="padding:6px;"></i> intentá de nuevo.</div>';
			$html .= '<div class="row-center searchword">'. $searchWord .':</div>';
			return $html.processResults($result);
		}
	}
	
	function formatNames($name) {
		if(strpos($name,'ambiente') !== false) {
			$name = str_replace("ambientes", "ambs.", $name);
			$name = str_replace("ambiente", "ambs.", $name);
		}
		else if(strpos($name,'dormitorio') !== false) {
			$name = str_replace("dormitorios", "dor.", $name);
			$name = str_replace("dormitorio", "dor.", $name);
		}
		else if(strpos($name,'pesos') !== false) {
			$name = "$ ";
		}
		else if(strpos($name,'dolares') !== false) {
			$name = "U\$D ";
		}		
		if(strpos($name,'mas-de') !== false) {
			$name = str_replace("mas-de", "+", $name);
		}
		
		return str_replace("-", " ", $name);
	}
	
	function formatNumber($n, $n_decimals=2) {			
        return ((floor($n) == round($n, $n_decimals)) ? number_format($n , 0, ',', '.') : str_replace("$", "", money_format('%.2n', $n)));
    }
	
	require JModuleHelper::getLayoutPath('mod_filter');
?>