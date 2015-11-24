<?php
class My_Model_Mapper_Mysql extends My_Model_Mapper
{

	protected $_messagePayload = array('traveled_distance' => null,
			'average_speed' => null,
			'aggressive_acc' => null,
			'aggressive_dec' => null,
			'steady_speed' => null,
			'speed_under' => null,
			'speed_over' => null,
			'idling' => null,
			'reserved' => null,
			'speed' => null,
			'max_delta_v' => null,
			'severity' => null,
			'max_speed' => null,
			'lateral_acc' => null,
			'side_slip_distance' => null,
			'duration' => null,
			'accident_type_id' => null,
			'maximum_delta_v' => null,
			'rolover' => null,
			'rolover_image' => null,
			'pdof_horizontal_angle' => null,
			'pdof_direction' => null,
			'pdof_image' => null,
			'pdof_vertical_angle' => null,
			'entrapment' => null,
			'injury_probability' => null,
			'multiple_hits' => null,
			'position_update' => null,
			'send_timeout' => null,
			'rep_number' => null,
			'lock_conn_timeout' => null,
			'log_delay' => null,
			'led_state' => null,
			'reserved1' => null,
			'reserved2' => null,
			'ip_address' => null,
			'datetime_on' => null,
			'latitude_on' => null,
			'longitude_on' => null,
			'num_aggressive_acc' => null,
			'num_aggressive_dec' => null,
			'rpm_hi' => null,
			'rpm_higher_band' => null,
			'rpm_optimal_band' => null,
			'rpm_lower_band' => null,
			'rpm_lo' => null,
			'max_acc_x' => null,
			'total_delta_vx' => null,
			'end_speed' => null,
			'severity_level' => null,
			'probability' => null,
			'max_acc_y' => null,
			'total_delta_vy' => null,
			'max_acc' => null,
			'total_delta_v' => null,
			'panic_button' => null,
			'reserved0' => null,
			'period_started' => null,
			'period_ended' => null,
			'overall_time' => null,
			'num_journeys' => null,
			'truck' => null,
			'datetime_last_pos0' => null,
			'latitude_last_pos0' => null,
			'longitude_last_pos0' => null,
			'datetime_last_pos1' => null,
			'latitude_last_pos1' => null,
			'longitude_last_pos1' => null,
			'datetime_last_pos2' => null,
			'latitude_last_pos2' => null,
			'longitude_last_pos2' => null,
			'acc_x_serialized' => null,
			'acc_y_serialized' => null,
			'acc_z_serialized' => null,
			'gyro_x_serialized' => null,
			'gyro_y_serialized' => null,
			'gyro_z_serialized' => null,
			'mag_serialized' => null,
			'longitude_arr_serialized' => null,
			'latitude_arr_serialized' => null,
			'vehicle_heading_arr_serialized' => null,
			'vehicle_speed_arr_serialized' => null,
			'crash_image' => null,
			'crash_report_name' => null,
			'param0' => null,
			'daily_date' => null,
			'daily_working_hours' => null,
			'daily_travelled_distance' => null,
			'daily_longest_journey_without_break' => null,
			'daily_longest_driving_distance' => null,
			'daily_longest_idling' => null,
			'daily_usage_indicator' => null,
			'daily_safety_indicator' => null,
			'daily_energy_indicator' => null,
			'daily_param0' => null,
			'daily_param1' => null,
			'daily_param2' => null,
			'daily_num_of_ended_journeys' => null,
			'daily_max_speed' => null,
			'daily_average_speed' => null,
			'daily_num_aggressive_acc' => null,
			'daily_num_aggressive_dec' => null,
			'daily_num_cornering' => null,
			'daily_num_abrupt_lane' => null,
			'daily_num_barrier_avoid' => null,
			'daily_num_skidding' => null,
			'daily_num_abrupt_turning' => null,
			'daily_num_spinning' => null,
			'daily_num_speed_events' => null,
			'daily_num_of_driving_without_pause' => null,
			'daily_num_of_idling' => null,
			'daily_exceptions_statistic' => null,
			'daily_num_of_geofence_breaks' => null,
			'daily_rpm_breaks' => null,
			'daily_reported_fault' => null,
			'daily_steady_speed' => null,
			'daily_speed_under' => null,
			'daily_speed_over' => null,
			'daily_idling' => null,
			'daily_rpm_hi' => null,
			'daily_rpm_higher_band' => null,
			'daily_rpm_optimal_band' => null,
			'daily_rpm_lower_band' => null,
			'daily_rpm_lo' => null,
			'daily_car_battery_low' => null,
			'daily_car_battery_failure' => null,
			'daily_backup_battery_low' => null,
			'daily_backup_battery_failure' => null,
			'daily_gsm_unavailable' => null,
			'daily_gps_unavailable' => null,
			'daily_obd_connection_failure' => null,
			'daily_ios_connection_failure' => null,
			'daily_disk_space_full' => null,
			'fence_id' => null,
			'geofence_latitude_last_pos' => null,
			'geofence_longitude_last_pos' => null,
	);

	/**
	 * id of a client database
	 * @var int
	 */
	protected $clientId = null;

	/**
	 * table name mapper is mapping to
	 * @var string
	 */
	protected $_tablename = null;

	/**
	 * table's primary key field name
	 * @var string
	 */
	protected $_primary_key_field = 'id';

	/**
	 *
	 * @var Zend_Db_Adapter_Abstract
	 */
	protected $_connection;

