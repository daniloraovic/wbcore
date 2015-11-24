<?php
class My_Model_Mapper_Mongo_UserGroup extends My_Model_Mapper_Mongo_ClientData_Abstract
{
	public function __construct($clientId) {
		$this->_key = "group_id";
		parent::__construct("user_group", $clientId);
	}
	
	public static function findById($clientId = null, $userGroupId = null) {
		
		$mapper = new self($clientId);
		$identity = $mapper->getIdentity();
		$identity->field('group_id')
				 ->eq($userGroupId);
		
		return $mapper->findAll($identity);
		
	}
	
	protected function _createObject( array $data )
	{
		$targetClass = $this->_targetClass();

		$domainFactory = new $targetClass;
		$obj = $domainFactory->createObject($data);

		$this->_addToMap($obj);
		return $obj;
	}
	
	public function removeFromClientIds($obj) {
		if ($obj instanceof My_Model_Domain) {
			$data = $obj->getData();
		} elseif (is_array($obj)) {
			$data = $obj;
		} else {
			throw new Exception("Unsupported datatype used in update", -1001);
		}
	
		$collection = $this->_connection->{$this->_collection};
	
		return $collection->update(array(), array( '$pull' => $data["pull"] ), array('multiple' => true));
	}

	protected function _targetClass ()
	{
		return 'My_Model_Factory_Domain_UserGroup_Mongo';
	}

	public function update($obj)
	{
		$collection = $this->_connection->{'user_group'};

		$data = $obj->getData();

		unset($data['client_id']);
		
		$result = $collection->update(array('group_id' => $data['group_id']), array( '$set' => $data ), array( 'upsert' => true ));		

		return $result;
	}
}
