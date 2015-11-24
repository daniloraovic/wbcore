<?php
class My_Model_Domain_UserGeneral extends My_Model_Domain
{
	public function __construct($id = null) {
		$this->_client_id_unset_from_data = false;
		parent::__construct($id);
	}
}