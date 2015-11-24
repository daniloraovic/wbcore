<?php
class My_Model_Mapper_Mysql_User extends My_Model_Mapper_Mysql_ClientData_Abstract
{
	public function __construct($clientId) {
		parent::__construct("user", $clientId);
	}
	
	protected function _targetClass () 
	{
		return 'My_Model_Factory_Domain_User_Mysql';
	}

}