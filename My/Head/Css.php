<?php 

class My_Head_Css extends My_Head_Basic
{	
	
	protected function _doAppend( $fileName )
	{
		$this->_view->headLink()->appendStylesheet( $fileName );
		
		return $this;
	}
}