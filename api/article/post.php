<html><head><meta name="robots" content="noindex, nofollow"></head><body></body></html>
<?php	
	// Load Articles Async	
	/*
	Usage:
	document.write('<style>@media screen and (max-width: 600px) { div[itemprop="articleBody"] {padding-top:5%;} div[itemprop="articleBody"] > div {padding-bottom:0% !important;padding-top:0% !important;} } .article-box {    background-color: #fff;     color: #333;     -webkit-border-radius: 4px;     border-radius: 4px;     -webkit-box-shadow: 0 1px 1px 0 rgba(0,0,0,.1), 0 -1px 2px 0 rgba(0,0,0,.1);     box-shadow: 0 1px 1px 0 rgba(0,0,0,.1), 0 -1px 2px 0 rgba(0,0,0,.1);} .article-box:hover {    -webkit-box-shadow: 0 12px 25px 0 rgba(0,0,0,.16), 0 -1px 2px 0 rgba(0,0,0,.1);     box-shadow: 0 12px 25px 0 rgba(0,0,0,.16), 0 -1px 2px 0 rgba(0,0,0,.1);     transition: box-shadow .3s ease-out;} .articleTitle {font-size:.7em;} .articleAmbience:before, .articleBedroom:before {    -webkit-transform: translateY(-50%);     transform: translateY(25%);     content: \'\';     display: inline-block;     height: 16px;     width: 1px;     margin: 0px 7px;     background-color: black;     background-color: var(--color-4);} .articlePeople,.articleAmbience,.articleBedroom{font-size:.6em} .articleZone{ text-transform:capitalize;font-size:.8em}}</style>'); 
	
	var page = 0;
	window.addEventListener("load", function load(event) {
		loadData(page);
	});
	var loadData = function(page) {
		var json = JSON.stringify({
			action: "createFilterRoutes"
			select: ["id", "title", "foto1", "operacion", "moneda", "precio", "personas", "ambientes", "dormitorios", "ubicacion"],
			where: [{"operand": "=","value": "carilo"}, {"operand": "=","value": "alquiler"}],
			//whereClause: "OR 1 = 1",
			order: "popular", // precio DESC
			paging: {"page": page, "limit": 10}
		});
		var xhttp = new XMLHttpRequest();	
		xhttp.onreadystatechange = function() {
			if (this.readyState == 4 && this.status == 200) {
				var response = this.responseText;
				var json = JSON.parse(response);
				var html = '';
				for(var x = 0; x < json.length; x++){				
					if(x%4 == 0){
						html += '</div><div class="row-space-between">';
					}
					html += '<div class="column-center w-20 column-pad article-box">';
					html += '<img class="articleImage" src="'+json[x].foto1+'"/>';
					html += '<p class="articleTitle">en '+formatNames(json[x].operacion)+'</p>';
					html += '<p class="articlePrice">'+formatNames(json[x].moneda)+json[x].precio+'</p>';
					html += '<p><span class="articlePeople">'+formatNames(json[x].personas)+'</span><span class="articleAmbience">'+formatNames(json[x].ambientes)+'</span><span class="articleBedroom">'+formatNames(json[x].dormitorios)+'</span></p>';
					html += '<p class="articleZone">'+formatNames(json[x].ubicacion)+'</p>';
					html += '</div>';
					if(x == json.length-1){					
						var blankItems = (x+1)%4;
						if(blankItems==1){
							html += '<div class="column-center w-20"></div><div class="column-center w-20"></div><div class="column-center w-20"></div>';
						}
						else if(blankItems==2){
							html += '<div class="column-center w-20"></div><div class="column-center w-20"></div>';
						}
						else if(blankItems==3){
							html += '<div class="column-center w-20"></div>';
						}
						html += '</div>';
					}
				}
				document.querySelector('div[itemprop="articleBody"]').innerHTML += html;
				if(response == undefined) {
					return false;
				}
				page++;
			}
		};
		xhttp.open("POST", "/api/article/post.php", true);
		xhttp.setRequestHeader("Content-Type", "application/json");
		xhttp.send(json);
	};

	function formatNames(name) {
		if(name.indexOf('ambiente') != -1) {
			name = name.replace("ambientes", "ambs.").replace("ambiente", "ambs.");
		}
		else if(name.indexOf('dormitorio') != -1) {
			name = name.replace("dormitorios", "dor.").replace("dormitorio", "dor.");
		}
		else if(name.indexOf('pesos') != -1) {
			name = "$ ";
		}
		else if(name.indexOf('dolares') != -1) {
			name = "U$D ";
		}
		if(name.indexOf('mas-de') != -1) {
			name = name.replace("mas-de", "+");
		}
		return name.replace(/-/g, " ");
	}
	*/
	
	define('_JEXEC', 1);
	require_once ('article.php');
	
	// parse json
	$json = json_decode(file_get_contents('php://input'), true);
	
	/*
	if(!isset($json["select"])) {
		echo "select clause missing";
		return;
	}
	*/
	
	if(isset($json) && isset($json["action"])) {
		$item = new Article();
		if($json["action"] == "get"){				
			echo json_encode($item->get($json));
		}
		if($json["action"] == "insert"){				
			echo json_encode($item->insert($json));
		}
	}
	return;
?>