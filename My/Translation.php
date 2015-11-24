<?php
class My_Translation {
	
	private static $_instance;
	private  $_resource;
	private $_lang;
	
	private function __construct( $lang ){
		$this->_resource = new Zend_Translate(
			array(
					'adapter' => 'csv',
					'content' => APPLICATION_PATH. "/../languages/backend/messages.en",
					'locale' => "en",
					'delimiter' => ','
			)
		);
				
		
		if($lang !== "") {
			$this->_resource->addTranslation(
					array(
							'content' => APPLICATION_PATH. "/../languages/backend/messages.$lang",
							'locale' => "$lang"
					)
			);
		}
	}
	
	public function getResource( ){
		return $this->_resource;
	}
	
	public function getTranslate ($literal ){
// 		error_log( print_r($this->getResource(), true), 3, APPLICATION_PATH.'/data/db/logs.log');
		$resource = $this->getResource();
		return $resource->_($literal) ;
	}
	
	public static function translate( $literal ){
		if ( empty( self::$_instance )){
			$lang = Zend_Auth::getInstance()->getIdentity()->language;					
			self::$_instance = new My_Translation($lang); 
		}
   
		return self::$_instance->getTranslate( $literal);
		
	}
	
	public static function publicTranslate ($literal, $lang='sr') {
		if ( empty( self::$_instance )){			
			self::$_instance = new My_Translation($lang);
		}
		 
		return self::$_instance->getTranslate( $literal);
		
		
	}
		
	
}