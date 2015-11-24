<?php
class My_Model_Mapper_Mysql_SelectionFactory
{
	public function where( My_Model_Mapper_IdentityObject $obj ) {
		
		if ( $obj->isVoid() ) {

			return '1';
			
		}
		
		$compstrings = array();
		
		foreach ( $obj->getComps() as $comp ) {
			if ( $comp['operator'] == 'IS NULL' || $comp['operator'] == 'IS NOT NULL'  ) {
				$compstrings[] = "{$comp['name']} {$comp['operator']}";
			} else if ($comp['operator'] != 'IN' && $comp['operator'] != 'NOT IN') {
				$compstrings[] = "{$comp['name']} {$comp['operator']} '{$comp['value']}'";
			} else {
				$compstrings[] = "{$comp['name']} {$comp['operator']} {$comp['value']}";
			}
		}
		
		$where = implode( " AND ", $compstrings );
		
		return $where;
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
	
	public function group( My_Model_Mapper_IdentityObject $obj = null ) {
		
		if( is_null($obj) || $obj->isVoid() ) {
			
			return '';
		}
		
		return $obj->getGroup();
	}
}