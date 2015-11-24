<?php
class My_Model_Mapper_IdentityObject
{
	protected $_customContainer = array();
	protected $_currentfield = null;
	protected $_fields = array();
	
	protected $_orderBy = array();
	protected $_limit = '';
	protected $_offset = '';
	protected $_group = '';
		
	private $__enforce = array();
	
	// an identity object can start off empty, or with a field
	public function __construct( $field=null, array $enforce=null ) {
		
		if ( !is_null($enforce) ) {
			
			$this->__enforce = $enforce;
			
		}
		if ( !is_null($field) ) {
			
			$this->field($field);
			
		}
	}
	
	// used to set and get custom run-time properties
	public function __call($name, $args)
	{
		$method = array();

		preg_match('~([a-z]+)(.*)~', $name, $method);

		switch ($method[1]) {
			case 'set' :
				$this->_customContainer[$method[2]] = $args[0];
				return $this;
				break;
			case 'get' :
				return isset($this->_customContainer[$method[2]]) ? $this->_customContainer[$method[2]] : null;
				break;
		}		
	}
	
	// field names to which this is constrained
	public function getObjectFields() {
		
		return $this->__enforce;
		
	}
	
	
	// this method returns a reference to the current object
	// allowing for fluent syntax
	public function field($fieldname) {
		
		if ( !$this->isVoid() && $this->_currentfield->isIncomplete() ) {
			
			throw new Exception("Incomplete field");
		}
		
		$this->enforceField($fieldname);
		
		if ( isset($this->_fields[$fieldname]) ) {
			
			$this->_currentfield=$this->_fields[$fieldname];
			
		} else {
			
			$this->_currentfield = new My_Model_Mapper_Field($fieldname);
			
			$this->_fields[$fieldname]=$this->_currentfield;
			
		}
		return $this;
	}
	
	// does the identity object have any fields yet
	public function isVoid() {
		
		return empty($this->_fields);
	}
	
	// is the given fieldname legal?
	public function enforceField($fieldname) {
		
		if ( !in_array($fieldname, $this->__enforce) && !empty($this->__enforce)) {
			
			$forcelist = implode(', ', $this->__enforce);
			
			throw new Exception("{$fieldname} not a legal field ($forcelist)");
		}
	}
	
	// does the work for the operator methods
	// gets the current field and adds the operator and test value
	// to it
	protected function _operator($symbol, $value = null) {
		
		if ($this->isVoid()) {
			
			throw new Exception("no object field defined");
		}
		
		$this->_currentfield->add($symbol, $value);
		
		return $this;
	}
	
	// return all comparisons built up so far in an associative array
	public function getComps() {
		
		$ret = array();
		
		foreach ($this->_fields as $key => $field) {
			
			$ret = array_merge($ret, $field->getComps());
		}
		return $ret;
	}
	
	public function getOrderBy() {
		
		return $this->_orderBy;
		
	}
	
	public function setOrderBy($param, $value) {
		
		$this->_orderBy[$param] = $value;
		
	}
	
	public function setLimit($value) {
		
		$this->_limit = $value;
		
	}
	
	public function getLimit() {
		
		return $this->_limit;
	}
	
	public function getOffset() {
		
		return $this->_offset;
	}
	
	public function setOffset($value) {
		
		$this->_offset = $value;
		
	}
	
	public function setGroup($value) {
		
		$this->_group = $value;
	}
	
	public function getGroup() {
		return $this->_group;
	}
}