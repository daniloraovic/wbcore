<?php 

class My_Head_JavaScript extends My_Head_Basic
{
	
	protected function _doAppend( $fileName )
	{
		$this->_view->headScript()->appendFile( $fileName );
		
		return $this;
	}
}