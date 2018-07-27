<html><head><meta name="robots" content="noindex, nofollow"></head><body></body></html>
<?php	
	// Load Fields Async
	
	/*
	//Usage:
	program.post("/api/post.php", {action: "themeGet", themeId:1}, function(response) {
		console.log(response);
	});
	program.post("/api/post.php", {action: "sessionClear"}, function(response) {
		console.log(response);
	});
	*/
	
	define('_JEXEC', 1);
	require_once ('utils.php');
	
	// parse json
	$json = json_decode(file_get_contents('php://input'), true);

	if(isset($json) && isset($json["action"])) {
		$utils = new Utils();
		if($json["action"] == "themeGet" && isset($json["themeId"])){				
			echo json_encode($utils->themeGet($json["themeId"]));
		}
		if($json["action"] == "sessionClear"){				
			echo json_encode($utils->sessionClear());
		}
	}
	return;
?>