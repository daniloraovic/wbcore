<?php
class My_Model_Domain_ClientData extends My_Model_Domain
{
	const NO_CLIENT = 0;

	public function __construct($id = null) {
		$this->_client_id_unset_from_data = false;
		parent::__construct($id);
	}
	
	public function getKey()
	{
		return $this->_data['id'];
	}
	
	
	public function setKey($key)
	{
		$this->_data['id'] = $key;
		
		return $this;
	}
	
	
}