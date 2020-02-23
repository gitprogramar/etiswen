<?php
// no direct access
defined('_JEXEC') or die;
require_once ( JPATH_ROOT.'/api/utils.php' );

$titles	= array();
$descriptions	= array();
$links	= array();
$templateVideo = "";
$templateImage = "";
$templateAssets = "";
$customParam = null;
// Get parameters
if (strlen($params->get('titles'))) {
	$titles	= explode("\n", $params->get('titles'));
}
if(strlen($params->get('descriptions'))) {
	$descriptions	= explode("\n", $params->get('descriptions'));
}
if (strlen($params->get('links'))) {
	$links	= explode("\n", $params->get('links'));
}
if (strlen($params->get('posters'))) {
	$posters = explode("\n", $params->get('posters'));
}
if (strlen($params->get('templateVideo'))) {
	$templateVideo	= $params->get('templateVideo');
}
if (strlen($params->get('templateImage'))) {
	$templateImage	= $params->get('templateImage');
}
if (strlen($params->get('templateAssets'))) {
	$templateAssets	= $params->get('templateAssets');
}
if(null !== $params->get('custom_param')) {
	$customParam = strtolower(str_replace(' ','-',$params->get('custom_param')));
}

// Get Images
$files = array();
$directory = trim($params->get('directory'));
if(strpos($directory, ",") !== false) {
	$files = explode(",", $directory);
}
else {
	$utils = new Utils();
	$files = $utils->fileGet($directory);	
}

$index = 0;
$html = '<div id="'.(null !== $customParam ? $customParam : $module->position).'" class="slider"><div class="animate" draggable="true">';
$dots = '<div class="dots" style="width: 100% !important;"><div>';
foreach($files as $file) {
	$template = "";
	if(strpos($file, ".mp4") !== false || strpos($file, ".webm") !== false || strpos($file, ".ogg") !== false) {
		$template = $templateVideo;
	}
	else {
		$template = $templateImage;
	}
	$template = str_replace("[path]", $file, $template);
	$template = str_replace("[poster]", $posters[$index], $template);
	$template = str_replace("[title]", $titles[$index], $template);
	$template = str_replace("[description]", $descriptions[$index], $template);
	$html .= isset($links[$index]) ? str_replace("[link]", $links[$index], $template) : $template;
	$dots .= '<span data-id="'.$index.'"></span>';
	$index++;
}
$html .= '</div>'.$dots.'</div></div></div>';
eval('?>'.$html.$templateAssets.'<?php;');

require JModuleHelper::getLayoutPath('mod_program_slider', $params->get('layout', 'default'));

