<?php
class My_Communication_Codec_Strategy
{
	protected $_unpackMap = '';
	
	protected $_packMap = '';
	
	
	public function encode( array $data )
	{

		if( isset($data['ip_address']) ) {
			
			$packMap = str_replace('@ip_length@', strlen($data['ip_address'])+1, $this->_packMap);
			
		} elseif( isset($data['number_of_vertices']) ) {
			
			$packMap = str_replace('@vertices@', str_repeat ( "f", (int)$data['number_of_vertices'] * 2 ), $this->_packMap);
			
		} else {
			
			$packMap = $this->_packMap;
		}

		return call_user_func_array('pack', array_merge(array($packMap), $data));
	}
	
	
	public function decode($data)
	{ 
		
		$unpackArr = unpack($this->_unpackMap, $data);

		$unpackArr['message_uuid'] = vsprintf(
			"%08x%08x%08x%08x",  
			array(
				$unpackArr['message_uuid1'],
				$unpackArr['message_uuid2'],
				$unpackArr['message_uuid3'],
				$unpackArr['message_uuid4']
			)
		);
		unset($unpackArr['message_uuid1']);
		unset($unpackArr['message_uuid2']);
		unset($unpackArr['message_uuid3']);
		unset($unpackArr['message_uuid4']);
		
		$unpackArr['device_id'] = bcadd($unpackArr['device_id1'], bcmul($unpackArr['device_id2'], bcpow(2, 32)), 0);

		//@todo fantomski podatak koji se u poruku ugradjuje greskom, ovde se uklanja (samo za tip 70)
		if (isset($unpackArr['alien'])) unset($unpackArr['alien']);
		
		unset($unpackArr['device_id1']);	
		unset($unpackArr['device_id2']);

		return $unpackArr;
	}

	protected function _getPackMap()
	{
		$conf = Zend_Registry::get('configuration');

		$class = get_class($this);
		
		
		preg_match('~Version\d+~', $class, $version);

		preg_match('~Version\d+_(.+)~', $class, $messageGroup);
//var_dump($conf->packMap->{$messageGroup[1]}->{$version[0]});

		return $conf->packMap->{$messageGroup[1]}->{$version[0]};		
	}	
	
	protected function _getUnpackMap()
	{
		$conf = Zend_Registry::get('configuration');
		
		$class = get_class($this);
		
		preg_match('~Version\d+~', $class, $version);

		preg_match('~Version\d+_(.+)~', $class, $messageGroup);

		return $conf->unpackMap->{$messageGroup[1]}->{$version[0]};		
	}
	
}