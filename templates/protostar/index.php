<?php
   /**
   * @version   1.1 $
   * @author Programar http://www.programar.com
   * @copyright Copyright (C) 2018
   */
   
   /* No Direct Access */
   defined( '_JEXEC' ) or die( 'Restricted index access' );
   $doc = JFactory::getDocument();  /*var_dump($doc);*/
   $app = JFactory::getApplication(); /*var_dump($app);*/
   $menu = JFactory::getApplication()->getMenu()->getActive();
   $base = explode('/', $doc->base);
   $host = $base[0]."//".$base[2];
   $this->language  = $doc->language;
  
   $sitename = $app->get('sitename');
   
   // template
	$templateId = "0";
	$url = explode("-", strtok($_SERVER["REQUEST_URI"],'?'));  	
	if(is_numeric($url[count($url)-1])) {
		// get template id from the url	  	
		$templateId = $url[count($url)-1]; 
		$_SESSION["templateId"] = $templateId;
	}
	elseif(isset($_SESSION["templateId"])) { 
		if(strtok($_SERVER["REQUEST_URI"],'?') == "/") {
			$templateId = "0";
			$_SESSION["templateId"] = $templateId;
		}
		else {
			// template id from session	    
			$templateId = $_SESSION["templateId"];
		}
	}
	else {
	  $_SESSION["templateId"] = $templateId;
	}  	
   ?>
<!DOCTYPE html>
<html xml:lang="<?=$this->language; ?>" lang="<?=$this->language; ?>" >
<head>
<title><?=$doc->title?></title>
<meta charset="utf-8">
<meta name="keywords" content="<?=$doc->_metaTags["name"]["keywords"] ?>">
<meta name="description" content="<?=$doc->description?>">
<meta property="og:image" content="<?=$host."/images/logo.png" ?>">
<meta property="og:description" content="<?=$doc->description?>" />
<meta property="og:title" content="<?=$doc->title?>" />
<meta property="og:url" content="<?=$host.$_SERVER["REQUEST_URI"]?>" />
<meta name="robots" content="all">
<meta name="googlebot" content="index,follow,all" />
<meta name="revisit" content="1 day">
<meta name="revisit-after" content="1 month">
<meta http-equiv="expires" content="never">
<meta name="classification" content="<?=$doc->description?>">
<meta name="distribution" content="Global">
<meta name="language" content="Spanish, English">
<meta name="subject" content="html5, javascript, html, json, jquery, php, php5, css">
<meta name="software" content="html5, javascript, programar, html, json, jquery, php, php5, css">
<meta name="country" content="Argentina, Uruguay, España, Chile, Peru, Bolivia, Paraguay, Colombia, EEUU" />
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<base href="<?=$host?>">
<script defer src="/assets/fontawesome-all.min.js"></script>
<?php 
	if($templateId == 0) { 
		$themeColor = "#54a3e2";
	}
	elseif($templateId == 1) { 
		$themeColor = "#4d5299";
	}
	elseif($templateId == 2) { 
		$showOnMobile = 1;
		$themeColor = "#ed1c94";				
	}
	elseif($templateId == 3) { 
		$showOnMobile = 1;
		$themeColor = "#ff654e";
	}
	elseif($templateId == 4) { 
		$themeColor = "#f15a5a";
	}
	elseif($templateId == 5) { 
		$showOnMobile = 1;
		$themeColor = "#fba505";
	}
	elseif($templateId == 6) { 
		$showOnMobile = 0;
		$themeColor = "#4caf50";
	}
	elseif($templateId == 7) { 
		$showOnMobile = 1;
		$themeColor = "#303030";
	}
	elseif($templateId == 8) { 
		$showOnMobile = 1;
		$themeColor = "#00c8c8";
	}
	elseif($templateId == 9) { 
		$showOnMobile = 1;
		$themeColor = "#fba505";
	}
	elseif($templateId == 10) { 
		$showOnMobile = 1;
		$themeColor = "#fba505";
	}
