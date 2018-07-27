<?php
   /**
   * @version   2.0 $
   * @author Programar
   * @copyright Copyright (C) 2018
   */
   
   /* No Direct Access */
   defined('_JEXEC') or die('Restricted index access');
   require_once (JPATH_ROOT.'/api/utils.php');
   
   $doc = JFactory::getDocument(); /*var_dump($doc)*/;
   $this->language  = $doc->language;
   /*$app = JFactory::getApplication(); var_dump($app);*/
   $utils = new Utils();
   $utils->enterpriseSession($doc->params["sitetitle"]);
   $customer = $_SESSION["customer"];
   $template = $_SESSION["template"];
   $theme = $_SESSION["theme"];
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
<meta name="theme-color" content="<?=$theme->first?>"/>
<style type="text/css" id="common">
* {
    margin: 0px;
    padding: 0px;
}

html {
    overflow-y: scroll;
    overflow-x: hidden;	
}

body {
    overflow: hidden;
    position: relative;
    font-family: Font1;
}

a,
a:visited,
a:active,
svg {
	text-decoration: none;
    color: var(--second);
    transition: color .3s ease-out;
}

a:hover {
    color: var(--third);
}

h1, h2, h3, h4, h5 {
	font-weight: 100;
}

img {
    max-width: 100%;
    width: 100%;
    height: auto;
}

input,
textarea, select{
	font-family: inherit;
	border: 1px solid var(--first);
    color: var(--first);
	font-size: 1em;
    padding: 7px 7px 7px 12px;
}

strong {
    color: var(--third);
}

fieldset {
    border: none;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
}

blockquote {
    border-left: 5px solid var(--first);
    padding: 0 0 0 15px;
    margin: 20px 0 15px;
}

ul.breadcrumb {
	display: flex;
	align-self: flex-start;
	list-style: none;
    font-size: .7em;
	padding: 2% 5% 0% 5%;
}
ul.breadcrumb span.divider {
	margin-right: 5px;
}

main {
    background: var(--bgbody);
    color: var(--black);
	min-height: 60vh;
}

.page-header {
    color: inherit;
    font-size: 1.2em;
    text-align: center;    
    text-transform: uppercase;
    position: relative;
}

article > div {
    padding: 4% 5% 0% 5%;
}

div[itemprop="articleBody"] > div {
	padding: 4% 0% 4% 0%;
}

div[itemprop="articleBody"] img:not(.no-border) {
    border: 1px solid #dad3d3;
}

div[itemprop="articleBody"] ul li {
    margin-left: 20px;
}

footer > div {
	padding: 4% 0% 4% 0%;
}

.t-center {
    text-align: center;
}

.t-left {
    text-align: left;
}

.t-right {
    text-align: right;
}

.column-pad *:not(.no-column-pad):not(a):not(svg),
fieldset * {
    margin-bottom: 10px;
}


/*Buttons*/
.btn {
    background: var(--first);
    box-shadow: inset 0 -.8em 1em 0 var(--third);
    color: #fff;
	font-size: inherit;
    text-shadow: 0 -1px 0 rgba(0, 0, 0, 0.25);
    border-color: rgba(0, 0, 0, 0.1) rgba(0, 0, 0, 0.1) rgba(0, 0, 0, 0.25);
    padding: 12px;
    border-radius: 20px;
    display: inline-block;
    cursor: pointer;
    transition: box-shadow .5s ease-out;
    border-width: .1px;
	line-height: 1em;
}

.btn:hover {    
    box-shadow: inset 0em 0em 0.5em 0em var(--third);
}

a.btn, a.btn * {
	color: var(--white);
}

.btn-transparent {
	border: 2px solid var(--white);
    background-color: transparent;
    text-shadow: none;
    box-shadow: none;
    transition: all .3s ease-out; 
}

.btn-transparent:hover * {
	color: #2b2b2b;
}

.btn-transparent:hover {
    background-color: rgba(255, 255, 255, 0.6);
	color: #2b2b2b;
    box-shadow: none;
}

.btn-transparent-theme {
	color: var(--first);
	border: 2px solid var(--first);
    background-color: transparent;
    text-shadow: none;
    box-shadow: none;
    transition: all .3s ease-out; 
}

