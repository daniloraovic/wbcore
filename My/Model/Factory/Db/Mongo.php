<?php 

class My_Model_Factory_Db_Mongo extends My_Model_Factory_Db
{
	
	public function getConnection()
	{
		try {
			// get mongo db connection
			$db = new MongoClient(
				"mongodb://{$this->getHost()}:{$this->getPort()}", 
				array(
					'username' => $this->getUsername(),
					'password' => $this->getPassword()
				)
			);
		} catch (MongoConnectionException $e) {
			//echo '<pre>';
			print_r($e);//die;
		}

		// get db
		$dbName = $this->getDbname();
		$dataBase = $db->$dbName;
		
		try {
			// test mongo connection
			$db->connect();
		} catch (Zend_Exception $e) {		  
			var_dump($e);  
			// TODO: uraditi nesto sa exceptionom
		}
		
		return $dataBase;
	}
	
}