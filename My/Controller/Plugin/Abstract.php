<?php
abstract class My_Controller_Plugin_Abstract
{
    protected $_head;
	
	public function __construct()
	{
		$this->_head = new My_Head();
		
		$this->_init();
	}
	
	
    abstract protected function _init();
    
    public function routeStartup( Zend_Controller_Request_Abstract $request  )
    {
    	
    }
    
    abstract public function routeShutdown( Zend_Controller_Request_Abstract $request  );
    
    abstract public function dispatchLoopStartup( Zend_Controller_Request_Abstract $request  );
    
    abstract public function preDispatch( Zend_Controller_Request_Abstract $request  );
    
    abstract public function postDispatch( Zend_Controller_Request_Abstract $request  );
    
	abstract public function dispatchLoopShutdown();
	
}