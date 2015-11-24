<?php 

class My_Controller_Front
{
	
	public static function dispatch( Zend_Controller_Request_Abstract $request, Zend_Controller_Response_Abstract $response )
	{
		Zend_Controller_Front::getInstance()->getDispatcher()->dispatch( $request, $response );
	}
	
}