<?php
class My_Model_Mapper_MainStorage_SelectionFactory
{
	public function setIdDevice( $value ) {
		
	}

	public function orderBy( My_Model_Mapper_IdentityObject $obj = null ) {
		
		if ( is_null($obj) ) {
			
			return array();
			
		}
		
		$result = array();
		
		foreach ( $obj->getOrderBy() as $key => $value) {
			
			$result[] = $key . (strtolower($value) == 'desc' ? ' DESC' : ' ASC');
			
		}
	
		return $result;
	}

	public function limit( My_Model_Mapper_IdentityObject $obj = null ) {

		if ( is_null($obj) ) {
			
			return '';
			
		}
		
		$result = $obj->getLimit();

		return $result;
	}
	
	public function offset( My_Model_Mapper_IdentityObject $obj = null ) {
		
		if ( is_null($obj) || $obj->isVoid() ) {
			
			return 0;
			
		}

		return $obj->getOffset();
	}
}