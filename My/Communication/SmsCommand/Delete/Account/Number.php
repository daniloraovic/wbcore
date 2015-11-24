<?php

class My_Communication_SmsCommand_Delete_Account_Number extends My_Communication_SmsCommand_Delete_Account {
	protected $_success = false;
	protected $_authorized = null;
	protected $_parameters = array();
	protected $_message = null;
	public function authorize($parameters = array(), $phoneNumber = null) {
		if(is_null($this->_authorized) && !is_null($phoneNumber) && isset($parameters['account']) && 1 == count($parameters['account']) && isset($parameters['name']) && isset($parameters['number'])) {
			$this->_authorized = false;
			$this->_parameters = $parameters;
			if(is_array($this->_parameters['number'])) {
				$this->_parameters['number'] = reset($this->_parameters['number']);
			}
			if(is_array($this->_parameters['account'])) {
				$this->_parameters['account'] = reset($this->_parameters['account']);
			}
			$accountPhoneNumberCollection = My_Model_Mapper_Mysql_AccountPhoneNumber::getByParams(array('phone_number' => $phoneNumber, 'id_account' => $this->_parameters['account'], 'status' => My_Model_Domain_AccountPhoneNumber::STATUS_ACTIVE, 'access_level' => My_Communication_SmsCommand::ACCESS_LEVEL_MAIN));
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
		//$accountPhoneNumberCollection = My_Model_Mapper_Mysql_PhoneNumber::getByParams(array('phone_number' => $this->_parameters['number'], 'id_client' => $this->_parameters['account']));
		$accountPhoneNumberCollection = My_Model_Mapper_Mysql_AccountPhoneNumber::getByParams(array('phone_number' => $this->_parameters['number'], 'id_account' => $this->_parameters['account']));
		if(1 === $accountPhoneNumberCollection->getTotal()) {
			$accountPhoneNumberCollection->current()->markDelete();
			$response = My_Model_Watcher::commit();
			if(true === !!$response['delete']['response']) {
				$this->_success = true;
				return $this->getMessage();
			}
			else {
				$this->_success = false;
				$this->_message = 1;
				return $this->getMessage();
			}
		}
		else {
			$this->_success = false;
			$this->_message = 2;
			return $this->getMessage();
		}
		throw new \Exception("Error running command");
	}
	public function getMessage() {
		if(true === $this->_success) {
			return "Number " . $this->_parameters['number'] . " removed from Account ID " . $this->_parameters['account'] . " succesfully";
		}
		else {
			if(1 === $this->_message) {
				return "Error removing number " . $this->_parameters['number'] . " from Account ID " . $this->_parameters['account'];
			}
			elseif(2 === $this->_message) {
				return "Error removing number " . $this->_parameters['number'] . " from Account ID " . $this->_parameters['account'] . " - not assigned";
			}
		}
	}
	public function getIdClient() {
		//TODO: multiple clients per account
		if(true === $this->_success) {
			$accountClientCollection = My_Model_Mapper_Mysql_AccountClient::getByAccountId($this->_parameters['account']);
			if(1 <= $accountClientCollection->getTotal()) {
				return $accountClientCollection->current()->getCompoundId('id_client');
			}
		}
		return 0;
	}
}