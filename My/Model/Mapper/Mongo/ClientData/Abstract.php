<?php
abstract class My_Model_Mapper_Mongo_ClientData_Abstract extends My_Model_Mapper_Mongo {

	private $identityClientId = null;
	
	public function __construct($collection, $clientId = null) {
		
		if (!$clientId) {
			throw new Exception("Can't find client_id.");
		}
		
		parent::__construct($collection);
		$this->_connection = $this->_dbClientData( (int)$clientId );
		$this->identityClientId = (int)$clientId;		
	}
	
	public function getIdentity()
	{
		$identity = new My_Model_Mapper_Mongo_IdentityObject();
		$identity->setClientId($this->identityClientId); 
		return $identity;
	}
}