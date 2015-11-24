<?php 

abstract class My_Model_Factory_Db
{
	
	protected $_host = null;
	protected $_port = null;
	protected $_username = null;
	protected $_password = null;
	protected $_dbname = null;
	
	
	abstract public function getConnection();
	
	
	public function getHost()
	{
		return $this->_host;
	}
	
	
	public function setHost( $host )
	{
		$this->_host = $host;
		
		return $this;
	}
	
	public function getPort()
	{
		return $this->_port;
	}
	
	
	public function setPort( $port )
	{
		$this->_port = $port;
		
		return $this;
	}
	
	
	public function getUsername()
	{
		return $this->_username;
	}
	
	
	public function setUsername( $username )
	{
		$this->_username = $username;
		
		return $this;
	}
	
	
	public function getPassword()
	{
		return $this->_password;
	}
	
	
	public function setPassword( $password )
	{
		$this->_password = $password;
		
		return $this;
	}
	
	
	public function getDbname()
	{
		return $this->_dbname;
	}
	
	
	public function setDbname( $dbname )
	{
		$this->_dbname = $dbname;
		
		return $this;
	}
	
}