	/**
	 *
	 * @var Zend_Db_Select
	 * @var unknown_type
	 */
	protected $_select = null;

	public function __construct($table = '') {
		$this->_tablename = $table;
		parent::__construct();
	}


	/**
	 *
	 * @return Zend_Db_Adapter_Abstract
	 */
	protected function _db( $config )
	{
		if( empty($config) ) {
			throw new Exception('no mysql db config');
		}

		$dbFactory = new My_Model_Factory_Db_Mysql();

		return $dbFactory->setHost($config->host)
		->setPort($config->port)
		->setUsername($config->username)
		->setPassword($config->password)
		->setDbname($config->dbname)
		->getConnection();
	}

	/**
	 *
	 * @return Zend_Db_Adapter_Abstract
	 */
	public function _dbGeneral()
	{
		return $this->_db($this->_dbConfiguration->mysql->general);
	}


	/**
	 *
	 * @return Zend_Db_Adapter_Abstract
	 */
	public function _dbClient()
	{
		return $this->_db($this->_dbConfiguration->mysql->client);
	}

	public function _dbClientRequest()
	{
		return $this->_db($this->_dbConfiguration->mysql->recoveryRequest->client);
	}


	/**
	 *
	 * @return Zend_Db_Adapter_Abstract
	 */
	public function _dbClientData( $clientId )
	{
		if( empty($clientId) ) {
			throw new Exception('no client id');
		}

		$client = 'client' . $clientId;

		return $this->_db($this->_dbConfiguration->mysql->clientData->$client);
	}

	/**
	 *
	 * @return Zend_Db_Adapter_Abstract
	 */
	public function _dbClientPd( $clientId )
	{
		if( empty($clientId) ) {
			throw new Exception('no client id');
		}
	
		$client = 'client' . $clientId;
	
		return $this->_db($this->_dbConfiguration->mysql->clientPd->$client);
	}
	
	public function getIdentity()
	{
		return new My_Model_Mapper_Mysql_IdentityObject();
	}

	public function getNumRows() {
		if (!$this->_select) return 0;
		$paginator = Zend_Paginator::factory($this->_select, 'DbSelect');
		return $paginator->getTotalItemCount();
	}

	public function getLastSelect() {
		return $this->_select;
	}

	public function insert($obj) {
		
		if ($obj instanceof My_Model_Domain) {
			if($obj->getClientIdUnsetFromData()) {
				$obj->unsetField($obj->getClientIdKey());
			}
			$data = $obj->getData();
		} elseif (is_array($obj)) {			
			$data = $obj;
		} else {
			throw new Exception("Unsupported datatype used in insert", -1001);
		}

		return $this->_connection->insert($this->_tablename, $data);
	}

	public function lastInsertId() {
		return $this->_connection->lastInsertId();
	}

	public function startTransaction() {
		return $this->_connection->beginTransaction();
	}

	public function select(My_Model_Mapper_IdentityObject $identity) {
		$select = $this->_connection->select();
		$select->from($this->_tablename);
		$select->where($this->_getSelection()->where($identity))
		->limit($this->_getSelection()->limit($identity), $this->_getSelection()->offset($identity))
		->order($this->_getSelection()->orderBy($identity))
		;
		$this->_select = $select;
		// 				echo $this->_select, "\n\n";
		// 				die;
		return $this;
	}

	/**
	 *
	 * @param My_Model_Domain|array $obj
	 * @throws Exception
	 * @return number
	 */
	public function update($obj) {

		if ($obj instanceof My_Model_Domain) {
			if($obj->getClientIdUnsetFromData()) {
				$obj->unsetField($obj->getClientIdKey());
			}			
			$bind = $obj->getData();
		} elseif (is_array($obj)) {
			$bind = $obj;
		} else {
			throw new Exception("Unsupported datatype used in insert/update", -1001);
		}
		
		$db = $this->_connection;

		return $this->_connection->update($this->_tablename, $bind, $db->quoteInto("$this->_primary_key_field = (?)", $obj->{"get" . $this->_primary_key_field}()));
	}

	public function updateAll(My_Model_Mapper_IdentityObject $identity, array $data) {
		if (empty($data)) {
			throw new Exception("Data can not be empty");
		}
		return $this->_connection->update($this->_tablename, $data, $this->_getSelection()->where($identity));
	}

	public function query($sql, $bind = array()) {
		$this->_connection->query($sql, $bind);
	}

	public function rollback() {
		$this->_connection->rollback();
	}
	
	public function commit() {
		$this->_connection->commit();
	}

	public function delete($obj) {
		
		$db = $this->_connection;		
		return $this->_connection->delete($this->_tablename, $db->quoteInto("$this->_primary_key_field = (?)", $obj->{"get" . $this->_primary_key_field}()));
	}
	
	public function deleteAll(My_Model_Mapper_IdentityObject $identity) {
		return $this->_connection->delete($this->_tablename, $this->_getSelection()->where($identity));
	}
	
	/**
	 *
	 * @return My_Model_Mapper_Mysql_SelectionFactory
	 */
	protected function _getSelection()
	{
		return new My_Model_Mapper_Mysql_SelectionFactory();
	}

	protected function _selectAll(My_Model_Mapper_IdentityObject $identity) {
		$this->select($identity);
		$data = $this->_connection->fetchAll($this->_select);
		return $data;
	}

	protected function _selectOne(My_Model_Mapper_IdentityObject $identity) {
		$this->select($identity);
		$data = $this->_connection->fetchRow($this->_select);
		return $data;
	}
}