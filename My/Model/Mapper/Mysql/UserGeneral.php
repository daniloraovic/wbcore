<?php
class My_Model_Mapper_Mysql_UserGeneral extends My_Model_Mapper_Mysql_ClientGeneral_Abstract
{
	public function __construct() {
		parent::__construct("user");
	}

	protected function _targetClass ()
	{
		return 'My_Model_Factory_Domain_UserGeneral_Mysql';
	}

}