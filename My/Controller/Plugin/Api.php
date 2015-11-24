<?php
class My_Controller_Plugin_Api extends My_Controller_Plugin_Abstract
{

	  
	
    public function routeShutdown(Zend_Controller_Request_Abstract $request)
	{
		$request->setControllerName('index');
		$request->setActionName('index');
	}	
    
    
    public function dispatchLoopStartup( Zend_Controller_Request_Abstract $request )
    {
    
    }

	
	public function preDispatch( Zend_Controller_Request_Abstract $request )
	{
	
	}	
	
	
	public function postDispatch( Zend_Controller_Request_Abstract $request )
	{

	}
	
	
	public function dispatchLoopShutdown()
	{
		
	}

	
    protected function _init()
	{

	}	
	
	
}