<?php
   /**
   * @version   2.0 $
   * @author Programar
   * @copyright Copyright (C) 2018
   */
   
   /* No Direct Access */
   defined('_JEXEC') or die('Restricted index access');
   require_once (JPATH_ROOT.'/async/utils.php');
   
   $doc = JFactory::getDocument();  /*var_dump($doc);*/
   $this->language  = $doc->language;
   /*$app = JFactory::getApplication(); var_dump($app);*/

   $utils = new Utils();
   $customer = $utils->customerGet($doc->params["sitetitle"]);
   
   // template
	/*$templateId = "0";
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
	} */ 	
?>
<!DOCTYPE html>
<html xml:lang="<?=$this->language; ?>" lang="<?=$this->language; ?>" >
<head>
<title><?=$doc->title?></title>
<meta charset="utf-8">
<meta name="keywords" content="<?=$doc->_metaTags["name"]["keywords"] ?>">
<meta name="description" content="<?=$doc->description?>">
<meta property="og:image" content="<?=$customer->domain."/images/logo.png" ?>">
<meta property="og:description" content="<?=$doc->description?>" />
<meta property="og:title" content="<?=$doc->title?>" />
<meta property="og:url" content="<?=$doc->base?>" />
<meta name="robots" content="all">
<meta name="googlebot" content="index,follow,all" />
<meta name="revisit" content="1 day">
<meta name="revisit-after" content="1 month">
<meta http-equiv="expires" content="never">
<meta name="classification" content="<?=$doc->description?>">
<meta name="distribution" content="Global">
<meta name="language" content="Spanish, English">
<meta name="country" content="Argentina, Uruguay, España, Chile, Peru, Bolivia, Paraguay, Colombia, EEUU" />
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<base href="<?=$customer->domain?>">
<meta name="theme-color" content="<?=$customer->color1?>"/>
<!--Common-->
<style type="text/css">
*{margin: 0px; padding: 0px;}a,a:visited,a:active,a:hover{text-decoration: none; cursor: pointer;}html{overflow-y: scroll; overflow-x: hidden;}body{overflow: hidden; position: relative; font-family: 'Font1', sans-serif; font-weight: 100;}.effect{transition: all .3s ease-out;}#breadcrumb{display: none;}article{width: 100%;}input{font-size: 1em; padding: 7px 7px 7px 12px; font-family: inherit;}input.input-small{margin-left: 10px;}div.input-prepend{text-align: right;}#login-form{display: flex; flex-direction: column; justify-content: center; align-items: center;}fieldset{border: none;display: flex; flex-direction: column; justify-content: center; align-items: center;}ul.tags,dd.result-category{display: none;}blockquote{border-left: 5px solid var(--color-1); padding: 0 0 0 15px; margin: 20px 0 15px;}div[itemprop="articleBody"]{min-height: 65vh;}div[itemprop="articleBody"] > div,div.search,div.cart,div.remind,div.reset{padding: 4% 5% 4% 5%;}div[itemprop="articleBody"] img{max-width: 100%; width: 100%; height: auto; opacity: .8; display: inherit;}div[itemprop="articleBody"] img:not(.no-border){border: 1px solid #dad3d3;}div[itemprop="articleBody"] img.border-rad{border-radius: 100px;}div[itemprop="articleBody"] ul li{margin-left: 20px;}div.search{font-size: .8em;}dt.result-title{margin-top: 2%;}#searchForm{display: flex; flex-direction: column;}.btn-toolbar{display: flex; width: 100%; justify-content: space-between;}.btn-toolbar > div{width: 25%;}.btn-toolbar div:first-child{width: 45%}div.pagination{margin-top: 40px;}div.pagination ul{list-style: none;}div.pagination ul li{display: inline; margin-right: 10px;}select{padding: 5px; color: #666; font-family: inherit; font-size: .8em; border-radius: 10px; letter-spacing: 1.5px;}.videoContainer{position: relative; padding-bottom: 56.25%; padding-top: 25px; height: 0; width: 100%;}.videoContainer iframe{position: absolute; top: 0; left: 0; width: 100%; height: 100%;}/*Large*/@media screen and (min-width:1000px){body{font-size: 22px;}.videoContainer{width: 80%;}}/*Landscape*/@media screen and (max-width:1200px){body{font-size: 20px;}}/*Medium*/@media screen and (max-width:1000px){body{font-size: 17px;}#form > div{flex-direction: column;}#form input, #form textarea, #form button{width: 80% !important;}#form button{width: 100%;}}/*Small*/@media screen and (max-width:600px){body{font-size: 15px;}.page-header{padding: 8% 4% 0%;}div[itemprop="articleBody"] > div, #form > div, #header-content > div, div.cart{flex-direction: column;}div[itemprop="articleBody"] > div > div{width: 100%; margin: 4% 0%;}.responsive{padding-top: 10%; padding-bottom: 10%; display: flex; flex-direction: column; justify-content: center; align-items: center !important;}.responsive > *{width: 90% !important;}.responsive p{text-align: center;}#notify{max-width: 50%;}#form input, #form textarea{width: 80% !important;}#form button{width: 100%;}.change-order-1{order: 1;}.change-order-2{order: 2;}.change-order-3{order: 3;}.change-order-4{order: 4;}}/*Cross-browser Flexbox Model CSS*/.column-center{display: flex; flex-direction: column; justify-content: center; align-items: center;}.column-vertical{height: inherit;}.column-space-around{display: flex; flex-direction: column; justify-content: space-around; align-items: center;}.column-space-between{display: flex; flex-direction: column; justify-content: space-between; align-items: center;}.row-center{display: flex; justify-content: center; align-items: center;}.row-space-between{display: flex; justify-content: space-between; align-items: center;}.row-space-around{display: flex; justify-content: space-around; align-items: center;}.flex{display: flex;}.j-center{justify-content: center;}.j-start{justify-content: flex-start;}.j-end{justify-content: flex-end;}.j-between{justify-content: space-between;}.j-around{justify-content: space-around;}.a-center{align-items: center;}.a-start{align-items: flex-start;}.d-column{flex-direction: column;}.d-row{flex-direction: row;}.o-1{order: 1;}.w-10{width: 10%;}.w-15{width: 15%;}.w-20{width: 20%;}.w-23{width: 23%;}.w-25{width: 25%;}.w-30{width: 30%;}.w-40{width: 40%;}.w-45{width: 45%;}.w-50{width: 50%;}.w-55{width: 55%;}.w-60{width: 60%;}.w-65{width: 65%;}.w-70{width: 70%;}.w-80{width: 80%;}.w-90{width: 90%;}.w-100{width: 100%;}.column-pad *:not(a):not(svg),#searchForm > div,#login-form *,fieldset *{margin-bottom: 10px;}.no-column-pad{margin-bottom: 0px !important;}.t-center{text-align:center;}.t-left{text-align:left;}.t-right{text-align:right;}
:root{--primary-color: <?=$customer->primarycolor?>; --secondary-color: <?=$customer->secondarycolor?>; --color-1: <?=$customer->color1?>; --color-2: <?=$customer->color2?>; --color-3: <?=$customer->color3?>; --color-4: <?=$customer->color4?>; --background-1: <?=$customer->background1?>; --background-2: <?=$customer->background2?>; --background-3: <?=$customer->background3?>;}body{color: #fff; color: var(--primary-color);}a,a:visited,a:active,i{color: #4d5299; color: var(--color-2); transition: all .3s ease-out;}a:hover{color: #3a4ed5; color: var(--color-3);}p{color: inherit;}#main-content{background: var(--background-2); color: #29293a; color: var(--secondary-color);}.page-header{color: inherit; font-size: 1.2em; text-align: center; padding: 4% 4% 0%; text-transform: uppercase; position: relative;}h1{padding-bottom: 9px;}h1:before{content: ""; position: absolute; left: calc(50% - 35px); bottom: 0; height: 1px; width: 70px; border-bottom: 3px solid var(--color-1);}h1,h2,h3,h4,h5{font-weight: 100;}strong{color: var(--color-3);}div[itemprop="articleBody"] a{border-bottom: .1px solid var(--color-2); font-size: .9em; transition: all .5s ease-out;}.icon-info svg{font-size: 2.5em; color: var(--color-1);}.searchText > input{background: #eaeaea; font-family: 'Font2', sans-serif !important;}input[name='searchword']{font-family: inherit; font-size: 1em; padding: 7px 7px 7px 12px; width: 90%;}.btn{background: #4d5299; background: var(--color-1); box-shadow: inset 0 -.8em 1em 0 #99a2e8; box-shadow: inset 0 -.8em 1em 0 var(--color-4); color: #fff !important; text-shadow: 0 -1px 0 rgba(0, 0, 0, 0.25); border-color: rgba(0, 0, 0, 0.1) rgba(0, 0, 0, 0.1) rgba(0, 0, 0, 0.25); padding: 12px; border-radius: 20px; display: inline-block; cursor: pointer; transition: all .5s ease-out; border-width: .1px;}.btn:hover{box-shadow: inset 0em 0em 0.5em 0em #99a2e8; box-shadow: inset 0em 0em 0.5em 0em var(--color-4);}input,textarea,input[name='searchword']{border: 1px solid var(--color-1); color: var(--color-1);}/* Font 1 */@font-face{font-family: 'Font1'; font-style: normal; font-weight: <?=$customer->fontweight1?>; src: url('../assets/fonts/<?=$customer->font1?>.eot'); /* IE9 Compat Modes */ src: url('../assets/fonts/<?=$customer->font1?>.woff2') format('woff2'), /* Super Modern Browsers */ url('../assets/fonts/<?=$customer->font1?>.woff') format('woff'), /* Modern Browsers */ url('../assets/fonts/<?=$customer->font1?>.ttf') format('truetype'), /* Safari, Android, iOS */ url('../assets/fonts/<?=$customer->font1?>.svg#Oswald') format('svg'); /* Legacy iOS */}/* Font 2 */@font-face{font-family: 'Font2'; font-style: normal; font-weight: <?=$customer->fontweight2?>; src: url('../assets/fonts/<?=$customer->font2?>.eot'); /* IE9 Compat Modes */ src: url('../assets/fonts/<?=$customer->font2?>.woff2') format('woff2'), /* Super Modern Browsers */ url('../assets/fonts/<?=$customer->font2?>.woff') format('woff'), /* Modern Browsers */ url('../assets/fonts/<?=$customer->font2?>.ttf') format('truetype'), /* Safari, Android, iOS */ url('../assets/fonts/<?=$customer->font2?>.svg#Lato') format('svg'); /* Legacy iOS */}
<?=$customer->style?>
</style>
<script type="text/javascript">var program={};</script>
<script defer src="/assets/program.min.js"></script>
</head>
<body>
<!--JSON-LD-->
<script type="application/ld+json">{"@context":"http://schema.org","@type":"Organization","url":"<?=$customer->domain?>","name":"<?=$customer->customername?>","logo":"<?=$customer->domain?>/images/logo.png","address":{"@type":"PostalAddress","addressCountry":"AR","addressLocality":"<?=$customer->zone?>","postalCode":"<?=$customer->postal?>","streetAddress":"<?=$customer->address?>"},"contactPoint":{"@type":"ContactPoint","telephone":"+<?=$customer->phoneParsed?>","contactType":"sales","email":"<?=$customer->email?>"}}</script>
<header class="column-center"> <?php if ($this->countModules('header-top'.$customer->templateId)) : ?> <div id="header-top"><?php $module=JModuleHelper::getModules("menu")[0];$module->params=array('params' => json_encode(array("position" => "header-top".$customer->templateId)));echo JModuleHelper::renderModule($module); ?></div><?php endif; ?> <div id="header-content"><jdoc:include type="modules" name="header-content<?=$customer->templateId?>"/></div><?php if ($this->countModules('header-bottom'.$customer->templateId)) : ?> <div id="header-bottom"><jdoc:include type="modules" name="header-bottom<?=$customer->templateId?>"/></div><?php endif; ?></header><main class="row-center"> <div id="breadcrumb"> <jdoc:include type="modules" name="breadcrumb"/> </div><?php if ($this->countModules('main-top'.$customer->templateId)) : ?> <div id="main-top"><jdoc:include type="modules" name="main-top<?=$customer->templateId?>"/></div><?php endif; ?> <article id="main-content"> <?php if ($this->countModules('filter')){echo '<jdoc:include type="modules" name="filter"/>';}else{echo '<jdoc:include type="component"/>';}?> </article> <?php if ($this->countModules('main-bottom'.$customer->templateId)) : ?> <div id="main-bottom"><jdoc:include type="modules" name="main-bottom<?=$customer->templateId?>"/></div><?php endif; ?></main><footer class="column-center"> <?php if ($this->countModules('footer-top'.$customer->templateId)) : ?> <div id="footer-top"><jdoc:include type="modules" name="footer-top<?=$customer->templateId?>"/></div><?php endif; ?> <div id="footer-content"><jdoc:include type="modules" name="footer-content<?=$customer->templateId?>"/></div><?php if ($this->countModules('footer-bottom'.$customer->templateId)) : ?> <div id="footer-bottom"><jdoc:include type="modules" name="footer-bottom<?=$customer->templateId?>"/></div><?php endif; ?></footer>
<!--Modal-->
<div class="modal-close">x</div><style type="text/css">.modal{opacity:0;z-index:-10000;width:100%;position:absolute;color:#eeeeee;transition:opacity .7s ease-out;}.modal:before{content:'';display:block;position:fixed;top:0;left:0;width:100%;height:100%;background-color:rgba(0,0,0,0.3);}.modal-simple{opacity:0;z-index:-10000;width:100%;position:absolute;color:#3e3e3e;color:var(--color-2);transition:opacity .7s ease-out;}.modal-hidden{visibility:hidden;height:0px;margin:0px;padding:0px;overflow:hidden;transition:all 1s ease-out;position:relative;}.modal-close{color:#fff;display:none;position:absolute;top:1em;right:1em;z-index:100000;font-size:2em;cursor:pointer;}</style>
<!--Loading-->
<div id="loading" class="modal modal-hidden column-center"><i class="fas fa-sync-alt fa-spin fa-3x fa-fw"></i><span class="sr-only">Cargando...</span></div> 
<!--Loading Simple-->
<div id="loading-simple" class="modal-simple modal-hidden column-center"><i class="fas fa-cog fa-spin fa-2x fa-fw"></i><span class="sr-only">Cargando...</span></div> 
<!--Locked-->
<div id="locked" class="modal modal-hidden column-center"><div id="locked-title">Sitio Bloqueado</div><div>Contacte con el Administrador</div></div><style type="text/css">#locked:before{background-color:rgba(0,0,0,0.7);}#locked div{font-size:2em;z-index:inherit;text-align:center;width:95%;}#locked-title{color:#e4e465;font-size:2.7em !important;}</style> 
<!--Notify-->
<div id="notify" class="modal-hidden"> <div id="notify-title"></div><div id="notify-message"></div></div><style type="text/css"> #notify{color: #000; position: absolute; top: 2em; right: 2em; padding: 1em; max-width: 30%; background-color: #ffffee; border-left: 15px solid; box-shadow: 0px 0px 5px rgba(51, 51, 51, 0.3); visibility: hidden; opacity: 0; z-index: -1; transform: translateY(-2em); transition: all 1s ease-in-out 0s, visibility 0s linear 1s, z-index 0s linear 0.01s;}.notify-warning{border-left-color: #fff06a !important;}.notify-success{border-left-color: #5cb85c !important;}.notify-fail{border-left-color: #ff674c !important;}#notify-message{font-size: .8em;}.notify-show{visibility: visible !important; opacity: 1 !important; z-index: 1000 !important; transform: translateY(0%) !important; transition-delay: 0s, 0s, 0.3s !important;}</style>
</body>
</html>