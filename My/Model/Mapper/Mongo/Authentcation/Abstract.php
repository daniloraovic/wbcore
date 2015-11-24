<?php
abstract class My_Model_Mapper_Mongo_Authentcation_Abstract extends My_Model_Mapper_Mongo {

	public function __construct($collection) {
		parent::__construct($collection);
		$this->_connection = $this->_dbAuthentcation();
	}
}