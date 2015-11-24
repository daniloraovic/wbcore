<?php 

class My_Controller_Dispatcher 
{
	
		
	public static function isDispatchable( Zend_Controller_Request_Abstract $request )
	{
		$dispatcher = Zend_Controller_Front::getInstance()->getDispatcher();

        // check controller
        if ( !$dispatcher->isDispatchable($request) ) {
            return false;
        }
        
		$class  = $dispatcher->loadClass($dispatcher->getControllerClass($request));
        $method = $dispatcher->formatActionName($request->getActionName());

        // check action
        return is_callable(array($class, $method));
	}

	
}
