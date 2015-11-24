<?php
class My_Model_Factory_Domain_Client_Mysql extends My_Model_Factory_Domain
{
		
	public function createObject(array $data)
	{
		
		$obj = new My_Model_Domain_Client( $data['id'] );

		unset($data['id']);

		$obj->setData($data);
		
		return $obj;
	}
	
}