.btn-transparent-theme:hover, .btn-transparent-theme:hover * {
    color: var(--white);
	box-shadow: none;
}

a.btn-transparent-theme, a.btn-transparent-theme * {
	color: var(--first);
}

.video {
    position: relative;
    padding-bottom: 56.25%;
    padding-top: 25px;
    height: 0;
    width: 100%;
}
.video iframe {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
}

/*login*/
#login-form {
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
}

#login-form * {
	margin-bottom: 10px;
}

input.input-small {
    margin-left: 10px;
}

div.input-prepend {
    text-align: right;
}

/*login end*/

/*search*/
div.search {
    font-size: .8em;
}

input[name='searchword'] {    
    width: 90%;
}

dt.result-title {
    margin-top: 2%;
}

ul.tags,
dd.result-category {
    display: none;
}

#searchForm {
    display: flex;
    flex-direction: column;
}

#searchForm > div {
	margin-bottom: 10px;
}

.btn-toolbar {
    display: flex;
    width: 100%;
    justify-content: space-between;
}

.btn-toolbar > div {
    width: 25%;
}

.btn-toolbar div:first-child {
    width: 45%
}

div.pagination {
    margin-top: 40px;
}

div.pagination ul {
    list-style: none;
}

div.pagination ul li {
    display: inline;
    margin-right: 10px;
}
/*search end*/

/*Large*/

@media screen and (min-width:1000px) {
    body {
        font-size: 22px;
    }
    .video {
        width: 80%;
    }
}


/*Landscape*/

@media screen and (max-width:1200px) {
    body {
        font-size: 20px;
    }
}


/*Medium*/

@media screen and (max-width:1000px) {
    body {
        font-size: 17px;
    }
	footer > div {
		flex-direction: column;
		padding: 10% 0% 4% 0%;
	}
	footer > div > div {
		width: 85% !important;
	}
}


/*Small*/

@media screen and (max-width:600px) {
    body {
        font-size: 15px;
    }
    .page-header {
        padding: 8% 4% 0%;
    }
	article > div,
    div[itemprop="articleBody"] > div
	{
        flex-direction: column;
    }
    div[itemprop="articleBody"] > div > div {
        width: 100%;
        margin: 4% 0%;
    }        
}

