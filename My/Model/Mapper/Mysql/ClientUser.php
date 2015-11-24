<?php
class My_Model_Mapper_Mysql_ClientUser extends My_Model_Mapper_Mysql_ClientGeneral_Abstract
{
	public function __construct() {
		parent::__construct('user');
	}
		
	
	public function update( $obj )
	{
		$dbClient = $this->_connection;

		return $dbClient->update( $this->_tablename, array( 'username' => $obj->getUsername() ), "id={$obj->getId()}" );
	}
	
// 	protected function _selectOne( My_Model_Mapper_IdentityObject $identity )
// 	{
// 		$dbClient = $this->_dbClient(); 
		
// 		$select = $dbClient->select();
		
// 		$select->from('user');

// 		$select->where($this->_getSelection()->where($identity));

// 		$commonData = $dbClient->fetchRow($select);

// 		return $commonData;
// 	}	
	
	protected function _targetClass () 
	{
		return 'My_Model_Factory_Domain_ClientUser_Mysql';
	}

}