<?php
class My_Model_Domain
{
	
	protected $_data = array();
	protected $_id;
	protected $_mappers = array();
	protected $_mappersForced = array();
	protected $_client_id_unset_from_data = true;
	protected $_client_id_key = "";
	
	public function __construct($id = null)
	{
		if (!is_null($id)) {
			$this->setId($id);
		}
	}
	
	public function getClientIdExist()
	{
		return array_key_exists($this->_client_id_key, $this->_data);	
	}
	
	public function getClientIdUnsetFromData() {
		return $this->_client_id_unset_from_data;
	}
	
	public function setClientIdUnsetFromData( $key) {
		$this->_client_id_unset_from_data = $key;
	}
	
	public function getClientIdKey()
	{
		return $this->_client_id_key;
	}
	
	public function getCid()
	{		
		return $this->_data[$this->_client_id_key];
	}
	
	
	/**
	 * Magic __call method
	 * 
	 * @param string $method
	 * @param array $args
	 */
	public function __call($method, $args)
	{
        $methodType = substr($method, 0, 3);
        $paramName = strtolower(substr($method, 3, 1)) . substr($method, 4);
        $paramSplit = preg_split('/(?<=\\w)(?=[A-Z])/', $paramName);
        
        $param = strtolower(join('_', $paramSplit));
        
        switch($methodType) {
            case 'set':
            	$arg = current($args);
				if(in_array($param, array("client_id", "id_client"))) {
					$this->_client_id_key = $param;					
				}            	
                $this->_data[$param] = $arg;
                return $this;
                break;
            case 'get':
            	if (isset($this->_data[$param])) {
                	return $this->_data[$param];
            	} else {
            		return null;
            	}
                break;
        }        
	}

	/**
	 * Magic __get method
	 * 
	 * @param string $name
	 * 
	 * @return mixed
	 */
	public function __get($name)
	{
		if(array_key_exists($name, $this->_data)) {
			return $this->_data[$name];
        } else {
        	return null;
        }
	}

	/**
	 * Magic __set method
	 * 
	 * @param string $name
	 * @param mixed $value
	 */
	public function __set($name, $value)
	{		
		$this->_data[$name] = $value;
		
        return $this;
	}	
		
	public function addMapper( $mapper ) 
	{
		if ( !empty( $mapper ) && !in_array( $mapper, $this->_mappersForced ) ) {
			$this->_mappersForced[] = $mapper;
		}

		return $this;
	}
	
	public function getData()
	{
		return $this->_data;
	}

	public function getId()
	{
		if ( isset($this->_data['id']) ) {
	
			return $this->_data['id'];
		} else {
				
			return $this->_id;
		}
	}	
	
	public function getMappers( $action = null )
	{
		if ( empty($this->_mappersForced) && !is_null($action) ) {
			$this->_mappers = My_Model_Factory_Mapper::instance()->getMappers( $this, $action );		
		}
		
		return empty($this->_mappersForced) ? $this->_mappers : $this->_mappersForced;
	}

	public function markBulkDelete()
	{
		My_Model_Watcher::registerBulkDelete($this);
		
		return $this;		
	}

	public function markClean()
	{
		My_Model_Watcher::registerClean($this);
		
		return $this;
	}

	public function markDelete()
	{
		My_Model_Watcher::registerDelete($this);
		
		return $this;		
	}	
	
	public function markDirty()
	{
		My_Model_Watcher::registerDirty($this);
		
		return $this;		
	}
	
	public function markFileInsert( $fileName )
	{
		My_Model_Watcher::registerFileInsert($fileName, $this);
		
		return $this;		
	}
	
	public function markNew()
	{
		My_Model_Watcher::registerNew($this);
		
		return $this;
	}	
	
	public function setData(array $data)
	{
		if(array_key_exists("client_id", $data)) {
			$this->_client_id_key = "client_id";
		}
		
		if(array_key_exists("id_client", $data)) {
			$this->_client_id_key = "id_client";
		}
		
		
		
		$this->_data = $data;
		
		return $this;
	}	

	public function setId($id)
	{
		$this->_id = $id;
		
		return $this;
	}
	
	public function setMappers(array $mappers)
	{
		$this->_mappersForced = $mappers;
		
		return $this;
	}
	
	public function unsetField($name)
	{		
		if( isset($this->_data[$name]) ) {
			unset($this->_data[$name]);
		}
		
        return $this;
	}
}