/*Slider*/div.slider{width: 100%;position: relative;overflow: hidden; cursor: -webkit-grab; cursor: grab;}div.slider > div:not(#dots){height: inherit; overflow-y: hidden;}div.slider div.slide{position:relative;overflow-y: hidden;float: left;background-repeat: no-repeat;width:100%;height: inherit;background-size: cover;background-position-x: 50%;}div.slider div.animate{transition: transform 0.6s ease-out;}/*Slider Video*/div.slider div.video-wrapper{z-index:-1;position: absolute;cursor: pointer;top: 0px;left: 0px;bottom: 0px;right: 0px;overflow: hidden;background-color: #dddddd; background-repeat: no-repeat; background-position: 50% 50%;}div.slider video{margin: auto;position: absolute;z-index: -1;top: 50%;left: 50%;transform: translate(-50%, -50%);visibility: visible;opacity: 1;width: 100%;height: auto;display: inline-block;vertical-align: baseline;}/*Slider Dots*/div.slider #dots{width: 100%; position: absolute; left: 0; bottom: 0; margin-top: 16px; margin-bottom: 5px; text-align: center;}div.slider #dots > div{width: auto; display: inline-block; border-radius: 15px;cursor: pointer; background: rgba(0, 0, 0, .4);}div.slider #dots span{cursor: pointer; height: 13px; width: 13px; padding: 0; margin: 8px 8px 3px 8px; border: 1px solid #ccc; border: 1px solid var(--first); border-radius: 50%; display: inline-block; text-align: center;transition: background-color .5s ease-out;}@media screen and (min-width:1000px) {div.slider #dots span:hover{color: #000; background-color: #fff; background-color: var(--first);}}div.slider .active-dot{color: #000; background-color: #fff; background-color: var(--first);}
/*Tooltip USAGE: document.getElementById('').attr("data-tooltip", "You must create a project before saving");*/[data-tooltip]{position: relative; z-index: 2;}[data-tooltip]:before{visibility: hidden; opacity: 0;}[data-tooltip]:before{position: absolute; bottom: 100%; left: 60%; margin-bottom: 5px; margin-left: -80px; padding: 7px; width: 160px; border-radius: 3px; content: attr(data-tooltip); text-align: center; line-height: 1.2; transition: all .5s ease-out; font-family: LatoRegular;font-size: 16px;color: #9c9c9c;background: #fff;border-radius: 7px;border: 1px solid #dddddd;padding: 10%;}[data-tooltip]:hover:before{visibility: visible; opacity: 1;}
/*Flexbox*/.column-center{display: flex; flex-direction: column; justify-content: center; align-items: center;}.column-vertical{height: inherit;}.column-space-around{display: flex; flex-direction: column; justify-content: space-around; align-items: center;}.column-space-between{display: flex; flex-direction: column; justify-content: space-between; align-items: center;}.row-center{display: flex; justify-content: center; align-items: center;}.row-space-between{display: flex; justify-content: space-between; align-items: center;}.row-space-around{display: flex; justify-content: space-around; align-items: center;}.flex{display: flex;}.j-center{justify-content: center;}.j-start{justify-content: flex-start;}.j-end{justify-content: flex-end;}.j-between{justify-content: space-between;}.j-around{justify-content: space-around;}.a-center{align-items: center;}.a-start{align-items: flex-start;}.d-column{flex-direction: column;}.d-row{flex-direction: row;}.o-1{order: 1;}.w-10{width: 10%;}.w-15{width: 15%;}.w-20{width: 20%;}.w-23{width: 23%;}.w-25{width: 25%;}.w-30{width: 30%;}.w-40{width: 40%;}.w-45{width: 45%;}.w-50{width: 50%;}.w-55{width: 55%;}.w-60{width: 60%;}.w-65{width: 65%;}.w-70{width: 70%;}.w-80{width: 80%;}.w-90{width: 90%;}.w-100{width: 100%;}/*Flexbox end

<?php if(strlen($template->fontfirst)>0) {
    echo "/* Font 1 */@font-face{font-family: Font1; font-style: normal; font-weight: ".$template->weightfirst." src: url('../assets/fonts/".$template->fontfirst.".eot'); /* IE9 Compat Modes */ src: url('../assets/fonts/".$template->fontfirst.".woff2') format('woff2'), /* Super Modern Browsers */ url('../assets/fonts/".$template->fontfirst.".woff') format('woff'), /* Modern Browsers */ url('../assets/fonts/".$template->fontfirst.".ttf') format('truetype'), /* Safari, Android, iOS */ url('../assets/fonts/".$template->fontfirst.".svg#Oswald') format('svg');}";
}

if(strlen($template->fontsecond)>0) {
    echo "/* Font 2 */@font-face{font-family: Font2; font-style: normal; font-weight: ".$template->weightsecond."; src: url('../assets/fonts/".$template->fontsecond.".eot'); /* IE9 Compat Modes */ src: url('../assets/fonts/".$template->fontsecond.".woff2') format('woff2'), /* Super Modern Browsers */ url('../assets/fonts/".$template->fontsecond.".woff') format('woff'), /* Modern Browsers */ url('../assets/fonts/".$template->fontsecond.".ttf') format('truetype'), /* Safari, Android, iOS */ url('../assets/fonts/".$template->fontsecond.".svg#Lato') format('svg');}";
}
?>

</style>
<style type="text/css" id="theme">:root{--white: #ffffff; --black: #29293a; --first: <?=$theme->first?>; --second: <?=$theme->second?>; --third: <?=$theme->third?>; --menu: <?=$theme->menu?> ; --bgheader: <?=$theme->bgheader?>; --bgbody: <?=$theme->bgbody?>; --bgfooter: <?=$theme->bgfooter?>; --extra: <?=$theme->extra?>;}</style>
<?=$template->head?>
<script defer src="/assets/program.min.js?v=1"></script>
</head>
<body>
<?php if(strtok($_SERVER["REQUEST_URI"],'?') == "/") { ?>
<script type="application/ld+json">{"@context":"http://schema.org","@type":"Organization","url":"<?=$customer->domain?>","name":"<?=$customer->customername?>","logo":"<?=$customer->domain?>/images/logo.png","address":{"@type":"PostalAddress","addressCountry":"AR","addressLocality":"<?=$customer->zone?>","postalCode":"<?=$customer->postal?>","streetAddress":"<?=$customer->address?>"},"contactPoint":{"@type":"ContactPoint","telephone":"+<?=$customer->phoneParsed?>","contactType":"sales","email":"<?=$customer->email?>"}}</script>
<?php }?>
<header class="column-center">
	<?php $module=JModuleHelper::getModules("menu")[0];$module->params=array('params' => json_encode(array("position" => "header-top".$customer->templateId)));echo JModuleHelper::renderModule($module);?>
	<jdoc:include type="modules" name="header-content<?=$customer->templateId?>" />
	<jdoc:include type="modules" name="header-bottom<?=$customer->templateId?>" />
</header>
<main class="column-center">
	<jdoc:include type="modules" name="main-top<?=$customer->templateId?>" />
	<article id="main-content" class="w-100">
		<?php if ($this->countModules('filter')){echo '<jdoc:include type="modules" name="filter"/>';}else{echo '<jdoc:include type="component"/>';}?>
	</article>
	<jdoc:include type="modules" name="main-bottom<?=$customer->templateId?>" />
</main>
<footer class="column-center">
	<jdoc:include type="modules" name="footer-top<?=$customer->templateId?>" />                    
	<jdoc:include type="modules" name="footer-content<?=$customer->templateId?>" />            
	<jdoc:include type="modules" name="footer-bottom<?=$customer->templateId?>" />
</footer>
<!--Modal-->
<div class="modal-close">x</div><style type="text/css">.modal{opacity:0;z-index:-10000;width:100%;position:absolute;color:#eeeeee;transition:opacity .7s ease-out;}.modal:before{content:'';display:block;position:fixed;top:0;left:0;width:100%;height:100%;background-color:rgba(0,0,0,0.3);}.modal-simple{opacity:0;z-index:-10000;width:100%;position:absolute;color:#3e3e3e;color:var(--second);transition:opacity .7s ease-out;}.modal-hidden{visibility:hidden;height:0px;margin:0px;padding:0px;overflow:hidden;transition:all 1s ease-out;position:relative;}.modal-close{color:#fff;display:none;position:absolute;top:1em;right:1em;z-index:100000;font-size:2em;cursor:pointer;}</style>
<!--Loading-->
<div id="loading" class="modal modal-hidden column-center"><i class="fas fa-sync-alt fa-spin fa-3x fa-fw"></i><span class="sr-only">Cargando...</span></div> 
<!--Loading Simple-->
<div id="loading-simple" class="modal-simple modal-hidden column-center"><i class="fas fa-cog fa-spin fa-2x fa-fw"></i><span class="sr-only">Cargando...</span></div> 
<!--Locked-->
<div id="locked" class="modal modal-hidden column-center"><div id="locked-title">Sitio Bloqueado</div><div>Contacte con el Administrador</div></div><style type="text/css">#locked:before{background-color:rgba(0,0,0,0.7);}#locked div{font-size:2em;z-index:inherit;text-align:center;width:95%;}#locked-title{color:#e4e465;font-size:2.7em !important;}</style> 
<!--Notify-->
<div id="notify" class="modal-hidden"> <div id="notify-title"></div><div id="notify-message"></div></div><style type="text/css"> #notify{color: #000; position: absolute; top: 2em; right: 2em; padding: 1em; max-width: 30%; background-color: #ffffee; border-left: 15px solid; box-shadow: 0px 0px 5px rgba(51, 51, 51, 0.3); visibility: hidden; opacity: 0; z-index: -1; transform: translateY(-2em); transition: all 1s ease-in-out 0s, visibility 0s linear 1s, z-index 0s linear 0.01s;}.notify-warning{border-left-color: #fff06a !important;}.notify-success{border-left-color: #5cb85c !important;}.notify-fail{border-left-color: #ff674c !important;}#notify-message{font-size: .8em;}.notify-show{visibility: visible !important; opacity: 1 !important; z-index: 1000 !important; transform: translateY(0%) !important; transition-delay: 0s, 0s, 0.3s !important;}@media screen and (max-width:600px){#notify {max-width: 50%;}}</style>
</body>
</html>