?>
<meta name="theme-color" content="<?= $themeColor?>"/>
<script type="text/javascript"> 
var program = {};
program.menuid = <?=(isset($menu->id) ? $menu->id : 0); ?>;
program.jsonMenu;
program.templateId = <?=(isset($templateId) ? $templateId : 0); ?>;
program.showOnMobile = <?=(isset($showOnMobile) ? $showOnMobile : 0); ?>;
</script>
<!--Common css-->
<style type="text/css">*{margin:0px;padding:0px;}a,a:visited,a:active,a:hover{text-decoration:none;cursor:pointer;}html{overflow-y:scroll;overflow-x:hidden;}body{overflow:hidden;position:relative;font-family:'Font1',sans-serif;font-weight:100;}.effect{transition:all .3s ease-out;}#breadcrumb{display:none;}article{width:100%;}input{font-size:1em;padding:7px 7px 7px 12px;font-family:inherit;}input.input-small{margin-left: 10px;}div.input-prepend{text-align:right;}#login-form{display:flex;flex-direction:column;justify-content:center;align-items:center;}ul.tags,dd.result-category{display:none;}blockquote{border-left:5px solid #d5d5d5;padding:0 0 0 15px;margin:20px 0 15px;}div[itemprop="articleBody"]{min-height:65vh;}div[itemprop="articleBody"] > div,div.search{padding:4% 5% 4% 5%;}div[itemprop="articleBody"] img{max-width:100%;width:100%;height:auto;opacity:.8;display:inherit;}div[itemprop="articleBody"] img:not(.no-border){border:1px solid #dad3d3;}div[itemprop="articleBody"] img.border-rad{border-radius:100px;}div[itemprop="articleBody"] ul li{margin-left:20px;}div.search{font-size:.8em;}dt.result-title{margin-top:2%;}#searchForm{display:flex;flex-direction:column;}.btn-toolbar{display:flex;width:100%;justify-content:space-between;}.btn-toolbar > div{width:25%;}.btn-toolbar div:first-child{width:45%}div.pagination{margin-top:40px;}div.pagination ul{list-style:none;}div.pagination ul li{display:inline;margin-right:10px;}select{padding:5px;color:#666;font-family:inherit;font-size:.8em;border-radius:10px;letter-spacing:1.5px;}.videoContainer{position:relative;padding-bottom:56.25%;padding-top:25px;height:0;width:100%;}.videoContainer iframe{position:absolute;top:0;left:0;width:100%;height:100%;}
/*Large*/@media screen and (min-width:1000px){body{font-size:22px;}.videoContainer{width:80%;}}/*Landscape*/@media screen and (max-width:1200px){body{font-size:20px;}}/*Medium*/@media screen and (max-width:1000px){body{font-size:17px;}#form > div{flex-direction:column;}#form input,#form textarea,#form button{width:80% !important;}#form button{width:100%;}}/*Small*/@media screen and (max-width:600px){body{font-size:15px;}.page-header{padding:8% 4% 0%;}div[itemprop="articleBody"] > div,#form > div,#header-content > div {flex-direction:column;}div[itemprop="articleBody"] > div > div{width:100%;margin:4% 0%;} .responsive { padding-top:10%;padding-bottom:10%;display:flex;flex-direction:column;justify-content:center;align-items:center !important;} .responsive > * { width:90% !important;} .responsive p { text-align:center;} #notify{max-width:50%;}#form input,#form textarea{width:80% !important;}#form button{width:100%;}.change-order-1{order:1;}.change-order-2{order:2;}.change-order-3{order:3;}.change-order-4{order:4;}}
	/*Cross-browser Flexbox Model CSS*/.column-center{display:flex;flex-direction:column;justify-content:center;align-items:center;} .column-vertical { height:inherit;} .column-space-around{display:flex;flex-direction:column;justify-content:space-around;align-items:center;}.column-space-between{display:flex;flex-direction:column;justify-content:space-between;align-items:center;}.row-center{display:flex;justify-content:center;align-items:center;}.row-space-between{display:flex;justify-content:space-between;align-items:center;}.row-space-around{display:flex;justify-content:space-around;align-items:center;}.flex{display:flex;}.j-center{justify-content:center;}.j-start{justify-content:flex-start;}.j-between{justify-content:space-between;}.j-around{justify-content:space-around;}.a-center{align-items:center;}.a-start{align-items:flex-start;}.d-column{flex-direction:column;}.d-row{flex-direction:row;}.o-1{order:1;}.w-10{width:10%;}.w-15{width:15%;}.w-20{width:20%;}.w-23{width:23%;}.w-25{width:25%;}.w-30{width:30%;}.w-40{width:40%;}.w-45{width:45%;}.w-50{width:50%;}.w-55{width:55%;}.w-60{width:60%;}.w-65{width:65%;}.w-70{width:70%;}.w-80{width:80%;}.w-90{width:90%;}.w-100{width:100%;}.column-pad *:not(a):not(svg),#searchForm > div,#login-form *{margin-bottom:10px;}.no-column-pad{margin-bottom:0px !important;}
</style>
<!--Common js-->
<script type="text/javascript">
program.json = function(val) {	
    return val.indexOf("{")!=-1 ? JSON.parse(val.substring(val.indexOf("{"))) : val;
};
program.getAsync = function(module, position, callback) {
    var xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) {
            var json = program.json(this.responseText);
            var pos = document.getElementById(isNaN(position.substr(position.length - 2)) ? position.substr(0, position.length - 1) : position.substr(0, position.length - 2)); /*removes last character (templateId)*/
            if (json.css != undefined) pos.innerHTML = '<style>' + json.css + '</style>';
            if (json.html != undefined) pos.innerHTML += json.html;
            eval(json.js);
            if (callback != undefined) {
                callback();
            }
        }
    };
    xhttp.open("GET", "/async/module.php?type=" + module + "&position=" + position + "&menuid=" + program.menuid, true);
    xhttp.send();
};
program.addCss = function(styles) {
    var head = document.head,
        link, exists = false;
    for (var x = 0; x < styles.length; x++) {
        for (var y = 0; y < document.styleSheets.length; y++) {
            if (document.styleSheets[y].href != null && document.styleSheets[y].href.indexOf(styles[x].substr(styles[x].lastIndexOf('/') + 1)) != -1) {
                exists = true;
                break;
            }
        }
        if (exists) {
            break;
        }
        link = document.createElement('link');
        link.type = 'text/css';
        link.rel = 'stylesheet';
        link.href = styles[x];
        head.appendChild(link);
    }
};
program.addScripts = function(scripts) {
    var head = document.head,
        script, exists = false;
    for (var x = 0; x < scripts.length; x++) {
        for (var y = 0; y < document.scripts.length; y++) {
            if (document.scripts[y].src.indexOf(scripts[x].substr(scripts[x].lastIndexOf('/') + 1)) != -1) {
                exists = true;
                break;
            }
        }
        if (exists) {
            break;
        }
        script = document.createElement('script');
        script.type = 'text/javascript';
        script.src = scripts[x];
        head.appendChild(script);
    }
};
program.getParameter = function(name, url) {
    if (!url) url = window.location.href;
    name = name.replace(/[\[\]]/g, "\\$&");
    var regex = new RegExp("[?&]" + name + "(=([^&#]*)|&|#|$)"),
        results = regex.exec(url);
    if (!results) return null;
    if (!results[2]) return '';
    return decodeURIComponent(results[2].replace(/\+/g, " "));
};
program.scroll = function(element) {
	document.querySelector(element).scrollIntoView({ 
	  behavior: 'smooth' 
	});
};
window.onload = function() {
    program.getAsync("menu", "header-top" + program.templateId, menuLoaded);

    function menuLoaded() {
        window.setTimeout(function() {
            document.getElementById("header-top").classList.toggle("header-top-load");
        }, 600);
        if (window.innerWidth >= 900 || program.showOnMobile) {
            program.getAsync("html", "header-content" + program.templateId, headerContentCallback);
        } else { /*window.setTimeout(function(){program.getAsync("html","header-content",headerContentCallback);},5000);*/ }
        if (document.getElementById("showChildPages") != null) {
            bindChildPage();
        }
        if (document.getElementById("showSiteMap") != null) {
            bindSiteMap();
        }
        program.getAsync("html", "footer-top" + program.templateId, footerTopCallback);
    }

    function headerContentCallback() {
        window.setTimeout(function() {
            document.getElementById("header-content").classList.toggle("header-content-load");
        }, 300);
    }

    function footerTopCallback() {
        window.setTimeout(function() {
            var footerTop = document.getElementById("footer-top");
            if (footerTop != undefined) {
                document.getElementById("footer-top").classList.toggle("footer-top-load");
            }
        }, 500);
        program.getAsync("html", "footer-content" + program.templateId, footerContentCallback);
    }

    function footerContentCallback() {
        window.setTimeout(function() {
            document.getElementById("footer-content").classList.toggle("footer-content-load");
        }, 600);
    } /*var styles=[];styles.push("/assets/font-awesome.min.css");window.setTimeout(program.addCss,5000,styles);*/
}; /*window.onscroll=function(ev){if ((window.innerHeight + window.pageYOffset) >=document.body.offsetHeight){console.log("bottom");}};*/
function bindChildPage() {
    var html = '<ul style="padding-top:10px;">';
    for (var x = 0; x < program.jsonMenu.length; x++) {
        if (program.jsonMenu[x].parent_id == 1 && program.jsonMenu[x].title.toLowerCase() == document.title.toLowerCase()) {
            html += '<li>';
            if (program.jsonMenu[x].home == "1") {
                program.jsonMenu[x].route = "";
            }
            html += '<a href="/' + program.jsonMenu[x].route + '">' + program.jsonMenu[x].title + '</a>';
            html += recursiveBindChilds(program.jsonMenu, program.jsonMenu[x].id);
            html += '</li>';
        }
    }
    document.getElementById("showChildPages").innerHTML = html;
};

