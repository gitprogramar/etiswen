/*
Usage: 
1-Navigate into to Custom Search API https://developers.google.com/apis-explorer/#p/customsearch/v1/search.cse.siterestrict.list?q=shakespeare&cx=018157137208427700493%253Axpzc_dex1uq&filter=0&imgSize=large&imgType=photo&lr=lang_es&searchType=image&siteSearch=https%253A%252F%252Ffrasesparaimagenes.com&fields=items(image(height%252Cwidth)%252Clink)%252Cqueries&_h=2&
2-Complete q (comma separated search words) and siteSearch
3-Open console and paste javacsript
4-Run start()
5-Copy the results variable
6-Login frontend site (into corresponding customer page)
7-Navigate into /google-search-engine
8-Inspect and paste values into 'result' div
9-Set default tags and category and hit 'Insert' at bottom of the page.

https://pixabay.com/es/images/search / https://www.flickr.com/search
artificial intelligence,computer,smartphone,software,business,feelings,people,nature,education,cat,dog,food,music,sports,coffe

http://memeschistosos.net
computador,ingeniero,nerd,internet,facebook,whatsapp,animal,gato,perro,graciosa,gracioso,humor,auto,amor,chistoso,fumador,chiste,genius,policia,hermana,papa,madre,lluvia,ratero

https://frasesparaimagenes.com
shakespeare,osho,gandhi,cerati,fridda,Einstein,coelho,romeo santos,walt disney,mandela,lennon

English:
https://www.memecenter.com
music,people,smartphone,software,business,computer,engineer,dog,cat,heart touching,happy,fail,dirty jokes,partner,anime,text message

http://www.fundoes.com
puzzles,boy,girl,friend,love,chatting,general,education,family,kids,politics
*/

function start(qtyPages) {	
	results = '';
	max = 91;
	// limits number of pages returned
	if(qtyPages != undefined)
		max = qtyPages*10-9;
		 
	appendSearch();
}

function appendSearch(qtyPages) {
	// limits number of pages returned
	if(qtyPages != undefined)
		max = qtyPages*10-9;
	
	
	var textBoxes = document.querySelectorAll('.gwt-TextBox');
	var comboBoxes = document.querySelectorAll('.gwt-SuggestBox');

	search = {};						
	search.start = 1; // index page for google search
	search.cx = textBoxes[3].value;
	search.lr = comboBoxes[6].value;
	search.siteSearch = textBoxes[19].value;
	search.orTerms = '';
	search.searchType = comboBoxes[8].value;
	search.imgSize = comboBoxes[4].value;
	search.imgType = comboBoxes[5].value;
	search.siteRestrict = '';
	search.fields = textBoxes[22].value;

	searchWords = textBoxes[0].value.split(',');
	wordIndex = 0;			

	// first search
	search.q = searchWords[0];
	loadData();
}

var loadData = function() {
	var response;
	
	var xhttp=new XMLHttpRequest();
	xhttp.onreadystatechange=function() {
		if (this.readyState==4 && this.status==200) {
		   handleResponse(JSON.parse(this.responseText));
		}
	};
	// parameters
	var params = 'q='+search.q+'&cx='+encodeURIComponent(search.cx)+'&filter=0';
	params += '&googlehost='+(search.lr == 'lang_es' ? 'google.com.ar' : 'google.com');
	if(search.searchType = 'image') {
		params += '&imgSize='+search.imgSize+'&imgType='+search.imgType+'&searchType='+search.searchType;
	}		
	params += '&lr='+search.lr+'&num=10&siteSearch='+encodeURIComponent(search.siteSearch);
	params += '&start='+search.start+'&fields='+encodeURIComponent(search.fields);
	
	xhttp.open("GET", 'https://content.googleapis.com/customsearch/v1/siterestrict?'+params+'&key=AIzaSyCXd3M-Cb0KvyBMKTNS23nfaoiez6l51Go', true);
	xhttp.send();
};

function handleResponse(json) {
	if (json.error != undefined) {
		alert(json.error.message);
		return;
	}
	
	if (json.items == undefined || json.items.length == 0) {
		console.log('Word Searched: ' + search.q + ' no items found.');
		wordIndex++;
		if(searchWords[wordIndex] != undefined) {
			search.q = searchWords[wordIndex];
			loadData();
		}
		else {
			console.log('Process finished!');				
		}
		return;
	}
		
	for (var x = 0; x < json.items.length; x++) {
		var itemName = json.items[x].link.substring(json.items[x].link.lastIndexOf('/') + 1);
		// check duplicates
		if (results.indexOf(itemName) == -1) {
			// check aspect ratio
			if(fitAspectRatio(json.items[x].image.width, json.items[x].image.height, json.items[x].link)) {
				results += '<li><a href="' + json.items[x].link + '" target="_blank">' + itemName + '</a></li>';
			}
		}
	}		
	
	// 91 max index Google API limitation		
	if (json.queries.nextPage != undefined && json.queries.request[0].startIndex < max) {
		search.start += 10;
		loadData();
	}
	else {
		console.log('Word Searched: ' + search.q + ' last index: ' + search.start);
		search.start = 1;
		wordIndex++;
		if(searchWords[wordIndex] != undefined) {
			search.q = searchWords[wordIndex];
			loadData();
		}
		else {
			console.log('Process finished!');				
		}
	}
}

// Instagram limitation:
// discard images that are not within a 4:5 to 1.91:1 aspect ratio 
function fitAspectRatio(width,height,image) {
	var aspectRatio = width/height;		
	if(aspectRatio < 0.8 || aspectRatio > 1.91) {
		console.log(image + ' image ommited. Aspect ratio ('+width+'/'+height+') = ' + aspectRatio + ' should be between 0.8 and 1.91');
		return false;
	}		
	return true;
}	
