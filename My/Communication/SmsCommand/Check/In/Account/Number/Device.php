<?php
class My_Communication_SmsCommand_Check_In_Account_Number_Device extends My_Communication_SmsCommand_Check_In_Account_Number implements My_Communication_ISmsCommand{
	
	
	protected $_success = false;
	protected $_authorized = null;
	protected $_parameters = array();
	protected $_phoneNumber = null;
	
	public function authorize($parameters = array(), $phoneNumber = null) {
		if(is_null($this->_authorized) && !is_null($phoneNumber) && isset($parameters['account']) && 1 == count($parameters['account']) && isset($parameters['device']) && isset($parameters['number'])) {
			$this->_authorized = false;
			$this->_parameters = $parameters;
			$this->_phoneNumber = $phoneNumber;
			if(is_array($this->_parameters['number'])) {
				$this->_parameters['number'] = reset($this->_parameters['number']);
			}
			if(is_array($this->_parameters['account'])) {
				$this->_parameters['account'] = reset($this->_parameters['account']);
			}
			$idClient = $this->getIdUniqueCode();
			$accountPhoneNumberCollection = My_Model_Mapper_Mysql_AccountPhoneNumber::getByParams(array('phone_number' => $phoneNumber, 'id_account' => $idClient, 'status' => My_Model_Domain_AccountPhoneNumber::STATUS_ACTIVE, 'access_level' => My_Communication_SmsCommand::ACCESS_LEVEL_MAIN));
			if(1 === $accountPhoneNumberCollection->getTotal()) {
				$this->_authorized = true;
				return $this;
			}
			else {
				throw new \Exception("Authorization failed");
			}
			return $this;
		}
		throw new \Exception("Missing arguments");
	}
	public function run() {
		if(true !== $this->_authorized) {
			$this->authorize();
		}
		$clientId = $this->getIdUniqueCode();

		$authorizedAccountPhoneNumberCollection = My_Model_Mapper_Mysql_PhoneNumber::getByParams(array(
				'id_client' => $clientId,
				'phone_number' => "+".$this->_parameters['number'], 
				'status' =>  My_Model_Domain_PhoneNumber::STATUS_ACTIVE
		));		

		if(0 === $authorizedAccountPhoneNumberCollection->getTotal()) {
			$this->_success = false;
			return $this->getMessage();
		}
		//get phone number id
		$idPhoneNumber = $authorizedAccountPhoneNumberCollection->current()->getId();

		//get device id 
		$deviceCodeArray = $this->_parameters['device'];
		$deviceCode = current($deviceCodeArray);
		
 		$deviceCollection = My_Model_Mapper_Mysql_Device::getByParams(array(
 				'id_client' => $clientId,
 				'device_code' => $deviceCode, 
 				'flag' =>  My_Model_Domain_Device::STATUS_ACTIVE
 		));
 		$idDevice = $deviceCollection->current()->getId();

 		//check if there is existig combination in device phone no		
 		$maximumPhoneNumbers = My_Model_Mapper_Mysql_ClientSettings::getMaximumPhoneNumbers(array(
 				'id_client' => $clientId
 		));

 		$devicePhoneNumberCollection =  My_Model_Mapper_Mysql_DevicePhoneNo::getByParams(array(
 				'id_device' => $idDevice,
 				'status' =>  My_Model_Domain_DevicePhoneNo::STATUS_ACTIVE
 		));

 		if ($maximumPhoneNumbers->current()->getValue() >= $devicePhoneNumberCollection->getTotal()){

 		$newDevicePhoneNumberCollection =  My_Model_Mapper_Mysql_DevicePhoneNo::getByParams(array(		
 				'id_device' => $deviceCollection->current()->getId(),
 				'id_phone_number' => $authorizedAccountPhoneNumberCollection->current()->getId(), 
 				'status' =>  My_Model_Domain_DevicePhoneNo::STATUS_ACTIVE
 		));
		if(0 === $newDevicePhoneNumberCollection->getTotal()) {
			$accountDevice = new My_Model_Domain_DevicePhoneNo();
			$accountDevice->setIdDevice($idDevice)
			                   ->setIdPhoneNumber($idPhoneNumber)
			                   ->setPriority($devicePhoneNumberCollection->getTotal()+1)
			                   ->setStatus(My_Model_Domain_DevicePhoneNo::STATUS_ACTIVE)
			                   ->markNew();
			$result = My_Model_Watcher::commit();
			if($accountDevice->getId()) {
				$this->_success = true;
				}
			return $this->getMessage();
			}
 		}
		else {
			$this->_success = false;
			return $this->getMessage();
		}
		throw new \Exception("Error running command");
	}
	
	public function getMessage() {
		if(true === $this->_success) {
			return "Phone Number " . $this->_parameters['number'] . " added to the Device " . current($this->_parameters['device']);
		}
		else {
			return "Error checking Phone Number " . $this->_parameters['number'] . " to the Device " . current($this->_parameters['device']);
		}
	}
	public function getIdClient() {
		//TODO: multiple clients per account
		$idClient = $this->getIdUniqueCode();
		if(true === $this->_success) {
			$accountClientCollection = My_Model_Mapper_Mysql_AccountClient::getByAccountId($idClient);
			if(1 <= $accountClientCollection->getTotal()) {
				return $accountClientCollection->current()->getCompoundId('id_client');
			}
		}
		return 0;
	}
		
	public function getIdUniqueCode(){
		$clientArray = My_Model_Mapper_Mysql_Client::getByParams(array('unique_code' => $this->_parameters['account']));
		$idClient = $clientArray->current()->getId();
		if($idClient !== 0){
			return $idClient;
		}else {
			throw new \Exception("Wrong client number");
		}
	}
	
}