function recursiveBindChilds(items, parentId) {
    var hasChilds = false;
    var html = "";
    for (var x = 0; x < items.length; x++) {
        if (items[x].parent_id == parentId) {
            if (!hasChilds) {
                html += '<ul style="padding-top:10px;">';
                hasChilds = true;
            }
            html += '<li>';
            html += '<a href="/' + items[x].route + '">' + items[x].title + '</a>';
            html += recursiveBindChilds(items, items[x].id);
            html += '</li>';
        }
    }
    if (hasChilds) {
        html += '</ul>';
    }
    return html;
};
</script>
</head>
<body>
<!--JSON-LD-->
<script type="application/ld+json">{"@context":"http://schema.org","@type":"Organization","url":"<?=$host?>","name":"<?=$sitename?>","logo":"<?=$host?>/images/logo.png","address":{"@type":"PostalAddress","addressCountry":"AR","addressLocality":"Buenos Aires","postalCode":"1038","streetAddress":"Tte. Gral. Juan Domingo Perón & Florida Cdad. Autónoma de Buenos Aires"},"contactPoint":{"@type":"ContactPoint","telephone":"+54-11-5635-1616","contactType":"sales","email":"info@programar.com.ar"},"sameAs" :[ "https://www.facebook.com/FaceProgramar","https://twitter.com/TuitProgramar","https://www.youtube.com/channel/UC_X-LGYDprn2zCNXEX_tNdQ"]}</script>
<header class="column-center"> <?php if ($this->countModules('header-top'.$templateId)) : ?> <div id="header-top"></div><?php endif; ?> <div id="header-content"></div><?php if ($this->countModules('header-bottom'.$templateId)) : ?> <div id="header-bottom"></div><?php endif; ?></header><main class="row-center"> <div id="breadcrumb"> <jdoc:include type="modules" name="breadcrumb"/> </div><?php if ($this->countModules('main-top'.$templateId)) : ?> <div id="main-top"></div><?php endif; ?> <article id="main-content"> <?php if ($this->countModules('filter')){echo '<jdoc:include type="modules" name="filter"/>';}else{echo '<jdoc:include type="component"/>';}?> </article> <?php if ($this->countModules('main-bottom'.$templateId)) : ?> <div id="main-bottom"></div><?php endif; ?></main><footer class="column-center"> <?php if ($this->countModules('footer-top'.$templateId)) : ?> <div id="footer-top"></div><?php endif; ?> <div id="footer-content"></div><?php if ($this->countModules('footer-bottom'.$templateId)) : ?> <div id="footer-bottom"></div><?php endif; ?></footer>
<!--Modules-->
<jdoc:include type="modules" name="common"/>
<jdoc:include type="modules" name="template<?=$templateId?>"/> 
</body>
</html>