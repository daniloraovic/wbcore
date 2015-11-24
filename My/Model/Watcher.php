<?php
/**
 * Identity Map and Unit Of Work design pattern
 *
 * @author MS
 *
 */
class My_Model_Watcher
{

	private static $_instance;

	private $_all = array();
	private $_bulkDelete = array();
	private $_delete = array();
	private $_dirty = array();
	private $_new = array();
	private $_fileInsert = array();


	private function __construct()
	{

	}

	private function __clone()
	{
		trigger_error("Singleton Design Pattern, shouldn't be cloning the instance", E_USER_ERROR);
	}

	/**
	 *
	 *
	 * @param My_Model_Domain $model
	 */
	public static function add(My_Model_Domain $obj)
	{
		$inst = self::instance();

		$inst->_all[$inst->globalKey($obj)] = $obj;
	}

	/**
	 *
	 *
	 *
	 */
	public static function commit($stopOnException = false)
	{
		$inst = self::instance();

		$result = array();
		
		if ( !empty($inst->_fileInsert) ) {
			
			foreach ( $inst->_fileInsert as $fileName => $values ) {

				$index = md5($fileName);
				
				foreach ( $values as $key => $obj ) {

					$mappers = $obj->getMappers( 'file-insert' );
						
					if ( !empty($mappers) ) {

						if ( count($mappers) > 1 ) {
								
							throw new Exception('Multiple file-insert mappers.');
						}
							
						$mapper = reset($mappers);

						if ($obj->getClientIdExist()) {
							$mapperInst = new $mapper($obj->getCid());								
						} else {
							$mapperInst = new $mapper;
						}
						
						$result['file-insert'][] = array(
								'response' => $mapperInst->filePrepare( $fileName, $obj ),
								'mapper' => $mapper,
								'obj' => $obj,
								'index' => $index
						);
							
					}
					unset($inst->_fileInsert[$fileName][$key]);
				}

				try {
						
					$result['results'][$index] = $mapperInst->fileInsert( $fileName );
						
				}
				catch(Exception $e) {
					throw $e;
				}

				unset($inst->_fileInsert[$fileName]);
			}
		}

		if ( !empty($inst->_new) ) {
				
			foreach ( $inst->_new as $key => $obj ) {

				$mappers = $obj->getMappers( 'insert' );
					
				if ( !empty($mappers) ) {
						
					foreach ( $mappers as $mapper ) {

						if ($obj->getClientIdExist()) {
							$mapperInst = new $mapper($obj->getCid());							
						} else {
							$mapperInst = new $mapper;
						}

						$result['insert'][] = array(
								'response' => $mapperInst->insert( $obj ),
								'mapper' => $mapper,
								'obj' => $obj
						);

					}
				}
				unset($inst->_new[$key]);
			}
		}

		if (!empty($inst->_dirty)) {

			foreach ($inst->_dirty as $key => $obj) {

				$mappers = $obj->getMappers( 'update' );

				if ( !empty($mappers) ) {
						
					foreach ( $mappers as $mapper ) {

						if ($obj->getClientIdExist()) {
							$mapperInst = new $mapper($obj->getCid());							
						} else {
							$mapperInst = new $mapper;
						}
													
						$result['update'][] = array(
								'response' => $mapperInst->update($obj),
								'mapper' => $mapper,
								'obj' => $obj
						);

					}
				}
				unset($inst->_dirty[$key]);
			}
		}

		if ( !empty($inst->_bulkDelete) ) {

			$obj = reset($inst->_bulkDelete);

			$mappers = $obj->getMappers( 'bulk-delete' );

			if ( !empty($mappers) ) {

				$mapper = reset($mappers);

				if ($obj->getClientIdExist()) {
					$mapperInst = new $mapper($obj->getCid());							
				} else {
					$mapperInst = new $mapper;
				}				
					
				$result['delete'][] = array(
						'response' => $mapperInst->bulkDelete( $inst->_bulkDelete ),
						'mapper' => $mapper,
						'obj' => $obj
				);
					
			}
		}

		if ( !empty($inst->_delete) ) {

			foreach ( $inst->_delete as $key => $obj ) {

				$mappers = $obj->getMappers( 'delete' );

				if ( !empty($mappers) ) {
						
					foreach ( $mappers as $mapper ) {

						if ($obj->getClientIdExist()) {
							$mapperInst = new $mapper($obj->getCid());							
						} else {
							$mapperInst = new $mapper;
						}
						
						$result['delete'][] = array(
								'response' => $mapperInst->delete( $obj ),
								'mapper' => $mapper,
								'obj' => $obj
						);
					}
				}
				unset($inst->_new[$key]);
			}
		}

		return $result;
	}

	/**
	 *
	 *
	 * @param string $className
	 * @param int $id
	 *
	 * @return mixed
	 */
	public static function exists($className, $id)
	{
		$inst = self::instance();

		$key = $className . $id;

		if ( isset($inst->_dirty[$key]) ) {
			return $inst->_dirty[$key];
		}

		return null;
	}

	/**
	 * Singleton design pattern
	 *
	 * @return My_Model_Watcher
	 */
	public static function instance()
	{
		if (empty(self::$_instance)) {
			self::$_instance = new My_Model_Watcher();
		}

		return self::$_instance;
	}

	public static function registerBulkDelete( My_Model_Domain $obj )
	{
		$inst = self::instance();

		$inst->_bulkDelete[$obj->getId()] = $obj;
	}

	/**
	 *
	 *
	 * @param My_Model_Domain $obj
	 *
	 * @return void
	 */
	public static function registerClean(My_Model_Domain $obj)
	{
		$inst = self::instance();

		unset($inst->_delete[$inst->globalKey($obj)]);

		unset($inst->_dirty[$inst->globalKey($obj)]);

		if( in_array($obj, $inst->_new, true) ) {
			$key = array_search($obj, $inst->_new, true);
			unset($inst->_new[$key]);
		}
	}

	/**
	 *
	 *
	 * @param My_Model_Domain $obj
	 *
	 * @return void
	 */
	public static function registerDelete(My_Model_Domain $obj)
	{
		$inst = self::instance();

		$inst->_delete[$inst->globalKey($obj)] = $obj;
	}

	/**
	 *
	 *
	 * @param My_Model_Domain $obj
	 *
	 * @return void
	 */
	public static function registerDirty(My_Model_Domain $obj)
	{
		$inst = self::instance();

		$inst->_dirty[$inst->globalKey($obj)] = $obj;
	}

	public static function registerFileInsert( $fileName, My_Model_Domain $obj)
	{
		$inst = self::instance();

		$inst->_fileInsert[$fileName][$inst->globalKey($obj)] = $obj;
	}

	/**
	 *
	 *
	 * @param My_Model_Domain $obj
	 *
	 * @return void
	 */
	public static function registerNew(My_Model_Domain $obj)
	{
		$inst = self::instance();

		if( !in_array($obj, $inst->_new, true) ) {
			$inst->_new[] = $obj;
		}
	}

	/**
	 *
	 *
	 * @param My_Model_Domain $model
	 */
	private function globalKey(My_Model_Domain $obj)
	{
		return get_class($obj) . $obj->getId();
	}

}
