<?php 

class My_Model_Factory_Db_Mysql extends My_Model_Factory_Db
{
	
	public function getConnection()
	{
		// get mysql connection
		$db = My_Db::factory('Pdo_Mysql', array(
			'host' => $this->getHost(),
			'port' => $this->getPort(),
            'username' => $this->getUsername(),
            'password' => $this->getPassword(),
            'dbname'   => $this->getDbname(),
			'driver_options' => array(PDO::MYSQL_ATTR_LOCAL_INFILE => true),

		));
	
		try {
			// test mysql connection
		    $db->getConnection();	    			
		} catch (Zend_Exception $e) {
			//var_dump($e);
			//throw new Exception("Database connection not present", 1036);
	     	// TODO: uraditi nesto sa exceptionom
		}	

		return $db;
	}
	
}