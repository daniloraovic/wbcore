<?php
class My_Communication_SmsCommand_Add_Account_Number_Name extends My_Communication_SmsCommand_Add_Account_Number implements My_Communication_ISmsCommand
{
	protected $_success = false;
	protected $_authorized = null;
	protected $_parameters = array();
	protected $_phoneNumber = null;
	public $clientArray = array();

	public function authorize($parameters = array(), $phoneNumber = null) {
		if(is_null($this->_authorized) && !is_null($phoneNumber) && isset($parameters['account']) && 1 == count($parameters['account']) && isset($parameters['name']) && isset($parameters['number'])) {
			$this->_authorized = false;
			$this->_parameters = $parameters;
			$this->_phoneNumber = $phoneNumber;
			if(is_array($this->_parameters['number'])) {
				$this->_parameters['number'] = reset($this->_parameters['number']);
			}
			if(is_array($this->_parameters['account'])) {
				$this->_parameters['account'] = reset($this->_parameters['account']);
			}
			$accountPhoneNumberCollection = My_Model_Mapper_Mysql_AccountPhoneNumber::getByParams(array('phone_number' => $phoneNumber, 'id_account' => $this->getIdUniqueCode(), 'status' => My_Model_Domain_AccountPhoneNumber::STATUS_ACTIVE, 'access_level' => My_Communication_SmsCommand::ACCESS_LEVEL_MAIN));
			
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
							'phone_number' => $this->_parameters['number'], 
							'id_client' => $clientId, 
							'status' =>  My_Model_Domain_PhoneNumber::STATUS_ACTIVE
					));
		
		if(0 !== $authorizedAccountPhoneNumberCollection->getTotal()) {
			$this->_success = false;
			return $this->getMessage();
		}
		$newAccountPhoneNumberCollection = My_Model_Mapper_Mysql_PhoneNumber::getByParams(array('phone_number' => $this->_parameters['number'], 'id_client' => $clientId));
		if(0 === $newAccountPhoneNumberCollection->getTotal()) {
			if(is_array($this->_parameters['name'])) {
				$this->_parameters['name'] = join(' ', $this->_parameters['name']);
			}
/* 		$authorizedAccountPhoneNumberCollection = My_Model_Mapper_Mysql_AccountPhoneNumber::getByParams(array('phone_number' => $this->_phoneNumber, 'id_account' => $this->_parameters['account'], 'status' => My_Communication_SmsCommand::ACCESS_LEVEL_MAIN));
		if(1 > $authorizedAccountPhoneNumberCollection->getTotal()) {
			$this->_success = false;
			return $this->getMessage();
		}
		$newAccountPhoneNumberCollection = My_Model_Mapper_Mysql_AccountPhoneNumber::getByParams(array('phone_number' => $this->_parameters['number'], 'id_account' => $this->_parameters['account']));
		if(0 === $newAccountPhoneNumberCollection->getTotal()) {
			if(is_array($this->_parameters['name'])) {
				$this->_parameters['name'] = join(' ', $this->_parameters['name']);
			}
			$accountPhoneNumber = new My_Model_Domain_AccountPhoneNumber();
			$accountPhoneNumber->setIdAccount($this->_parameters['account'])
							   ->setPhoneNumber($this->_parameters['number'])
							   ->setName($this->_parameters['name'])
							   ->setAccessLevel(My_Communication_SmsCommand::ACCESS_LEVEL_REGISTERED)
							   ->setStatus(My_Communication_SmsCommand::STATUS_ACTIVE)
							   ->markNew();*/
	
			$accountPhoneNumber = new My_Model_Domain_PhoneNumber();
			$accountPhoneNumber->setIdClient($clientId)
			                   ->setPhoneNumber("+".$this->_parameters['number'])
			                   ->setName($this->_parameters['name'])
			                   ->setStatus(My_Model_Domain_PhoneNumber::STATUS_ACTIVE)
			                   ->markNew();
			$result = My_Model_Watcher::commit();
			if($accountPhoneNumber->getId()) {
				$this->_success = true;
			}
			return $this->getMessage();
		}
		else {
			$this->_success = true;
			return $this->getMessage();
		}
		throw new \Exception("Error running command");
	}
	public function getMessage() {
		if(true === $this->_success) {
			return "Number " . $this->_parameters['number'] . " added to the Account ID " . $this->_parameters['account'];
		}
		else {
			return "Error adding number " . $this->_parameters['number'] . " to the Account ID " . $this->_parameters['account'];
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