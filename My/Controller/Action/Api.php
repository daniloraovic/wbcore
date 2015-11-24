<?php 

class My_Controller_Action_Api extends Zend_Controller_Action
{
	
	public function init()
	{		
		$this->_helper->layout->disableLayout();	
		$this->_helper->viewRenderer->setNoRender();

		$this->_helper->contextSwitch()
	         ->addActionContext('index', array('xml', 'json'))
	         ->initContext();
	}
	
}