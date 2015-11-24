<?php

abstract class My_Model_Abstract {
	protected static $_instance = array();
	protected $_data = array();
	protected $_filename = '';
	
	protected function __construct() {
	}

	/**
	 * Singleton model use get_called_class late static binding
	 * function to determine name of the called class.
	 *
	 * @return My_Model_Abstract
	 */
	public static function getInstance()
	{
		$className = get_called_class();
		if (!isset(self::$_instance[$className])) {
			self::$_instance[$className] = new $className;
		}
		return self::$_instance[$className];
	}

	/**
     * Static factory method. Always produce new instance.
     * 
     * @return Application_Model_Abstract 
     */
    public static function newInstance()
    {
    	$className = get_called_class();
    	return new $className;
    }
    

    public function get($id) {
    	return @$this->_data[$id];
    }
    
    public function getAll() {
    	return $this->_data;
    }
}