<?php
class My_Model_Factory_Mapper
{
	private static $_instance = null;
	
	private $_config;	
	
	public function __construct()
	{
		$this->_loadConfig();
	}
	
	/**
	 * Singleton design pattern
	 * 
	 * @return My_Model_Factory_Mapper
	 */
	public static function instance()
	{
		if (empty(self::$_instance)) {
			self::$_instance = new My_Model_Factory_Mapper();
		}
		
		return self::$_instance;
	}
	
	
	public function getConfig()
	{
		return $this->_config;
	}
	
	
	public function setConfig(Zend_Config $config)
	{
		$this->_config = $config;
		
		return $this;
	}
	
	
	/**
	 * 
	 * @param My_Model_Domain $obj
	 * 
	 * @return array
	 */
	public function getMappers( My_Model_Domain $obj, $action )
	{
		$moduleName = Zend_Controller_Front::getInstance()->getRequest()->getModuleName();
		
		if ( $obj instanceof My_Model_Domain_Message ) {
			$arg = get_class( $obj ) . $obj->getMessageGroup();

			return $this->_config->$arg->$moduleName->$action->toArray();
			
		} elseif ( $obj instanceof My_Model_Domain_Command ) {
			$command = $obj->getCommand();
			
			$arg = get_class( $obj ) . $command['message_group'];
			return $this->_config->$arg->$moduleName->$action->toArray();
			
		} else {
			$arg = get_class( $obj );
			return $this->_config->$arg->$moduleName->$action->toArray();
			
		}
	}
	
	/**
	 * 
	 * 
	 */
	private function _loadConfig()
	{
		
		$domainMapperConfig = Zend_Registry::get('configuration')->domainMappers;
		
		if ( empty($domainMapperConfig) ) {
			throw new Exception('No configuration for domain mappers');
		}
		
		$this->_config = $domainMapperConfig;
	}
}