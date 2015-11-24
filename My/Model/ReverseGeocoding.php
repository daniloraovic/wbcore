<?php
class My_Model_ReverseGeocoding
{
	public static function getGeocoding($latitude, $longitude, $clientId) {

// 		curl_init("http://maps.google.com/maps/geo?q=" . $event->getLatitude() . ",". $event->getLongitude() ."&output=json&sensor=false");
				
		$ch = curl_init("http://maps.googleapis.com/maps/api/geocode/json?latlng=" . $latitude . ",". $longitude ."&sensor=false");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$exec = curl_exec($ch);
		$jsondata = json_decode($exec,true);
		
		$result = array();
				
		if( is_array( $jsondata ) && $jsondata["status"] == "OK" )
		{
			$key = 0; 
			foreach ($jsondata["results"] as $index => $valuse) {
				if($valuse["geometry"]["location_type"] == "ROOFTOP") {
					$key = $index;
					break;
				} 
			}
			
			$data = $jsondata["results"][$key];
			$result["location"] = $data["formatted_address"];
			$result["accuracy"] = $data["geometry"]["location_type"];

		} else if(is_array( $jsondata ) && $jsondata["status"] == "ZERO_RESULTS") {
			//error_log("\n" . print_r(date('d/m/Y', time()), true), 3, APPLICATION_PATH . "/data/logs/" . $clientId . "-reverse-geocoding.log" );
			//error_log("\nError ocure during reverse geocoding " . print_r($jsondata["status"], true), 3, APPLICATION_PATH . "/data/logs/" . $clientId . "-reverse-geocoding.log" );
			$result["location"] = "Location is still unavailable";
			$result["accuracy"] = "None";			
		} else if(is_array( $jsondata ) && $jsondata["status"] == "OVER_QUERY_LIMIT") {
			//error_log("\n" . print_r(date('d/m/Y', time()), true), 3, APPLICATION_PATH . "/data/logs/" . $clientId . "-reverse-geocoding.log" );
			//error_log("\nError ocure during reverse geocoding " . print_r($jsondata["status"], true), 3, APPLICATION_PATH . "/data/logs/" . $clientId . "-reverse-geocoding.log" );
			$result["location"] = "Location is still unavailable";
			$result["accuracy"] = "None";
		} else if(is_array( $jsondata ) && $jsondata["status"] == "REQUEST_DENIED") {
			//error_log("\n" . print_r(date('d/m/Y', time()), true), 3, APPLICATION_PATH . "/data/logs/" . $clientId . "-reverse-geocoding.log" );
			//error_log("\nError ocure during reverse geocoding " . print_r($jsondata["status"], true), 3, APPLICATION_PATH . "/data/logs/" . $clientId . "-reverse-geocoding.log" );
			$result["location"] = "Location is still unavailable";
			$result["accuracy"] = "None";
		} else if(is_array( $jsondata ) && $jsondata["status"] == "INVALID_REQUEST") {
			//error_log("\n" . print_r(date('d/m/Y', time()), true), 3, APPLICATION_PATH . "/data/logs/" . $clientId . "-reverse-geocoding.log" );
			//error_log("\nError ocure during reverse geocoding " . print_r($jsondata["status"], true), 3, APPLICATION_PATH . "/data/logs/" . $clientId . "-reverse-geocoding.log" );
			$result["location"] = "Location is still unavailable";
			$result["accuracy"] = "None";
		} else if(is_array( $jsondata ) && $jsondata["status"] == "UNKNOWN_ERROR") {
			//error_log("\n" . print_r(date('d/m/Y', time()), true), 3, APPLICATION_PATH . "/data/logs/" . $clientId . "-reverse-geocoding.log" );
			//error_log("\nError ocure during reverse geocoding " . print_r($jsondata["status"], true), 3, APPLICATION_PATH . "/data/logs/" . $clientId . "-reverse-geocoding.log" );
			$result["location"] = "Location is still unavailable";
			$result["accuracy"] = "None";
		}
		
		return $result;
	}
}