<?php
class My_Model_Mapper_MainStorage_Message 
{
	public function findAll( My_Model_Mapper_IdentityObject $identity )
	{
		$clientId = $identity->getClientId();
		
		$resultArr = array();
		
		$identity->setOrderBy('datetime_triggered', 'ASC');
		
		$resultArr = $this->_findMysqlMessage( $identity );
		
		if ( $this->_clientHasCassandraPositional( $identity ) ) {
			
			$resultArr = array_merge( $resultArr, $this->_findCassandraPositionalMessage( $identity ) );
			
			ksort( $resultArr );
		} 
		
		return new My_Model_Collection($resultArr, new My_Model_Factory_Domain_Message_Cassandra);
	}

	public function getIdentity()
	{
		return new My_Model_Mapper_MainStorage_IdentityObject();
	}
	
	protected function _getSelection()
	{
		return new My_Model_Mapper_MainStorage_SelectionFactory();
	}
	
	private function _clientHasCassandraPositional( My_Model_Mapper_IdentityObject $identity )
	{
		$clientId = $identity->getClientId();
		
		$dbConfig = Zend_Registry::get('dbConfiguration');
		
		if ( is_null( $dbConfig->cassandra->messagePositional->{"client$clientId"} ) ) {
			
			return false;
		} else {
			
			return true;
		} 
	}

	private function _findMysqlMessage( My_Model_Mapper_IdentityObject $identity )
	{
		$mapperMysql = new My_Model_Mapper_Mysql_Message();
		
		$mysqlCollection = $mapperMysql->findAll( $identity );

		$result = array();
		
		foreach ($mysqlCollection as $value) {
			
			$result[$value->getDatetimeTriggered()] = $value->getData();

		}

		return $result;
	}

	private function _findCassandraPositionalMessage( My_Model_Mapper_IdentityObject $identity )
	{
		$cassandraMapper = new My_Model_Mapper_CassandraPositional_Message();
	
		$cassandraCollection = $cassandraMapper->findAll( $identity );
	
		$result = array();
		
		foreach($cassandraCollection as $value) {

			$result = array_merge( $result, $value->getData() );
		}

		return $result;
	}	
}
