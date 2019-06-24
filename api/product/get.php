<html><head><meta name="robots" content="noindex, nofollow"></head><body></body></html>
<?php	
	// Load Fields Async
	
	/*
	//Usage:
	program.get("https://nubsant.com/api/product/get.php?date=2019-02-01", function(response) {
		console.log(response.value);
	});
	
	*/
	
	define('_JEXEC', 1);
	define('JPATH_ROOT', realpath(dirname(__FILE__).'/../../') );
	require_once ('product.php');
	
	// get query string parameters
	$params = $_GET;

	if(count($params) > 0) {
		$response = array();
		$item = new Product();
		if(isset($params["token"])) {
			$response["value"] = $item->isValid($params["token"]);
			echo json_encode($response);
		}
	}
	
	return;
?>