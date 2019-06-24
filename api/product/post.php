<html><head><meta name="robots" content="noindex, nofollow"></head><body></body></html>
<?php	
	// Product logic	
	/*
	Usage:
	var json ={
		action: "generateToken",
		date: "2019-02-10"
	};
	program.post("/api/product/post.php", json, function(response) {
		console.log(response.value);
	});
	*/
	
	define('_JEXEC', 1);
	define('JPATH_ROOT', realpath(dirname(__FILE__).'/../../') );
	require_once ('product.php');
	
	// parse json
	$json = json_decode(file_get_contents('php://input'), true);
	
	if(isset($json) && isset($json["action"])) {
		$response = array();
		$item = new Product();
		if($json["action"] == "generateToken"){				
			$response["value"] = $item->generateToken($json);
			echo json_encode($response);
		}	
	}
	return;
?>