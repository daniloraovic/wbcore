<?php
class My_Model_Mapper_Mongo extends My_Model_Mapper
{
	
	protected $_clientId = null;
	
	/**
	 * table name mapper is mapping to
	 * @var string
	 */
	protected $_collection = null;
	
	/**
	 * table's primary key field name
	 * @var string
	 */
	protected $_key = 'id';
	
	/**
	 *
	 * @var Zend_Db_Adapter_Abstract
	 */
	protected $_connection;
	
	/**
	 *
	 * @var Zend_Db_Select
	 * @var unknown_type
	 */
	protected $_select = null;
	
	protected $_use_keys_as_index = true;
	protected $_upsert_key = null;
	protected $_upsert = false;
	
	public function __construct($collection = '') {
		$this->_collection = $collection;
		parent::__construct();
	}
	
	protected function _db( $config )
	{

		if( empty($config) ) {
			throw new Exception('no mongo db config');
		}
		
		$dbFactory = new My_Model_Factory_Db_Mongo();
				
		return $dbFactory->setHost($config->host)
				  		 ->setPort($config->port)
				  		 ->setUsername($config->username)
				  		 ->setPassword($config->password)
				  		 ->setDbname($config->dbname)
				  		 ->getConnection();
	}
	
	
	public function _dbAuthentcation()
	{
		return $this->_db($this->_dbConfiguration->mongo->authentication);
	}
	
	public function _dbRequestAuthentcation()
	{
		return $this->_db($this->_dbConfiguration->mongo->recoveryRequest->authentication);
	}
	
	
	public function _dbQueueHandler()
	{
		return $this->_db($this->_dbConfiguration->mongo->queueHandler);
	}
	
	
	public function _dbClientData( $clientId )
	{
		if( empty($clientId) ) {
			throw new Exception('no client id');
		}
	
		$client = 'client' . $clientId;

		return $this->_db($this->_dbConfiguration->mongo->clientData->$client);
	}
	
	public function _dbClientRequestData( $clientId )
	{
		if( empty($clientId) ) {
			throw new Exception('no client id');
		}
	
		$client = 'client' . $clientId;
		
		return $this->_db($this->_dbConfiguration->mongo->recoveryRequest->clientData->$client);
	}

	public function getIdentity()
	{
		return new My_Model_Mapper_Mongo_IdentityObject();
	}
	
	public function _getSelection()
	{
		return new My_Model_Mapper_Mongo_SelectionFactory();
	}	
	
	public function getVehiclesByDeviceId( $clientId ) {
		
		$vehicleMapper = new My_Model_Mapper_Mongo_Vehicle($clientId);
		
		$identityVehicle = $vehicleMapper->getIdentity();
		
		$allVehices = $vehicleMapper->findAll($identityVehicle)->getRawData();
		
		$vehicleDataByDevice = array();
		
		foreach ($allVehices as $vehicleData) {
			
			$idVehiclePointer = $vehicleData['_id'];
			$vehicleDataByDevice[$vehicleData['true_device_id']]['id'] = $idVehiclePointer->{'$id'};
			$vehicleDataByDevice[$vehicleData['true_device_id']]['device_id'] = $vehicleData['true_device_id'];
		}
		
		return $vehicleDataByDevice;
	}
	
	public function insert($obj) {
		
		if ($obj instanceof My_Model_Domain) {
			$data = $obj->getData();
			if($obj->getClientIdUnsetFromData()) {
				unset($data[$obj->getClientIdKey()]);
			}
		} elseif (is_array($obj)) {
			$data = $obj;
		} else {
			throw new Exception("Unsupported datatype used in insert", -1001);
		}
		
		
		if($this->_upsert) {
			return $this->update_upsert($obj);
		}
		
		$collection = $this->_connection->{$this->_collection};
		
		return $collection->insert($data);		
	}
	
	public function update_upsert($obj) {
		
		if ($obj instanceof My_Model_Domain) {
			$data = $obj->getData();
		} elseif (is_array($obj)) {
			$data = $obj;
		} else {
			throw new Exception("Unsupported datatype used in insert", -1001);
		}
				
		$collection = $this->_connection->{$this->_collection};
		return $collection->update(array( $this->_upsert_key => $data[$this->_upsert_key]), array( '$set' => $data ), array( 'upsert' => true ));
	}
	
	public function update($obj) {
		
		if ($obj instanceof My_Model_Domain) {
			$data = $obj->getData();
		} elseif (is_array($obj)) {
			$data = $obj;
		} else {
			throw new Exception("Unsupported datatype used in update", -1001);
		}
		
		if($obj->getClientIdUnsetFromData()) {
			unset($data[$obj->getClientIdKey()]);
		}
		
		$collection = $this->_connection->{$this->_collection};
				
		return $collection->update(array( $this->_key => $data[$this->_key]), array( '$set' => $data ));
	}
	
	public function delete($obj) {
		
		$collection = $this->_connection->{$this->_collection};
		
		if ($obj instanceof My_Model_Domain) {
			$data = $obj->getData();
		} elseif (is_array($obj)) {
			$data = $obj;
		} elseif ($obj instanceof My_Model_Mapper_Mongo_IdentityObject) {
			return $collection->remove($this->_getSelection()->where($obj));
		} else {
			throw new Exception("Unsupported datatype used in delete", -1001);
		}
		
		
		return $collection->remove(array($this->_key => $data[$this->_key]));
	}
	
	public function deleteAll(My_Model_Mapper_Mongo_IdentityObject $identity) {
		$collection = $this->_connection->{$this->_collection};	
		return $collection->remove($this->_getSelection()->where($identity));
	}
	
	protected function _selectAll( My_Model_Mapper_Mongo_IdentityObject $identity ) {
		$collection = $this->_connection->{$this->_collection};
		
		$cursor = $collection->find($this->_getSelection()->where($identity));

		$cursor->sort( $this->_getSelection()->orderBy($identity) );
		
		$cursor->limit( $this->_getSelection()->limit($identity) );
		
		$cursor->skip( $this->_getSelection()->offset($identity) );
		
		$result = iterator_to_array($cursor, $this->_use_keys_as_index);
		
		return $result;
	}
	
	protected function _selectOne( My_Model_Mapper_Mongo_IdentityObject $identity ) {
		
		$collection = $this->_connection->{$this->_collection};
		
		return $collection->findOne($this->_getSelection()->where($identity));
	}
	
	public function count( My_Model_Mapper_Mongo_IdentityObject $identity ) {
		
		$collection = $this->_connection->{$this->_collection};
		
		return $collection->count($this->_getSelection()->where($identity));
	}
	
	protected function _createObject( array $data )
	{	
		$domainFactory = new $this->_targetClass();
		$obj = $domainFactory->createObject($data);
	
		$this->_addToMap($obj);
		return $obj;
	}
}