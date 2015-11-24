<?php
class My_Model_Mapper_Mongo_SelectionFactory
{
	public function where( My_Model_Mapper_IdentityObject $obj = null) {
		
		if ( is_null($obj) || $obj->isVoid() ) {
			
			return array();
			
		}
		
		$where = array();
		
		foreach ( $obj->getComps() as $comp ) {
			
			switch ($comp['operator']) {
				case '=' :
					
					$where[$comp['name']] = $comp['value'];
					
					break;
					
				case '$in' :
					
					if (isset($where[$comp['name']])) {
						
						$where[$comp['name']][$comp['operator']][] = $comp['value']; 
			
					} else {
						
						$where[$comp['name']] = array($comp['operator'] => $comp['value']);
						
					}
					
					break;
					
				default :
					
					if (empty($where[$comp['name']])) {
					
						$where[$comp['name']] = array($comp['operator'] => $comp['value']);
						
					} else {
						
						$where[$comp['name']][$comp['operator']] = $comp['value'];
						
					}					
					break;
			}
			
		}

		return $where;
	}
	
	public function orderBy( My_Model_Mapper_IdentityObject $obj = null ) {
		
		if ( is_null($obj) ) {
			
			return array();
			
		}
		
		$result = array();
		
		foreach ( $obj->getOrderBy() as $key => $value) {
			
			$result[$key] = (int) (strtolower($value) == 'desc' ? '-1' : '1');
			
		}
	
		return $result;
	}
	
	public function limit( My_Model_Mapper_IdentityObject $obj = null ) {

		if ( is_null($obj) ) {
			
			return 0;
			
		}

		return $obj->getLimit() != '' ? $obj->getLimit() : 0;
	}
	
	public function offset( My_Model_Mapper_IdentityObject $obj = null ) {

		if ( is_null($obj) ) {
			
			return 0;
			
		}

		return $obj->getOffset() ? $obj->getOffset() : 0;
	}
}