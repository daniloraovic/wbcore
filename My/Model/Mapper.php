<?php
class My_Model_Mapper
{
	
	protected $_dbConfiguration;
	
	
	public function __construct()
	{
		$this->_dbConfiguration = Zend_Registry::get('dbConfiguration');	
	}
	
	
	public function findOne( My_Model_Mapper_IdentityObject $identity )
	{
		//@todo napraviti get metode i proveriti da li moze u !empty ovako 
		/*
		if( !is_null($selection) && !empty($selection->getId()) ) {
			
			$old = $this->_getFromMap($selection->getId());
			
			if(!is_null($old)) {
				return $old;
			}
		}
		*/

		$dataArray = $this->_selectOne($identity);
		$obj = null;

		if( !empty($dataArray) ) {

			$obj = $this->_factoryClassInstance()->createObject($dataArray);
		}
		
		return $obj;
		
	}
	

	public function findAll( My_Model_Mapper_IdentityObject $identity = null )
	{

		return new My_Model_Collection( $this->_selectAll($identity), $this->_factoryClassInstance() );
	}
		
	
	protected function _addToMap( My_Model_Domain $obj )
	{
		return My_Model_Watcher::add($obj);
	}
	
	private function _factoryClassInstance()
	{

		$factoryClass = $this->_targetClass();	
		return new $factoryClass();
	}
	
	private function _getFromMap( $id )
	{
		return My_Model_Watcher::exists($this->targetClass(), $id);
	}
	
	//my model mapper
	public function numberOf( My_Model_Mapper_IdentityObject $identity )
	{

		$dataArray = $this->_countAll($identity);
		$obj = null;

		if( !empty($dataArray) ) {			
			$obj = $this->_factoryClassInstance()->createObject($dataArray);
		}
		
		return $obj;
	}
	
}