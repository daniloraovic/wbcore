<?php
/**
 * Non-regular execution and error logging utility
 *
 * Mail sender functionality (within '_sendEmail' method)
 * has a Zend_Mail class dependency
 *
 * @author MS
 *
 */
class My_Controller_Logger
{

	//Logging configuration
	protected $_configuration = array();
	
	//Application configuration
	protected $_appConfiguration = array();
	
	//Client id if applicable
	protected $_clientId = null;
	
	//Absolute path to the log file
	protected $_logFile = null;
	
	//Payload containing information to be logged
	protected $_payload = null;
	
	//Message to be logged
	protected $_message = null;
	
	//Brief debug backtrace
	protected $_briefBacktrace = null;
	protected $_briefBacktraceExport = null;
	
	//Full debug backtrace
	protected $_fullBacktrace = null;
	protected $_fullBacktraceExport = null;
	
	//Datetime
	protected $_dateTime = null;
	
	private static function _swapObjectToClassName(&$object) {

		foreach($object as $container => $entity) {
			
			$type = gettype($entity);
			
			switch($type) {
				
				case "object": {
					
					$object[$container] = get_class($entity);
					
				}; break;
				
				case "resource": {
					 
					$object[$container] = get_resource_type($entity);
					
				}; break;
				
				case "array": {

					self::_swapObjectToClassName($object[$container]);
					
				}
				
			}
			
		}
		
	}
	
	public function __construct($logFile = null) {
		
		$this->_configuration = array( 
										"recipient" => array(
											"mail" => Zend_Registry::get('configuration')->logMail->recipient->mail,
											"name" => Zend_Registry::get('configuration')->logMail->recipient->name,
										),
										"sender" => array(
											"mail" => Zend_Registry::get('configuration')->logMail->sender->mail,
											"name" => Zend_Registry::get('configuration')->logMail->sender->name,	
										),
										"logFileNamePrefix" => array(
											"client" => Zend_Registry::get('configuration')->bitgearLoggerFilenamePrefix->client,
											"global" => Zend_Registry::get('configuration')->bitgearLoggerFilenamePrefix->global,
										),
									);
		
		if($logFile) {
			
			$this->setLogFile($logFile);
			
		}
		
	}
	
	public function reset() {
		
		$this->_logFile = null;
		$this->_payload = null;
		$this->_message = null;
		$this->_briefBacktrace = null;
		$this->_fullBacktrace = null;
		$this->_fullBacktraceExport = null;
		$this->_dateTime = null;
		
		return $this;
		
	}
	
	public function setClientId($id = null) {
		
		$this->_appConfiguration = include APPLICATION_PATH . DIRECTORY_SEPARATOR . 'configs' . DIRECTORY_SEPARATOR . 'com-app-config.php';
		
		if(array_key_exists($id, $this->_appConfiguration["client_process"][APPLICATION_ENV])) {
			
			$this->_clientId = $id;
			
			$this->setLogFile(APPLICATION_PATH . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . 'logs' . DIRECTORY_SEPARATOR . $this->_configuration["logFileNamePrefix"]["client"] . $this->_clientId . ".log");
			
		}
		
		else {
			
			trigger_error("There is no Client defined with id '" . $id . "' within '" . APPLICATION_ENV . "' environment", E_USER_WARNING);
			
			$this->setLogFile(APPLICATION_PATH . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . 'logs' . DIRECTORY_SEPARATOR . $this->_configuration["logFileNamePrefix"]["global"] . ".log");
			
		}
		
		return $this;
		
	}
	
	public function setLogFile($logFile = null) {
		
		if(!is_file($logFile)) {
			
			$fileHandle = fopen($logFile, "x");
			
			fclose($fileHandle);
			
		}
		
		if(is_writable($logFile)) {
			
			$this->_logFile = $logFile;
			
		}
		else {
			
			trigger_error("Log file '$logFile' can not be accessed", E_USER_WARNING);
			
			//this will end up in an Error Controller, uncomment when changes are in place
			//throw new Exception("Log file '$logFile' can not be accessed.");
			
		}
		
		return $this;
		
	}
	
	public function log($payload = null, $backtrace = false, $email = false) {
		
		try{
			if(!$this->_logFile) {
				
				trigger_error("Log file undefined and no client id provided", E_USER_WARNING);
				
				//this will end up in an Error Controller, uncomment when changes are in place
				//throw new Exception("Log file undefined.");
				
			}
			
			if($backtrace) {
				
				$this->_fullBacktrace = debug_backtrace();
	
				if(isset($this->_fullBacktrace[1])) {
					
					$this->_briefBacktrace["proxy"] = $this->_fullBacktrace[1];
					
					if(count($this->_fullBacktrace) > 2) {
						
						$this->_briefBacktrace["init"] = $this->_fullBacktrace[count($this->_fullBacktrace) - 1];
						
					}
					
				}
				else {
					
					if(isset($this->_fullBacktrace[0])) {
						
						$this->_briefBacktrace = $this->_fullBacktrace[0];
						
					}
					else {
						
						$this->_briefBacktrace = (array) $this->_fullBacktrace;
						
					}
					
				}
				
			}
			else {
				
				$this->_briefBacktrace = null;
				$this->_briefBacktraceExport = null;
				$this->_fullBacktrace = null;
				$this->_fullBacktraceExport = null;
				
			}
				
			$type = strtolower(reset(explode(" ", gettype($payload))));
			
			$type{0} = strtoupper($type{0});
			
			$method = "_log" . $type;
			
			$this->_payload = $payload;
			
			if(method_exists($this, $method)) {
				
				$this->$method();
				
			}
			else {
				
				$this->_logSerialized($backtrace);
				
			}
			
			$response = ($email || !$this->_logFile) ? $this->_sendEmail() : $email;
			
			return $this;
			
		}catch(Exception $e){
		
			$this->_logLoggerError($e);
			
			return $this;
			
		}
		
	}
	
