<?php
class My_Model_Mapper_Mysql_ClientParent extends My_Model_Mapper_Mysql_ClientGeneral_Abstract
{	
	
	public function __construct() {
		parent::__construct('client_parent');
	}
	
	protected function _targetClass () 
	{
		return 'My_Model_Factory_Domain_ClientParent_Mysql';
	}


}