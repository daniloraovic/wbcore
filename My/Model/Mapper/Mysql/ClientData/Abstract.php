<?php
abstract class My_Model_Mapper_Mysql_ClientData_Abstract extends My_Model_Mapper_Mysql {
	
	private $identityClientId = null;
	
	public function __construct($table, $clientId) {
		
		if (!$clientId) {
			throw new Exception("Can't find client_id.");
		}
		
		parent::__construct($table);
		$this->_connection = $this->_dbClientData($clientId);	
		$this->identityClientId = (int)$clientId;
	}
	
	public function getIdentity()
	{
		$identity = new My_Model_Mapper_Mysql_IdentityObject();
		$identity->setClientId($this->identityClientId);
		return $identity;
	}
}