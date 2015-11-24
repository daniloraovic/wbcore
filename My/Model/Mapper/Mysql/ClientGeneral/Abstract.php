<?php
abstract class My_Model_Mapper_Mysql_ClientGeneral_Abstract extends My_Model_Mapper_Mysql {
	
	public function __construct($tablename) {
		parent::__construct($tablename);
		$this->_connection = $this->_dbClient();
	}
}