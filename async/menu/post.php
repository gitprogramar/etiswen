<html><head><meta name="robots" content="noindex, nofollow"></head><body></body></html>
<?php	
	// Load Fields Async
	
	/*
	//Usage:
	var json = JSON.stringify({
		action: "createRoutes"
	});
	var xhttp = new XMLHttpRequest();	
	xhttp.onreadystatechange = function() {
		if (this.readyState == 4 && this.status == 200) {
			var response = this.responseText;
			console.log(response);
		}
	};
	xhttp.open("POST", "/async/menu/post.php", true);
	xhttp.setRequestHeader("Content-Type", "application/json");
	xhttp.send(json);
	
	*/
	
	define('_JEXEC', 1);
	define('JPATH_ROOT', realpath(dirname(__FILE__).'/../../') );
	require_once ('menu.php');
	
	// parse json
	$json = json_decode(file_get_contents('php://input'), true);

	if(isset($json) && isset($json["action"])) {
		$response = array();
		$menu = new Menu();
		if($json["action"] == "createRoutes"){			
			$response["value"] = $menu->createRoutes();
		}
		else if($json["action"] == "getCustom"){			
			$response["value"] = $menu->getCustom();
		}
		else if($json["action"] == "get"){
			if($json["type"] != undefined && $json["type"] =! '')
				$response["value"] = $menu->get($json["type"]);
			else
				$response["value"] = $menu->get();
		}		
		echo json_encode($response);
	}
	return;
?>