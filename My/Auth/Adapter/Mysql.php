<?php

require_once 'Zend/Auth/Adapter/Interface.php';

require_once 'Zend/Db/Adapter/Abstract.php';

require_once 'Zend/Auth/Result.php';


class My_Auth_Adapter_Mysql implements Zend_Auth_Adapter_Interface {
	
	protected $_db = null;
	protected $_tableName = null;
	protected $_identity = null;
	protected $_credential = null;
	protected $_cookie_identity = null;
	protected $_resultRow = null;
	protected $_authenticateResultInfo = null;
	
	public function __construct($db = null, $tableName = null, $identity = null, $credential = null, $cookie_identity = null) {
		
		$this->_setDbAdapter($db);
		
		if (null !== $tableName){
			$this->setTablename($tableName);
		}
		
		if (null !== $cookie_identity) {

			$this->setCookieIdentity($cookie_identity);			
		} else {
			if (null !== $identity) {
				$this->setIdentity($identity);
			}
		
			if (null !== $credential) {
				$this->setCredential($credential);
			}	
		}		
	}
	
	public function setCredentialTreatment($credentialTreatment = array()) {
		$this->_credentialTreatment = $credentialTreatment;
		return $this;
	}
	
	public function setTableName($tableName)
	{
		$this->_tableName = $tableName;
	
		return $this;
	}
	
	public function setIdentity( $identity )
	{
		$this->_identity = $identity;
	
		return $this;
	}
	
	public function setCredential($credential)
	{
		$this->_credential = $credential;
	
		return $this;
	}
	
	public function setCookieIdentity($cookie_identity)
	{
		$this->_cookie_identity = $cookie_identity;
		
		return $this;
	}
	
	public function authenticate()
	{
		$this->_authenticateSetup();
		return $this->_authenticateValidateResult($this->_authenticateFind());
	}
	
	protected function _authenticateValidateResult($resultIdentity)
	{
		
		if ( empty($resultIdentity) ) {
			$this->_authenticateResultInfo['code'] = Zend_Auth_Result::FAILURE_CREDENTIAL_INVALID;
			$this->_authenticateResultInfo['messages'][] = 'Wrong username / password combination.';
			return $this->_authenticateCreateAuthResult();
		}
	
		$this->_resultRow = current($resultIdentity);
		if ( "1" === $this->_resultRow['blocked'])
		{
			$this->_authenticateResultInfo['code'] = Zend_Auth_Result::FAILURE;
			$this->_authenticateResultInfo['messages'][] = 'User blocked';
			return $this->_authenticateCreateAuthResult();
				
		}
		
		$this->_authenticateResultInfo['code'] = Zend_Auth_Result::SUCCESS;
		$this->_authenticateResultInfo['messages'][] = 'Authentication successful.';
		return $this->_authenticateCreateAuthResult();
	
// 		if (My_Model_Domain_Account::ACCOUNT_ACTIVE != $this->_resultRow['status']){
// 			$this->_authenticateResultInfo['code'] = Zend_Auth_Result::FAILURE;
// 			$this->_authenticateResultInfo['messages'][] = 'User is inactive';
// 			return $this->_authenticateCreateAuthResult();
// 		} 
				
// 		$this->_authenticateResultInfo['code'] = Zend_Auth_Result::SUCCESS;
// 		$this->_authenticateResultInfo['identity'] = array(
// 			'id' => $resultIdentity[0]['id'],
// 			'username' => $resultIdentity[0]['username'],
// 			'status' => $resultIdentity[0]['status'],
// 			'cookie' => $resultIdentity[0]['cookie'],
// 		);
// 		$this->_authenticateResultInfo['messages'][] = 'Authentication successful.';
// 		return $this->_authenticateCreateAuthResult();
	}
	
	protected function _authenticateSetup()
	{
		$exception = null;
	
		if ($this->_db == '') {
			$exception = 'A db name must be supplied for the authentication adapter.';
		} elseif($this->_cookie_identity == ''){
			if ($this->_tableName == '') {
				$exception = 'An tablename must be supplied for the authentication adapter.';
			} elseif ($this->_identity == '') {
				$exception = 'An identity key must be supplied for the authentication adapter.';
			} elseif ($this->_credential == '') {
				$exception = 'A credential key must be supplied for the authentication adapter.';
			}
		}
	
		if (null !== $exception) {
			/**
			 * @see Zend_Auth_Adapter_Exception
			 */
			require_once 'Zend/Auth/Adapter/Exception.php';
			throw new Zend_Auth_Adapter_Exception($exception);
		}

		$this->_authenticateResultInfo = array(
				'code'     => Zend_Auth_Result::FAILURE,
				'identity' => $this->_identity,
				'messages' => array()
		);
		
		return true;
	}
	
	protected function _authenticateFind()
	{
		$db = $this->_db;
		$select = $db->select();
		$select->from('user');
		$credential = $this->_credential;
		$identity = $this->_identity;
		if(!empty($this->_credentialTreatment)) {
			$funcName = empty($this->_credentialTreatment['func(?)']) ? null : $this->_credentialTreatment['func(?)'];
			if(!empty($funcName)) {
				$credential = hash($funcName,  $this->_credential);				
			}
		}
		if (null == $this->_cookie_identity) {
			$select->where("username = ?", trim($identity))
				   ->where('password = ?', trim($credential));
		} else {
			$select->where('cookie = ?', trim($this->_cookie_identity));
		}
		return $db->fetchAll($select);
	}
	
	protected function _setDbAdapter($config) {
		
		if (empty($config) ) {
			throw new Exception('no mysql db config');
		}
		
		$dbFactory = new My_Model_Factory_Db_Mysql();		
		$this->_db = $dbFactory->setHost($config->host)
								->setPort($config->port)
								->setUsername($config->username)
								->setPassword($config->password)
								->setDbname($config->dbname)
								->getConnection();
		
		return $this;
	}
	
	protected function _authenticateCreateAuthResult()
	{
		return new Zend_Auth_Result(
			$this->_authenticateResultInfo['code'],
			$this->_authenticateResultInfo['identity'],
			$this->_authenticateResultInfo['messages']
		);
	}
	
	public function getResultRowObject($returnColumns = null, $omitColumns = null)
	{
		if (!$this->_resultRow) {
			return false;
		}
		
		$returnObject = new stdClass();
		if (null !== $returnColumns) {
			$availableColumns = array_keys($this->_resultRow);			
			foreach ( (array) $returnColumns as $returnColumn) {
				if (in_array($returnColumn, $availableColumns)) {
					$returnObject->{$returnColumn} = $this->_resultRow[$returnColumn];
				}
			}
			return $returnObject;
		} elseif (null !== $omitColumns) {
			$omitColumns = (array) $omitColumns;
			foreach ($this->_resultRow as $resultColumn => $resultValue) {
				if (!in_array($resultColumn, $omitColumns)) {
					$returnObject->{$resultColumn} = $resultValue;
				}
			}
			return $returnObject;
		} else {
			foreach ($this->_resultRow as $resultColumn => $resultValue) {
				$returnObject->{$resultColumn} = $resultValue;
			}
			return $returnObject;
		}		
	}
}