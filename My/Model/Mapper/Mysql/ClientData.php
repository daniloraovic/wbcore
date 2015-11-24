<?php
class My_Model_Mapper_Mysql_ClientData extends My_Model_Mapper_Mysql_ClientGeneral_Abstract
{	
	
	public function __construct() {
		$this->_primary_key_field = "id";
		parent::__construct('client');
	}
	
	public function getAll( My_Model_Mapper_IdentityObject $identity ) {
		
		$dbClientData = $this->_connection;
		
		$select = $dbClientData->select();
		
		$select->from('client_parent')
			   ->joinLeft(array('client' => 'client'), 'client_parent.client_id = client.id');
		
		$select->where($this->_getSelection()->where($identity));
		
		return $dbClientData->fetchAll($select);
	}	
	
	protected function _targetClass () 
	{
		return 'My_Model_Factory_Domain_ClientData_Mysql';
	}


}