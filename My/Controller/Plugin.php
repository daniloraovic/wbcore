<?php 

class My_Controller_Plugin extends Zend_Controller_Plugin_Abstract
{
	private $_modulePlugin = null;
	
	/**
	 * 
	 */
	private function _init()
	{

		if( is_null($this->_modulePlugin )) {

		    if(is_null($this->_request->getModuleName())) $this->_request->setModuleName('default');
		    
		    $serverVars = $this->_request->getServer();
		    if (isset($serverVars['SERVER_NAME'])){
		        if ( strpos($serverVars['SERVER_NAME'],'support') !== false) {
		            if ( $this->_request->getModuleName() === 'default'){
		                $this->_request->setModuleName('default');
		            }
		        }else if ( strpos($serverVars['SERVER_NAME'], 'carsharing') !== false){
		  
		                $this->_request->setModuleName('carsharing');

		        }
		    }
		    

	
			$zendFilter = new Zend_Filter_Word_SeparatorToCamelCase('-');
			$pluginClassName = 'My_Controller_Plugin_' . $zendFilter->filter($this->_request->getModuleName());
	
	    	if(!class_exists($pluginClassName)) {
	    		throw new Exception('The front controller plugin class "' . $pluginClassName. ' does not exists');
	    	}
	    	
	    	$pluginClass = new $pluginClassName();
	    	
	    	if(!($pluginClass instanceof My_Controller_Plugin_Abstract)) {
	    		throw new Exception('The front controller plugin class "' . $pluginClass. ' must extend My_Controller_Plugin_Abstract');
	    	}
	    	
	    	$this->_modulePlugin = $pluginClass;
		}
    	
    	return $this;
    	
	}
	
	
	/**
     * Prior to routing the request
     *
     * @param Zend_Controller_Request_Abstract $request
     */
    public function routeStartup(Zend_Controller_Request_Abstract $request)
    {

    }
    
    
	/**
     * After routing the request
     *
     * @param Zend_Controller_Request_Abstract $request
     */
    public function routeShutdown(Zend_Controller_Request_Abstract $request)
    {
		$this->_init()->_modulePlugin->routeShutdown($request); 
    }
    
    
	/**
     * Prior to entering the dispatch loop
     *
     * @param Zend_Controller_Request_Abstract $request
     */
    public function dispatchLoopStartup(Zend_Controller_Request_Abstract $request)
    {
		$this->_init()->_modulePlugin->dispatchLoopStartup($request); 
    }
    
    
	/**
     * Prior to dispatching an individual action
     *
     * @param Zend_Controller_Request_Abstract $request
     */
    public function preDispatch(Zend_Controller_Request_Abstract $request)
    {
		$this->_init()->_modulePlugin->preDispatch($request); 
    }
    
    
    /**
     * After dispatching an individual action
     *
     * @param Zend_Controller_Request_Abstract $request
     */
	public function postDispatch( Zend_Controller_Request_Abstract $request )
	{
		$this->_init()->_modulePlugin->postDispatch($request); 
	}
	
	
 	/**
     * After completing the dispatch loop
     *
     * @param 
     */
	public function dispatchLoopShutdown()
	{
		$this->_init()->_modulePlugin->dispatchLoopShutdown(); 
	}
	
	
}