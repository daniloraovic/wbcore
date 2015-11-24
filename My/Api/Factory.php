<?php 

class My_Api_Factory
{
	
	private $_module = 'api';	
	private $_controller;
	private $_action;
	private $_params = array();

	
	public function getModule()
	{
		return $this->_module;
	}
	
	
	public function getController()
	{
		return $this->_controller;
	}
	
	
	public function setController( $controller )
	{
		$this->_controller = $controller;
		
		return $this;
	}
	
	
	public function getAction()
	{
		return $this->_action;
	}
	
	
	public function setAction( $action )
	{
		$this->_action = $action;
		
		return $this;
	}

	
	public function getParams()
	{
		return $this->_params;
	}
		
	
	public function setParams( $params )
	{
		$this->_params = $params;
		
		return $this;
	}
	
	
	public function getResponse()
	{

		try {
			$request = new Zend_Controller_Request_Simple($this->getAction(), $this->getController(), $this->getModule(), $this->getParams());
			
			if( !My_Controller_Dispatcher::isDispatchable( $request ) ) {
				throw new Exception('API method unknown');
			}
			
			$response = new My_Controller_Response_Data();

			Zend_Controller_Front::getInstance()->getDispatcher()->dispatch( $request, $response );
			
			
		} catch (Exception $e) {
			var_dump($e->getMessage());
		}
		
		return $response;
		
	}
	
}