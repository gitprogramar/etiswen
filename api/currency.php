<?php	
	// Load Async Currency conversion between Dolars and Pesos
	define( '_JEXEC', 1 );
	define('JPATH_ROOT', realpath(dirname(__FILE__).'/../') );
	require_once ( JPATH_ROOT .'/api/utils.php');
	new Utils();
	$dolar = JRequest::getVar('dolar', '', 'get');
	$to = JRequest::getVar('to', '', 'get');

	if(strlen($dolar) == 0) {		
		echo "not found";
		return;
	}
	
	/*default*/	
	$content = array();
	$content["USD"] = $dolar;
	$content["CURRENCY"] = "USD";
	$content["RATE"] = $dolar;
	if(!isset($to) || strlen($to) == 0 || $to == 'USD') {		
		echo json_encode($content);
		return;
	}
		
	$currencyPerLocale = array_reduce(
		\ResourceBundle::getLocales(''),
		function (array $currencies, string $locale) {
			$currencies[$locale] = \NumberFormatter::create(
				$locale,
				\NumberFormatter::CURRENCY
			)->getTextAttribute(\NumberFormatter::CURRENCY_CODE);

			return $currencies;
		},
		[]
	);
	
	$locale = Locale::acceptFromHttp($_SERVER['HTTP_ACCEPT_LANGUAGE']);
	$currency = isset($to) ? $to : $currencyPerLocale[$locale];
	if(!isset($currency) || strlen($currency) == 0 || $currency == 'USD') {		
		echo json_encode($content);
		return;
	}
	
	// get current rate from http://themoneyconverter.com
	$page_text = file_get_contents("http://themoneyconverter.com/CurrencyConverter.aspx?tab=0&dccy1=USD&dccy2=".$currency);
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
		$rate = $utils->between($currency.'/USD', '</div>', $page_text);
		$rate = preg_split("/ = /", $rate);
	}
	else {
		$rate = preg_split("/ = /", $rate->nodeValue);
	}		
	$content = array();
	$content["USD"] = $dolar;
	$content["CURRENCY"] = $currency;
	$content["RATE"] = round($rate[1]*$dolar);
	echo json_encode($content);		
?>