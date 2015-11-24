<?php
class My_Model_Domain_UserGroup extends My_Model_Domain {
	
	public function __construct($id = null) {
		$this->_client_id_unset_from_data = true;
		parent::__construct($id);
	}
	
	public function getMongoId() {
		
		return $this->_id;
		
	}
	
}