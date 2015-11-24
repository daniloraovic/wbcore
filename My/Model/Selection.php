<?php
//@todo deprecated ?
class My_Model_Selection
{
	protected $_where = array();
	
	public function init()
	{
		
	}
	
	public function __call($method, $args)
	{

		$methodType = substr($method, 0, 3);
		
		$methodName = substr($method, 3);

        preg_match_all("~[A-Z][a-z]+~", $methodName, $params);

        $params = array_map("strtolower", $params[0]);

		$paramName = '_' . implode('_', $params);

		if (in_array($paramName, array_keys(get_object_vars($this)))) {
			switch ($methodType) {
				case 'set' :
					$this->$paramName = $args[0];
					return $this;
				case 'get' : 
					return $this->$paramName;
			}
		}

		return $this;
	}	
}