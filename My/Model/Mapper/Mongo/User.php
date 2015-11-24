<?php
class My_Model_Mapper_Mongo_User extends My_Model_Mapper_Mongo_Authentcation_Abstract
{
	public function __construct() {
		$this->_key = "user_id";
		parent::__construct('user');
	}

	protected function _targetClass ()
	{
		return 'My_Model_Factory_Domain_User_Mongo';
	}
	
	protected function _selectAll( My_Model_Mapper_Mongo_IdentityObject $identity )
	{
		$collection = $this->_dbAuthentcation()->user;
		
		//default identity (return only active user)
		$identity->field('active')->ne(false);
		
		$result = iterator_to_array($collection->find($this->_getSelection()->where($identity)), false);
	
		return $result;
	}

	protected function _selectOne( My_Model_Mapper_Mongo_IdentityObject $identity = null )
	{
		$collection = $this->_dbAuthentcation()->user;
		$identity->field('active')->ne(false);
		return $collection->findOne($this->_getSelection()->where($identity));						
	}
	
}
