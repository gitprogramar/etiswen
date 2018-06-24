<html><head><meta name="robots" content="noindex, nofollow"></head><body></body></html>
<?php	
	// Load Async Currency conversion between Dolars and Pesos
	define( '_JEXEC', 1 );
	define('JPATH_ROOT', realpath(dirname(__FILE__).'/../') );
	require_once ( JPATH_ROOT .'/async/utils.php');
	
	$dolar = JRequest::getVar('dolar', '', 'get');

	if(strlen($dolar) == 0) {		
		echo "not found";
		return;
	}
	
	// get current rate from http://themoneyconverter.com
	$page_text = file_get_contents("http://themoneyconverter.com/CurrencyConverter.aspx?tab=0&dccy1=USD&dccy2=ARS");
	$dom = new DOMDocument;
	libxml_use_internal_errors(true);
	$dom->loadHTML($page_text);
	// parse rate value
	$rate = $dom->getElementById('ratebox');
	if(is_null($rate)) {
		$rate = $dom->getElementById('cc-ratebox');			
	}				
	if(is_null($rate)) {
		$utils = new Utils();
		$rate = $utils->between('ARS/USD', '</div>', $page_text);
		$rate = preg_split("/ = /", $rate);
	}
	else {
		$rate = preg_split("/ = /", $rate->nodeValue);
	}		
	$content = array();
	$content["USD"] = $dolar;
	$content["ARS"] = round($rate[1]*$dolar);
	echo json_encode($content);		
?>