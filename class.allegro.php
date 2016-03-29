<?php
/*
 * class.allegro.php
 * author: pizzaminded
 * license: MIT 
 * build: 260316
 * last modified: 26-03-2016 16:50
 */
class Allegro {
	
	/* login data */
	private $all_username   = 'your_username_here'; //nazwa uzytkownika
	private $all_password   = 'your_password_here'; //haslo
	private $all_webapi_key = 'your_webapi_here'; //klucz do webapi
	private $all_country    = 1; //numer kraju w webapi (1 - Polska)
	
	/* app configuration etc */
	private $build = 260316;
	private $soap = null; //uchwyt do soapclient
	private $status = array();	
	private $login = array();	
	private $session_handler = null;
	private $verkey = '';
	
	public function __construct() {
	//<__construct>
		try {
			$this -> soap = new SoapClient('http://webapi.allegro.pl/uploader.php?wsdl');
			$this -> status = $this -> soap -> doQuerySysStatus(1, 1, $this -> all_webapi_key);
			$this -> verkey = $this ->status['ver-key'];
			$this -> login = $this -> soap -> doLogin(
														$this -> all_username, 
														$this -> all_password, 
														$this -> all_country, 
														$this -> all_webapi_key,
														$this -> verkey );
			$this -> session_handler = $this ->login['session-handle-part'];
		}
		catch(SoapFault $e)	{
			//todo: jesli nie ma funkcji do bledow, implementuje ja, jesli jest to zglasza blad ze nie laczy z allegro
			die('Failed to connect: '.$e->getMessage(););
		}
		
	//</__construct>
	}
	public function getItemCount($type) {
		/*
		 * getItemCount 
		 * @build: 290316
		 * @version: 1.1
		 * @return: int or bool(false)
		 */
		//typy aukcji - patrz dokumentacja allegro 
		$types = array('bid','won','not_won','watch','watch_cl','sell', 'sold', 'not_sold', 'future');
		$type = strtolower($type); 
		if(in_array($type, $types)) {
			$result = $this-> soap -> doMyAccountItemsCount($this -> session_handler, $type,  array());
			return $result;
		} else {
			throw new Exception("Unspecified offer type");
		}
	//</getItemCount>
	}
		public function checkIfItemSold($id) {
		/** 
		  * checkIfItemSold
		  * @build 290316
		  * @version: 1.0
		  * @param $id long
		  * @return  bool
		  */
		  if(!is_numeric($id)) {
			  throw new Exception("ID must be numeric");
		  } else {
			$request = $this-> soap -> doGetItemsInfo($this -> session_handler, array($id),0,0,0,0,0,0);
			$response = (array)$request["array-item-list-info"][0]->{'item-info'};
			if($response['it-ending-info'] < 2 )
			{
				return false; //aukcja dalej trwa
			} else {
				return true; //aukcja skasowana/zakonczona
			}
		  }
	//</checkIfItemSold>
	}
	public function checkIfItemsSold($array) {
		/** 
		  * checkIfItemSold
		  * sprawdza czy dane aukcje zostaly zamkniete
		  * @version: 1.0
		  * @param $array array
		  * @return  bool
		  */
		define('ALL_MAX_ITEMS',25);
		
		if(!is_array($array)) {
			throw new Exception("Input must be array");
		} else {
			$count = count($array);
			$input = array();
			if($count <= ALL_MAX_ITEMS) {
				$input[] = $array;
			} else {
				while(count($array) > 0 ) {
					$input[] = array_splice($array,0, ALL_MAX_ITEMS);
				}
			}		
			unset($array);
			$out = array();
			$rounds = count($input);
			for($z = 0; $z < $rounds; $z++) {
				$request = $this-> soap -> doGetItemsInfo($this -> session_handler, $input[$z],0,0,0,0,0,0);
				$roundcount = count($input[$z]) ;
				for($y = 0; $y < $roundcount; $y++ ) {
					$response = (array)$request["array-item-list-info"][$roundcount - 1]->{'item-info'};
					if($response['it-ending-info'] < 2 )
					{
						$out[$input[$z][$y]] = false;
					} else {
						$out[$input[$z][$y]] = true;
					} 
				}
			}
			return $out; 
		  }
	//</checkIfItemsSold>
	}
//koniec klasy
}
?>
