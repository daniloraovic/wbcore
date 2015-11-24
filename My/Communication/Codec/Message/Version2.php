<?php
class My_Communication_Codec_Message_Version2 extends My_Communication_Codec_Strategy 
{
	
	public function decode($data)
	{
		$unpackArr = parent::decode( $data );
		
		if(isset($unpackArr['event_type_id']) ) {
			
			if ( ($unpackArr['message_group'] == 0) && ($unpackArr['event_type_id'] == 1) ) {
			
				$this->_unpackMap .= "a20datetime_on/f1latitude_on/f1longitude_on/f1traveled_distance/S1max_speed/S1average_speed/S1aggressive_acc/S1aggressive_dec/S1num_aggressive_acc/S1num_aggressive_dec/S1steady_speed/S1speed_under/S1speed_over/S1idling/S1rpm_hi/S1rpm_higher_band/S1rpm_optimal_band/S1rpm_lower_band/S1rpm_lo/S1reserved/s1term";
		
				$unpackArr = parent::decode( $data );
			}
			
			$status0Arr = array("status_gsm", "status_gps", "status_obd", "status_io0", "status_io1", "status_io2", "status_io3", "status0_reserved");
			$status1Arr = array("status_moving", "status_engine_state", "status1_reserved0", "status1_reserved1", "status1_reserved2", "status1_reserved3", "status1_reserved4", "status1_reserved5");
			$healthArr = array("status_heart_beat", "status_int_battery_failure", "status_car_battery_failure", "status_disk_space_low", "status_self_test", "health_reserved0", "health_reserved1", "health_reserved2");
	
			$status0Binary = str_split( decbin( $unpackArr["_status0"] ));
			$status1Binary = str_split( decbin( $unpackArr["_status1"] ));	
			$healthBinary = str_split( decbin( $unpackArr["_health"] ));
	
			$status0Binary = array_pad($status0Binary, -8, 0);
			$status1Binary = array_pad($status1Binary, -8, 0);
			$healthBinary = array_pad($healthBinary, -8, 0);
			
			for($i=0;$i<8;$i++) {
				
				$unpackArr[$status0Arr[$i]] = ( !empty($status0Binary[7-$i]) ? intval($status0Binary[7-$i]) : 0 );
				$unpackArr[$status1Arr[$i]] = ( !empty($status1Binary[7-$i]) ? intval($status1Binary[7-$i]) : 0 );
				$unpackArr[$healthArr[$i]] = ( !empty($healthBinary[7-$i]) ? intval($healthBinary[7-$i]) : 0 );
			}
			
			unset( $unpackArr["_status0"] );
			unset( $unpackArr["_status1"] );
			unset( $unpackArr["_health"] );
		}

		return $unpackArr;
	}
}