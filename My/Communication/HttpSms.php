<?php
class My_Communication_HttpSms {
	protected $_channel = null;
	protected $_channelName = null;
	protected $_shortCode = null;
	protected $_content = null;
	protected $_dataType = null;
	protected $_premium = null;
	protected $_campaignId = null;
	protected $_MSISDN = null;
	protected $_sourceReference = null;
	protected $_smsGateway = null;
	protected $_post = null;
	protected $_request = null;
	protected $_response = null;
	protected $_session = null;
	protected $_parsedResponse = null;
	protected $_params = null;
	protected $_errorCode = array(
		"100" => "System Error, unable to process request",
		"101" => "Message Successfully sent",
		"102" => "Blank MSISDN, unable to send message",
		"103" => "Blank Channel, unable to send message",
		"104" => "Blank Shortcode, unable to send message",
		"105" => "Blank Content, unable to send message",
		"106" => "Blank or invalid Campaign, unable to send message",
		"107" => "Reference too long, unable to send message",
		"108" => "Source Reference too long, unable to send message",
		"109" => "Unknown Datatype, unable to send message",
		"110" => "Unknown Premium value, unable to send message",
		"111" => "Invalid Channel, unable to send message",
		"112" => "Invalid Shortcode value, unable to send message",
		"113" => "Invalid Channel/Shortcode combination, unable to send message",
		"114" => "Cannot send masked premium message, unable to send message",
		"115" => "WAP push content is too long, unable to send message",
		"116" => "Validity period is invalid",
		"117" => "Multipart value is invalid",
		"118" => "Multipart is only valid for text messages",
		"119" => "Multiple MSISDNs submitted without 'multitarget' enabled",
		"120" => "MSISDN is not valid, unable to send message",
		"121" => "Message content is too long, unable to send message"
	);
	public function __construct() {
		
	}
	
	public function setupSend($msdsdn = null, $content = null, $channel = null, $shortCode = null, $premium = "0", $dataType = null, $sourceReference = null, $campaignId = null) {
		if(is_null($msdsdn)) {
			throw new Exception("SMS Phone number missing");
		}
		if(is_null($content)) {
			throw new Exception("SMS Content missing");
		}
// 		if(is_null($channel)) {
// 			throw new Exception("SMS Channel missing");
// 		}
		if("1" == $premium && is_null($shortCode)) {
			throw new Exception("Short code is mandatory for premium SMS");
		}
		
		
		$this->_MSISDN = $msdsdn;
		$this->_channelName = $channel;
		$this->_content = $content;
		$this->_sourceReference = $sourceReference;
		
		if("1" == $premium) {
			$this->_premium = "1";
			$this->_shortCode = $shortCode;
		}
		if(!is_null($campaignId)) {
			$this->_campaignId = $campaignId;
		}
		if(!is_null($sourceReference)) {
			$this->_sourceReference = $sourceReference;
		}
		if(!is_null($dataType)) {
			$this->_dataType = $dataType;
		}
		
		return $this->_readConfiguration();
// 					 ->_makePost()
// 			 		 ->_makeHeaders();
	}
	
// 	public function receive($frontController = null) {
// 		if(! $frontController instanceof Zend_Controller_Front) {
// 			throw new \Exception("Instance of Zend front controller expected");
// 		}
// 		$this->_frontController = $frontController;
// 		return $this->_gatherRequest()
// 					 ->_getReceiveResponse();
// 	}
	
