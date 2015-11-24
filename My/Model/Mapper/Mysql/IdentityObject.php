<?php
class My_Model_Mapper_Mysql_IdentityObject extends My_Model_Mapper_IdentityObject
{
	// add an equality operator to the current field
	// ie 'age' becomes age=40
	// returns a reference to the current object (via operator())
	public function eq( $value ) {
		
		return $this->_operator( "=", $value );
	
	}
	
	// add an equality operator to the current field
	// ie 'age' becomes age=40
	// returns a reference to the current object (via operator())
	public function eqBin( $value ) {
		
		return $this->_operator( "= BINARY", $value );
	
	}
	public function isNull() {
	
		return $this->_operator( "IS NULL");
	
	}

	public function isNotNull() {
	
		return $this->_operator( "IS NOT NULL");
	
	}
	
	public function ne( $value ) {
		
		return $this->_operator( "<>", $value );
	
	}	
	
	// less than
	public function lt( $value ) {
		
		return $this->_operator( "<", $value );
		
	}

	// less than or equal
	public function le( $value ) {
		
		return $this->_operator( "<=", $value );
		
	}	
	
	// greater than
	public function gt( $value ) {
		
		return $this->_operator( ">", $value );
		
	}

	// greater than or equal
	public function ge( $value ) {
		
		return $this->_operator( ">=", $value );
		
	}	
	
	// IN list
	public function in( $value ) {
		
		return $this->_operator( 'IN', '(\'' . join('\',\'', $value) . '\')' );
		
	}
	
	public function nin( $value ) {
	
		return $this->_operator( "NOT IN", '(\'' . join('\',\'', $value) . '\')' );
	
	}
}