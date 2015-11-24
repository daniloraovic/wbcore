<?php
class My_Model_Mapper_Field
{
	protected $name=null;
//	protected $operator=null;
	protected $comps=array();
//	protected $incomplete=false;
	
	// sets up the field name (age, for example)
	function __construct($name) {
		
		$this->name = $name;
	}
	
	// add the operator and the value for the test
	// (> 40, for example) and add to the $comps property
	function add($operator, $value) {
		
		$this->comps[] = array('name' => $this->name, 'operator' => $operator, 'value' => $value);
	}
	
	// comps is an array so that we can test one field in more than one way
	function getComps() { 
		
		return $this->comps; 
	}
	
	// if $comps does not contain elements, then we have
	// comparison data and this field is not ready to be used in
	// a query
	function isIncomplete() { 

		return empty( $this->comps); 
	}
	
}