	public function send() {
		return $this->_dispatchRequest()
					 ->_parseSendResponse();
	}
	
// 	public function getResponse() {
// 		$response = array();
// 		foreach ($this->_errorCode as $code => $message) {
// 			if (stristr($this->_response, "\n$code\n")) {
// 				$response[intval($code)] = $message;
// 			}
// 		}
		
// 		if (!count($response)) {
// 			if (stristr($this->_response, "HTTP/1.1 401")) {
// 				$response[401] = "The server rejected your username/password (HTTP 401).";
// 			} else {
// 				$response[500] = "No recognised response code was returned by the server.";
// 			}
// 		}
// 		return $response;
// 	}
	
// 	public function getParsedResponse() {
// 		return $this->_parsedResponse;
// 	}
	
// 	/* fix */
// 	public function deliveryReport($receipt = null) {
// 		if(is_null($receipt)) {
// 			throw new \Exception("Receipt identifier missing");
// 		}
// 		$this->_setReceipt($receipt);
// 		return $this->_dispatchRequest()
// 					 ->_parseReportResponse();
// 	}	
	
	
	protected function _readConfiguration() {
		$activeSmsGateway = Zend_Registry::get('configuration')->smsGatewayActiveKey;
		$this->_smsGateway = Zend_Registry::get('configuration')->smsGateway->$activeSmsGateway;
// 		$this->_smsGateway = Zend_Registry::get('configuration')->smsGateway->{$this->_channelName};
// 		$this->_channel = $this->_smsGateway->defaultChannel;
// 		$this->_mask = $this->_smsGateway->mask;
		return $this;
	}
	
// 	protected function _makePost() {
// 		$this->_post = "";
// 		$this->_post .= "&MSISDN=$this->_MSISDN";
// 		$this->_post .= "&Channel=$this->_channel";
// 		$this->_post .= "&Content=$this->_content";
// 		$this->_post .= "&Mask=$this->_mask";
// 		$this->_post .= $this->_dataType ? "&DataType=$this->_dataType" : "";
// 		$this->_post .= $this->_campaignId ? "&CampaignID=$this->_campaignId" : "";
// 		$this->_post .= $this->_sourceReference ? "&SourceReference=$this->_sourceReference" : "";
// 		if("1" == $this->_premium) {
// 			$this->_post .= "&Premium=$this->_premium";
// 			$this->_post .= "&Shortcode=$this->_shortCode";
// 		}
// 		return $this;
// 	}
	
// 	protected function _makeHeaders() {
// 		$this->_request  = "POST " . $this->_smsGateway->url . " HTTP/1.0\n";
// 		$this->_request .= "Host: " . $this->_smsGateway->host . ":" . $this->_smsGateway->port . "\n";
// 		$this->_request .= "Authorization: Basic " . base64_encode($this->_smsGateway->username . ":" . $this->_smsGateway->password)."\n";
// 		$this->_request .= "Content-Type: application/x-www-form-urlencoded\n";
// 		$this->_request .= "Content-Length: " . strlen($this->_post)."\n";
// 		$this->_request .= "\n" . $this->_post;
// 		return $this;
// 	}
	
// 	protected function _gatherRequest() {
// 		$this->_params = array();
// 		$this->_params['reference'] = $this->_frontController->getRequest()->getParam('Reference');
// 		$this->_params['trigger'] = $this->_frontController->getRequest()->getParam('Trigger');
// 		$this->_params['shortcode'] = $this->_frontController->getRequest()->getParam('Shortcode');
// 		$this->_params['msisdn'] = $this->_frontController->getRequest()->getParam('MSISDN');
// 		$this->_params['content'] = $this->_frontController->getRequest()->getParam('Content');
// 		$this->_params['channel'] = $this->_frontController->getRequest()->getParam('Channel');
// 		$this->_params['data_type'] = $this->_frontController->getRequest()->getParam('DataType');
// 		$this->_params['date_received'] = $this->_frontController->getRequest()->getParam('DateReceived');
// 		$this->_params['campaign_id'] = $this->_frontController->getRequest()->getParam('CampaignID');
// 		return $this;
// 	}
	
// 	protected function _gatherReceipt() {
// 		$this->_params = array();
// 		$this->_params['reference'] = $this->_frontController->getRequest()->getParam('Reference');
// 		$this->_params['date_received'] = $this->_frontController->getRequest()->getParam('DateReceived');
// 		$this->_params['status'] = $this->_frontController->getRequest()->getParam('Status');
// 		return $this;
// 	}
	
// 	protected function _getReceiveResponse() {
// 		return $this->_params;
// 	}

	protected function _dispatchRequest() {
		$baseurl =$this->_smsGateway->protocol . '://' . $this->_smsGateway->host;
		
		$user = $this->_smsGateway->username;
		$pass = $this->_smsGateway->password;
		$sender = $this->_smsGateway->sender;		
		$to = preg_replace('/\D/', '', $this->_MSISDN);;
		$text = urlencode($this->_content);
		
		if(!is_null($this->_dataType)) {
			$url = "$baseurl?user=$user&password=$pass&sender=$sender&SMSText=$text&GSM=$to&type=longSMS&datacoding=8&output=json";
		}
		else
		{
			$url = "$baseurl?user=$user&password=$pass&sender=$sender&SMSText=$text&GSM=$to&type=longSMS&output=json";
		}
		
		$response = file($url);
		$this->_response = $response[1];
				
		return $this;
		
	}	
	
	protected function _parseSendResponse() {		
		$resp = substr($this->_response, 1, -2);
		$firstExplode = explode(",",$resp);
		$statusExplode = explode(":",$firstExplode[0]);
		$messageExplode = explode(":",$firstExplode[1]);
		
		$response[substr($statusExplode[0], 1, -1)] = substr($statusExplode[1], 1, -1);
		$response[substr($messageExplode[0], 1, -1)] = substr($messageExplode[1], 1, -1);		
		
		return $response;		
	}
	
	protected function _dispatchRequestClicatell() {	
		
		$baseurl =$this->_smsGateway->protocol . '://' . $this->_smsGateway->host;
		
		$user = $this->_smsGateway->username;
		$pass = $this->_smsGateway->password;
		$api = $this->_smsGateway->api_id;
		$url = "$baseurl/http/auth?user=$user&password=$pass&api_id=$api";		
		$ret = file($url);
		
		$sess = explode(":",$ret[0]);
		if ($sess[0] == "OK") {
		
			$sess_id = trim($sess[1]); // remove any whitespace
			$this->_session = $sess_id;
			//SLANJE
			$url = "$baseurl/http/sendmsg?session_id=$sess_id&to=$this->_MSISDN&text=$this->_content&callback=3&deliv_ack=1";		

			$this->_response = file($url);
						
			return $this;
    	} else {
        	$response = "Authentication failure: ". $ret[0];
    	}
// 		$this->_response = "";
// 		if(!$fp = fsockopen ($this->_smsGateway->protocol . '://' . $this->_smsGateway->host, $this->_smsGateway->port, $errno, $errstr, 30))
// 				throw new \Exception("Couldn't establish SMS Gateway connection");
// 		fputs ($fp, $this->_request);
// 		while (!feof($fp)) {
// 			$this->_response .= fgets ($fp, 128);
// 		}
// 		fclose ($fp);
		return $response;
	}
	
	protected function _parseSendResponseClicatell() {
		$send = explode(":",array_pop($this->_response));
		if ($send[0] == "ID") {
			$response['id'] = $send[1];
			$response['session'] = $this->_session;
		} else {
			throw new Exception("Send message failed ".$send[1]);
		}
// 		$this->_parsedResponse = array();
// 		$parts = explode("\n", $this->_response);
// 		$this->_parsedResponse['reference'] = array_pop($parts);
// 		$this->_parsedResponse['message'] = array_pop($parts);
// 		$this->_parsedResponse['code'] = array_pop($parts);
		return $response;
	}
}