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
	private static $soap = null; //uchwyt do soapclient
	//private $link = null; //uchwyt do allegro api
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
		catch(SoapFault $error)	{
			//todo: jesli nie ma funkcji do bledow, implementuje ja, jesli jest to zglasza blad ze nie laczy z allegro
			die("Blad polaczenia z allegro");
		}
		
	//</__construct>
	}
	public function getItemCount($type) {
		/*
		 * getItemCount 
		 * build: 260316
		 * version: 1.0
		 * return: int or bool(false)
		 */
		//typy aukcji - patrz dokumentacja allegro 
		$types = array('bid','won','not_won','watch','watch_cl','sell', 'sold', 'not_sold', 'future');
		$type = strtolower($type); 
		if(in_array($type, $types)) {
			$result = $this->soap -> doMyAccountItemsCount($this -> session_handler, $type,  array());
			return $result;
		} else {
			return false;
		}
	//</getItemCount>
	}
//koniec klasy
}
?>
