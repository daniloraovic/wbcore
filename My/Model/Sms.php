<?php
abstract class My_Model_Sms
{
	protected $_content = null;
	protected $_phoneNumber = null;
	protected $_smsChannel = null;
	protected $_campaignId = null;
	protected $_dataType = null;
	public function __construct($smsChannel = null, $phoneNumber = null, $campaignId = null) {
		$this->_httpSms = new My_Communication_HttpSms();
		$this->setSmsChannel($smsChannel)
			 ->setPhoneNumber($phoneNumber)
			 ->setCampaignId($campaignId);
	}
	
	public function getContent() {
		return $this->_content;
	}
	
	public function setSmsChannel($smsChannel = null, $phoneNumber = null) {
		$this->_smsChannel = $smsChannel;
		return $this;
	}
	
	public function setPhoneNumber($phoneNumber = null) {
		$this->_phoneNumber = $phoneNumber;
		return $this;
	}
	
	public function setCampaignId($campaignId = null) {
		$this->_campaignId = $campaignId;
		return $this;
	}
	
	public function setDataType($dataType = null) {
		$this->_dataType = $dataType;
		return $this;
	}
	
	public function configureMessage($template = null, $templateData = null) {
		$this->_template = $template;
		$this->_data = $templateData;
		return $this;
	}
	
	public function sendSms() {
		$sms = new My_Communication_HttpSms();
		$sms->setupSend($this->_phoneNumber, $this->_content, null, null, "0", $this->_dataType, null, null);
		return $sms->send();
	}
	
	abstract protected function _loadTemplate();
}