<?php 

/**
 * @see Zend_Auth_Adapter_Interface
 */
require_once 'Zend/Auth/Adapter/Interface.php';

/**
 * @see Zend_Db_Adapter_Abstract
 */
require_once 'Zend/Db/Adapter/Abstract.php';

/**
 * @see Zend_Auth_Result
 */
require_once 'Zend/Auth/Result.php';


/**
 * @category   My
 * @package    My_Auth
 * @subpackage Adapter
 * @copyright  Copyright (c) 2011 Mrežni sistemi (http://www.mreznisistemi.rs)
 * @license    
 */
class My_Auth_Adapter_Mongo implements Zend_Auth_Adapter_Interface
{
	
	protected $_db = null;
	
	protected $_dbName = null;
	
	protected $_collectionName = null;
	
	protected $_identityKey = null;
	
	protected $_credentialKey = null;
	
	protected $_credentialTreatment = null;
	
	protected $_identity = null;
	
	protected $_credential = null;
	
	protected $_resultRow = null;
	
	protected $_authenticateResultInfo = null;
	
	
 	public function __construct( $db = null, $dbName = null, $collectionName = null, $identityKey = null, 
 								$credentialKey = null, $credentialTreatment = null)
    {
        $this->_setDbAdapter($db);

        if (null !== $dbName) {
            $this->setDbName($dbName);
        }
        
     	if (null !== $collectionName) {
            $this->setCollectionName($collectionName);
        }

        if (null !== $identityKey) {
            $this->setIdentityKey($identityKey);
        }

        if (null !== $credentialKey) {
            $this->setCredentialKey($credentialKey);
        }

        if (null !== $credentialTreatment) {
            $this->setCredentialTreatment($credentialTreatment);
        }
    }
	
    
    public function setDbName( $dbName )
    {
    	$this->_dbName = $dbName;

    	return $this;
    }
    
    
 	public function setCollectionName( $collectionName )
    {
    	$this->_collectionName = $collectionName;

    	return $this;
    }
    
    
 	public function setIdentityKey( $identityKey )
    {
    	$this->_identityKey = $identityKey;

    	return $this;
    }

    
 	public function setCredentialKey( $credentialKey )
    {
    	$this->_credentialKey = $credentialKey;

    	return $this;
    }

    
	public function setCredentialTreatment( $credentialTreatment )
    {
    	$this->_credentialTreatment = $credentialTreatment;

    	return $this;
    }
    
    
	public function setIdentity( $value )
    {
        $this->_identity = $value;
        
        return $this;
    }

    
    public function setCredential( $credential )
    {
        $this->_credential = $credential;
        
        return $this;
    }
    
    
	/**
     * setAmbiguityIdentity() - sets a flag for usage of identical identities
     * with unique credentials. It accepts integers (0, 1) or boolean (true,
     * false) parameters. Default is false.
     *
     * @param  int|bool $flag
     * @return Zend_Auth_Adapter_DbTable
     */
    public function setAmbiguityIdentity($flag)
    {
        if (is_integer($flag)) {
            $this->_ambiguityIdentity = (1 === $flag ? true : false);
        } elseif (is_bool($flag)) {
            $this->_ambiguityIdentity = $flag;
        }
        return $this;
    }
    /**
     * getAmbiguityIdentity() - returns TRUE for usage of multiple identical
     * identies with different credentials, FALSE if not used.
     *
     * @return bool
     */
    public function getAmbiguityIdentity()
    {
        return $this->_ambiguityIdentity;
    }
    
    
 	/**
     * getResultRowObject() - Returns the result row as a stdClass object
     *
     * @param  string|array $returnColumns
     * @param  string|array $omitColumns
     * @return stdClass|boolean
     */
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
    
    
	public function authenticate()
	{
		$this->_authenticateSetup();		
		
		$resultIdentities = $this->_authenticateFind();
		
		$this->_authenticateValidateResultSet( $resultIdentities );

		$authResult = $this->_authenticateValidateResult( $resultIdentities );
		
        return $authResult;
	}
	
	
	protected function _authenticateSetup()
    {
        $exception = null;

        if ($this->_dbName == '') {
            $exception = 'A db name must be supplied for the My_Auth_Adapter_Mongo authentication adapter.';
        } elseif ($this->_collectionName == '') {
            $exception = 'An collection name must be supplied for the My_Auth_Adapter_Mongo authentication adapter.';    
        } elseif ($this->_identityKey == '') {
            $exception = 'An identity key must be supplied for the My_Auth_Adapter_Mongo authentication adapter.';
        } elseif ($this->_credentialKey == '') {
            $exception = 'A credential key must be supplied for the My_Auth_Adapter_Mongo authentication adapter.';
        } elseif ($this->_identity == '') {
            $exception = 'A value for the identity was not provided prior to authentication with My_Auth_Adapter_Mongo.';
        } elseif ($this->_credential === null) {
            $exception = 'A credential value was not provided prior to authentication with My_Auth_Adapter_Mongo.';
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
    	
    	try {
	    	$dbName = $this->_dbName;
			$collectionName = $this->_collectionName;
			
			$dataBase = $this->_db->$dbName;
			$collection = $dataBase->$collectionName;
			
			$credential = $this->_credential;
			if( !empty($this->_credentialTreatment) ) {
				$funcName = empty($this->_credentialTreatment['func(?)']) ? null : $this->_credentialTreatment['func(?)'];
				if( !empty($funcName) ) {
					$credential = call_user_func($funcName, $this->_credential);
				}
			}
	
			$resultIdentities = iterator_to_array($collection->find(array($this->_identityKey => $this->_identity, $this->_credentialKey => $credential, "active" => true)));
    	} catch ( Exception $e ) {
     		/**
             * @see Zend_Auth_Adapter_Exception
             */
            require_once 'Zend/Auth/Adapter/Exception.php';
            throw new Zend_Auth_Adapter_Exception('The supplied parameters to My_Auth_Adapter_Mongo failed to '
                                                . 'produce a valid mongo statement, please check db, collection and key names '
                                                . 'for validity.', 0, $e);
        }
        return $resultIdentities;
    			
    }
    
    
	protected function _authenticateValidateResultSet(array $resultIdentities)
    {

        if (count($resultIdentities) < 1) {
            $this->_authenticateResultInfo['code'] = Zend_Auth_Result::FAILURE_IDENTITY_NOT_FOUND;
            $this->_authenticateResultInfo['messages'][] = 'A record with the supplied identity could not be found.';
            return $this->_authenticateCreateAuthResult();
        } elseif (count($resultIdentities) > 1 && false === $this->getAmbiguityIdentity()) {
            $this->_authenticateResultInfo['code'] = Zend_Auth_Result::FAILURE_IDENTITY_AMBIGUOUS;
            $this->_authenticateResultInfo['messages'][] = 'More than one record matches the supplied identity.';
            return $this->_authenticateCreateAuthResult();
        }

        return true;
    }
    
	
	protected function _authenticateValidateResult($resultIdentity)
    {

        if ( empty($resultIdentity) ) {
            $this->_authenticateResultInfo['code'] = Zend_Auth_Result::FAILURE_CREDENTIAL_INVALID;
            $this->_authenticateResultInfo['messages'][] = 'Supplied credential is invalid.';
            return $this->_authenticateCreateAuthResult();
        }

        $this->_resultRow = current($resultIdentity);

        $this->_authenticateResultInfo['code'] = Zend_Auth_Result::SUCCESS;
        $this->_authenticateResultInfo['messages'][] = 'Authentication successful.';
        return $this->_authenticateCreateAuthResult();
    }
	
    
	protected function _authenticateCreateAuthResult()
    {
        return new Zend_Auth_Result(
            $this->_authenticateResultInfo['code'],
            $this->_authenticateResultInfo['identity'],
            $this->_authenticateResultInfo['messages']
            );
    }
	
		
	protected function _setDbAdapter( $db ) 
	{
		//@todo autorizacija za mongoDB
		$this->_db = new MongoClient(
				"mongodb://{$db->host}:{$db->port}", 
				array(
					'username' => $db->username,
					'password' => $db->password
				)
			);
			
		return $this;
	}
	
}