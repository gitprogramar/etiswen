<?php
	// Load menu
	define( '_JEXEC', 1 );
	define('JPATH_ROOT', realpath(dirname(__FILE__).'/../') );
	require_once ( JPATH_ROOT .'/api/utils.php');
	new Utils();
	
	class Product {		
		
		public function __construct() {
			
		}
		
		function isValid($token) {						
			$currentDate = JFactory::getDate()->format('Y-m-d');
			$tokenDate = $this->decode($token);
			//return $tokenDate;
			$utils = new Utils();
			if(!$utils->dateIsValid($tokenDate, 'Y-m-d')) {
				return false;
			}
			$days = $utils->dateDifference($tokenDate, $currentDate);
			return $tokenDate < $currentDate && $days < 365 ? true : false;
		}
		
		function generateToken($params) {				
			$utils = new Utils();
			if(!$utils->dateIsValid($params["date"], 'Y-m-d')) {
				return 'Invalid date format '. $params["date"] . '. Should be: YYYY-mm-dd';
			}		
			$value = isset($params["date"]) ? $params["date"] : JFactory::getDate()->format('Y-m-d');
			return $this->encode($value);
		}
		
		function encode($value) {
			$values = explode('-', $value);
			$token = '';	
			// extra values
			$extra = range('A','F');
			foreach(range(1, 9) as $num) {
				$extra[] = $num;				
			}
			shuffle($extra);
			// append extra & values
			for($x=0; $x<3; $x++) {
				$randomKeys = array_rand($extra ,5);
				$letters = str_split(intval($values[$x])+1024);									
				for($y=0; $y<4; $y++) {				
					$token .= $extra[$randomKeys[$y]].$letters[$y];
				}
				// append last value
				$token .= $extra[$randomKeys[4]];
				if($x<2) {					
					$token .= '-';
				}
			}
			return $token;
		}
		
		function decode($value) {
			$values = explode('-', $value);
			$date = '';			
			for($x =0; $x<3; $x++) {
				$letters = str_split($values[$x]);
				$datePart = '';
				for($y=0; $y<8; $y++) {
					if($y%2!=0) {
						$datePart .= $letters[$y];
					}
				}
				$tempDate = intval($datePart)-1024;
				if(strlen($tempDate) == 1) {
					$tempDate = '0'.$tempDate;
				}
				$date .= $tempDate;
				if($x<2) {
					$date .= '-';
				}
			}
			return $date;
		}		
	}
?>