<?php 

class My_Controller_Response_Data extends Zend_Controller_Response_Abstract 
{
	
	protected $_data;
	
	
	public function getData()
	{
		return $this->_data;
	}
	
	
	public function setData( $data )
	{
		$this->_data = $data;
		
		return $this;
	}
	
	
	
}