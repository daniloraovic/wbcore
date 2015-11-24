<?php
class My_Db extends Zend_Db {
	
	private static $connections = array();
	private static $forcedConnections = array();
	private static $allConnections = array();
	protected static $_instance = null;
	
	public static function factory($adapter, $config = array(), $force_new_connections = false) {
		if(!self::$_instance instanceof self) {
			self::$_instance = new self();
		}
		if ($config instanceof Zend_Config) {
			$config = $config->toArray();
		}
	
		/*
		 * Convert Zend_Config argument to plain string
		* adapter name and separate config object.
		*/
		if ($adapter instanceof Zend_Config) {
			if (isset($adapter->params)) {
				$config = $adapter->params->toArray();
			}
			if (isset($adapter->adapter)) {
				$adapter = (string) $adapter->adapter;
			} else {
				$adapter = null;
			}
		}
	
		/*
		 * Verify that adapter parameters are in an array.
		*/
		if (!is_array($config)) {
			/**
			 * @see Zend_Db_Exception
			 */
			require_once 'Zend/Db/Exception.php';
			throw new Zend_Db_Exception('Adapter parameters must be in an array or a Zend_Config object');
		}
	
		/*
		 * Verify that an adapter name has been specified.
		*/
		if (!is_string($adapter) || empty($adapter)) {
			/**
			 * @see Zend_Db_Exception
			 */
			require_once 'Zend/Db/Exception.php';
			throw new Zend_Db_Exception('Adapter name must be specified in a string');
		}
	
		/*
		 * Form full adapter class name
		*/
		$adapterNamespace = 'Zend_Db_Adapter';
		if (isset($config['adapterNamespace'])) {
			if ($config['adapterNamespace'] != '') {
				$adapterNamespace = $config['adapterNamespace'];
			}
			unset($config['adapterNamespace']);
		}
	
		// Adapter no longer normalized- see http://framework.zend.com/issues/browse/ZF-5606
		$adapterName = $adapterNamespace . '_';
		$adapterName .= str_replace(' ', '_', ucwords(str_replace('_', ' ', strtolower($adapter))));
	
		/*
		 * Load the adapter class.  This throws an exception
		* if the specified class cannot be loaded.
		*/
		if (!class_exists($adapterName)) {
			require_once 'Zend/Loader.php';
			Zend_Loader::loadClass($adapterName);
		}
	
		$fingerprint = md5($adapterName . '|' . serialize($config));
		if ($force_new_connections || !isset(self::$connections[$fingerprint])) {
			/*
			 * Create an instance of the adapter class.
			* Pass the config to the adapter class constructor.
			*/
			$dbAdapter = new $adapterName($config);

			/*
			 * Verify that the object created is a descendent of the abstract adapter type.
			*/
			if (! $dbAdapter instanceof Zend_Db_Adapter_Abstract) {
				/**
				 * @see Zend_Db_Exception
				 */
				require_once 'Zend/Db/Exception.php';
				throw new Zend_Db_Exception("Adapter class '$adapterName' does not extend Zend_Db_Adapter_Abstract");
			}
			
			$databaseName = $dbAdapter->getConfig();
			if ($force_new_connections) {
				self::$forcedConnections[] = $dbAdapter;
				return $dbAdapter;
			} else {
				self::$connections[$fingerprint] = $dbAdapter;
			}
		}
		
		return self::$connections[$fingerprint];
	}
	
	public function __destruct() {
		foreach(self::$connections as $fingerprint => $adapter) {
			if($adapter->isConnected()) {
				$adapter->closeConnection();
				unset(self::$connections[$fingerprint]);
			}
		}
		foreach(self::$forcedConnections as $index => $adapter) {
			if($adapter->isConnected()) {
				$adapter->closeConnection();
				unset(self::$forcedConnections[$index]);
			}
		}
		self::$allConnections = null;
	}
}