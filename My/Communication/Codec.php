<?php
class My_Communication_Codec
{
	private $_strategy;
	private $_data = null;
//	private $_messageGroup = null;
//	private $_version = null;
	private $_messageHeader = null;
	
	private $_messageGroups = array(
		'0'   => 'Regular', 
		'1'   => 'DrivingBehavior', 
		'2'	  => 'AdvancedDrivingBehavior', 
		'3'	  => 'Stability', 
		'4'   => 'Acn',
		'5'   => 'Mdm',
		'7'   => 'CrashReport',
		'128' => 'CommandAcknowledge',
		'129' => 'CommandUnknown',
		'130' => 'CommandRequest',
		'131' => 'HardwareConfigurationFile',
		'132' => 'AlgorithmConfiguration',
		'134' => 'CommandNotValid',
		'135' => 'GeoFenceAdd',
		'136' => 'GeoFenceDelete',
		'137' => 'DataRetentionConfiguration',
		'138' => 'ResendMessages',
		'139' => 'SystemCommand',
		'142' => 'VehicleReservation',
	);

	
	public function __construct($data)
	{
		$this->_data = $data;
	}
	
	protected function _decodeHeader() 
	{
		$this->_messageHeader = unpack("L4message_uuid/L2device_id/S1length/C1message_group/C1version",$this->_data);
		
		//$this->_messageGroup = (int) $this->_messageHeader['message_group'];
		$this->_messageHeader['message_group'] = (int) $this->_messageHeader['message_group'];
		//$this->_version = $message['version'] == 0 ? 2 : ((int) $message['version']);
	}
	
	public function decode()
	{
		$this->_decodeHeader();
	
		if (!key_exists($this->_messageHeader['message_group'], $this->_messageGroups)) {
		
			return false;
//			throw new Exception('Invalid message group.');
		} elseif ($this->_messageHeader['message_group'] > 127) {
				
			$this->_messageHeader['message_uuid'] = vsprintf(
				"%08x%08x%08x%08x",  
				array(
					$this->_messageHeader['message_uuid1'],
					$this->_messageHeader['message_uuid2'],
					$this->_messageHeader['message_uuid3'],
					$this->_messageHeader['message_uuid4']
				)
			);
			unset($this->_messageHeader['message_uuid1']);	
			unset($this->_messageHeader['message_uuid2']);
			unset($this->_messageHeader['message_uuid3']);
			unset($this->_messageHeader['message_uuid4']);	
			
			$this->_messageHeader['device_id'] = bcadd($this->_messageHeader['device_id1'], bcmul($this->_messageHeader['device_id2'], bcpow(2, 32)), 0);
			
			unset($this->_messageHeader['device_id1']);
			unset($this->_messageHeader['device_id2']);
				
			return $this->_messageHeader;
		} 
	
		$this->_strategy = $this->_strategyFactory($this->_messageHeader['message_group'], $this->_messageHeader['version']);
			
		return $this->_strategy->decode($this->_data);
	}
	
	
	public function encode()
	{

		
		$group = $this->_findCommandGroup();

		if (!array_key_exists($group, $this->_messageGroups)) {
			return false;
//			throw new Exception('Invalid command group.');
		}
		
		$this->_strategy = $this->_strategyFactory($group, $this->_data['version']);

		// testing different modes of packing/unpacking		
		$dec = array_map('hexdec', explode('-', chunk_split($this->_data['message_uuid'], 8, '-')));
		foreach ($dec as $key => $value) $dec[$key] = (int) $value;
		
		unset($this->_data['message_uuid']);
		
		$deviceId1 = bcmod($this->_data['device_id'], bcpow(2, 32));
		$deviceId2 = bcdiv(bcsub($this->_data['device_id'], $deviceId1), bcpow(2, 32));
		
		$this->_data = array_merge(array('message_uuid1' => $dec[0], 'message_uuid2' => $dec[1], 'message_uuid3' => $dec[2], 'message_uuid4' => $dec[3], 'device_id1'=> (int) $deviceId1, 'device_id2' => (int) $deviceId2), $this->_data);
		
		unset($this->_data['device_id']);

		return $this->_strategy->encode($this->_data);
	}
	
	private function _findCommandGroup()
	{
		return intval($this->_data['message_group']);
	}
	
	private function _strategyFactory($group, $version = 1) 
	{
		
		if(!isset($group)) {
			throw new Exception('Message group ID name must be supplied');
		}
		
		$strategyClassName = "My_Communication_Codec_Message_Version{$version}_" . $this->_messageGroups[$group];
		
		if(!class_exists($strategyClassName)) {
			
    		throw new Exception('The class "' . $strategyClassName. ' does not exists');
		}

		$strategy = new $strategyClassName(); 

    	if(!($strategy instanceof My_Communication_Codec_Strategy)) {
    		throw new Exception('The class "' . $strategyClassName. ' must extend "My_Communication_Codec_Strategy"');
    	}
    	
    	return $strategy;
	}
	
	/**
	 * 
	 * @deprecated
	 */	
	private function _findMessageGroup()
	{
		$message = unpack("L4message_uuid/L1device_id/S1length/C1message_group",$this->_data);
		
		//ovo treba izbaciti sluzi samo za decodovanje comandi radi testiranja
//		$message = unpack("C1message_uuid/L1device_id/S1length/C1message_group",$this->_data);

		return intval($message['message_group']);
	}	
	
}