<html><head><meta name="robots" content="noindex, nofollow"></head><body></body></html>
<?php	
	// Load Fields Async
	
	/*
	//Usage:
	var json = JSON.stringify({
		action: "get",
		select: ["id", "title"],
		where: [{"operand": "=","value": "alquiler"}],
		whereClause: "AND LOWER(fieldsgroups.title) = 'filtroroute'",
		order: "popular", 
		paging: {"page": 1, "limit": 10}
	});
	var xhttp = new XMLHttpRequest();	
	xhttp.onreadystatechange = function() {
		if (this.readyState == 4 && this.status == 200) {
			var response = this.responseText;
			console.log(response);
		}
	};
	xhttp.open("POST", "/api/field/post.php", true);
	xhttp.setRequestHeader("Content-Type", "application/json");
	xhttp.send(json);
	*/
	
	define('_JEXEC', 1);
	require_once ('field.php');
	
	// parse json
	$json = json_decode(file_get_contents('php://input'), true);

	if(isset($json) && isset($json["action"])) {
		$field = new Field();
		if($json["action"] == "get"){				
			echo json_encode($field->get($json));
		}
	}
	return;
?>