	protected function _logLoggerError(Exception $e){
	
		$logFile = APPLICATION_PATH . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . 'logs' . DIRECTORY_SEPARATOR .'ErrorInLogger.log';
		
		if(!is_file($logFile)) {
			
			$fileHandle = fopen($logFile, "x");
			
			fclose($fileHandle);
			
		}
		
		if(is_writable($logFile)) {
			
			error_log("Error in Logger, ".$e->getMessage(), 3, $logFile);
			
		}
		else {
			
			trigger_error("Log file '$logFile' can not be accessed", E_USER_WARNING);
			
			//this will end up in an Error Controller, uncomment when changes are in place
			//throw new Exception("Log file '$logFile' can not be accessed.");
			
		}
		
	}
	
	protected function _logBoolean() {
		
		$this->_message = "Boolean with a value of '" . ($this->_payload ? "true" : "false") . "' supplied to the log utility.\n";
		
		return $this->_log();
		
	}
	
	protected function _logInteger() {
		
		$this->_message = "Integer '" . $this->_payload . "' supplied to the log utility.\n";
		
		return $this->_log();
		
	}
	
	protected function _logDouble() {
		
		$this->_message = "Double '" . $this->_payload . "' supplied to the log utility.\n";
		
		$this->_log();
		
		return $this;
		
	}
	
	protected function _logString() {
		
		$this->_message = "String '" . $this->_payload . "' supplied to the log utility.\n";
		
		return $this->_log();
		
	}
	
	protected function _logArray() {
		
		$this->_message = "Array with keys (" . implode(", ", array_keys($this->_payload)) . ") supplied to the log utility.\n";
		
		return $this->_log();
		
	}
	
	protected function _logObject() {
		
		if($this->_payload instanceof Exception) {
			
			$this->_message = "An Excption object supplied to the log utility.\nException message:\n" . $this->_payload->getMessage() . "\n";
			
		}
		else {
			
			$this->_message = "Object of a class '" . get_class($this->_payload) . "' supplied to the log utility.\n";
			
		}
		
		return $this->_log();
	}
	
	protected function _logResource() {
		
		$this->_message = "Resource of a type '" . get_resource_type($this->_payload) . "' supplied to the log utility.\n";
		
		return $this->_log();
		
	}
	
	protected function _logNull() {
		
		$this->_message = "'NULL' supplied to the log utility.\n";
		
		return $this->_log();
		
	}
	
	protected function _logUnknown() {
		
		$this->_message = "'Unknown type' of entity supplied to the log utility.\n";
		
		return $this->_log();
		
	}
	
	protected function _logSerialized() {
		
		$this->_message = "Unsuported entity supplied to the log utility, it's serialized value is:\n" . serialize($this->_payload) . "\n";
		
		return $this->_log();
		
	}
	
	private function _log() {
		
		$this->_dateTime = new DateTime();
		
		if($this->_briefBacktrace) {
			
			$this->_message .= "\nBrief backtrace follows:\n" . $this->_exportBriefBacktrace() . "\n\n";
			
		}
		
		if($this->_logFile) {
		
			error_log($this->_message, 3, $this->_logFile);
			
		}
		
		return $this;
		
	}
	
	private function _exportFullBacktrace() {
		
		$this->_fullBacktraceExport = $this->_fullBacktrace;
		
		if(is_array($this->_fullBacktraceExport)) {
			
			self::_swapObjectToClassName($this->_fullBacktraceExport);
			
		}
		
		return print_r($this->_fullBacktraceExport, true);
		
	}
	
	private function _exportBriefBacktrace() {
		
		$this->_briefBacktraceExport = $this->_briefBacktrace;
		
		if(is_array($this->_briefBacktraceExport)) {
			
			self::_swapObjectToClassName($this->_briefBacktraceExport);
			
		}
		
		return print_r($this->_briefBacktraceExport, true);
		
	}
	
	protected function _sendEmail() {
		
		$email = new Zend_Mail('utf-8');
		
		$message  = "An event has been logged and required to send a notification.\n";
		
		if(!$this->_logFile) {
			
			$message .= "\nLog file seems to be undefined and no client id has been supplied to the logging utility, hence this e-mail message.\n\n";
			
		}
		
		$message .= "Current logging utility time is: " . $this->_dateTime->format("d.m.Y H:i:s") . " [" . date_default_timezone_get() . "]\n\n";
		$message .= "Logger Utility message:\n";
		$message .= $this->_message . "\n\n";
		$message .= "Below is a full debug backtrace of the event:\n\n";
		$message .= $this->_exportFullBacktrace() . "\n\n";
		$message .= "Debug backtrace end.\n";
		
		$email->setFrom($this->_configuration["sender"]["mail"], $this->_configuration["sender"]["name"]);
		$email->addTo($this->_configuration["recipient"]["mail"], $this->_configuration["recipient"]["name"]);
		$email->setSubject("VDT Logging Utility message");
		$email->setBodyText($message);
		
		return $email->send();
		
	}
	
}