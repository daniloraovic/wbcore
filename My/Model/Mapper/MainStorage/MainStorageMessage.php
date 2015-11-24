<?php
class My_Model_Mapper_MainStorage_Message 
{
	protected function _selectAll( My_Model_Mapper_IdentityObject $identity )
	{
					
		$select->where($this->_getSelection()->where($identity));
		
		$select->order($this->_getSelection()->orderBy($identity));
		
		$select->limit($this->_getSelection()->limit($identity), $this->_getSelection()->offset($identity));
 
		$commonData = $dbClientData->fetchAll($select);

		return $commonData;
	}
	
	
	protected function _targetClass () 
	{
		return 'My_Model_Factory_Domain_Message_Mysql';
	}
}
