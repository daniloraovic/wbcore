<?php
class My_Communication_SimpleEmail {
	
	protected $_senderEmail = null;
	protected $_senderName = null;
	protected $_recipientEmail = null;
	protected $_recipientName = null;
	protected $_subject = null;
	protected $_message = null;
	protected $_transport = null;
	protected $_bcc = null;
	protected $_attach = null;
	protected $_filename = null;
	protected $_filetype = null;
	protected $_cc = null;

	
	protected $_receipt = false;
	
	/**
	 * Class constructor.
	 * Requires an array argument with following properties: "sender_email", "sender_name", "recipient_email", "recipient_name", "subject", "message" 
	 * 
	 * @param array $array("sender_email", "sender_name", "recipient_email", "recipient_name", "subject", "message")
	 */
	public function __construct(array $array = array()) {
		
		$this->_initialize($array);
		
	}
	
	/**
	 * Method to dispatch correctly formated Email message
	 * Throws a general Exception in case of logically negative response
	 * 
	 * @throws Exception
	 */
	public function send($receipt = false) {
		
		$this->_receipt = ($receipt === true) ? true : false;
		
		$response = $this->_sendEmail();
		
		if(!$response) {
			
			throw new Exception("Error sending email with response: " . serialize($response));
			
		}
		
		return $response;
		
	}
	
	/**
	 * Method to initialize Email object attributes.
	 * Throws a general Exception in cases: passed array argument is an empty array, sender or recipient email address is not in correct format 
	 * 
	 * @param array $array
	 * @throws Exception
	 */
	protected function _initialize(array $array = array()) {
		
		if(empty($array)) {
			
			throw new Exception("Unable to initialize mail object, empty array passed as an argument");
			
		}
		
		if(!$this->_isValidEmail($array["sender_email"])) {
			
			throw new Exception("Sender email addres is not valid");
			
		}
		
		if(!$this->_isValidEmail($array["recipient_email"])) {
			
			throw new Exception("Recipient email addres is not valid");
			
		}
		
		$transportSettings = Zend_Registry::get('configuration')->simpleEmail;
		
		$this->_senderEmail = $array["sender_email"];
		$this->_senderName = $array["sender_name"];
		$this->_recipientEmail = $array["recipient_email"];
		$this->_recipientName = $array["recipient_name"];
		$this->_subject = $array["subject"];
		$this->_message = $array["message"];

		if(isset($array['path'])) {
			$file = fopen($array['path'], 'r');
			
			$this->_attach = $file;
			
			if( isset($array['filename']) ) {
				$this->_filename = $array['filename']; 
			} else {
				$time = time();				
				$this->_filename = "device-statistics-$time.csv";
			}
			
			if( isset($array['filetype']) ) {
				$this->_filetype = $array['filetype'];
			}
		}
		
		if(isset($array['cc'])) {
			$this->_cc = $array['cc'];			
		}
				
		if(isset($array['bcc'])) {
			$this->_bcc = $array['bcc'];
		}
		
		if( $transportSettings ) {
			
			$this->_transport = new Zend_Mail_Transport_Smtp($transportSettings->server, $transportSettings->config->toArray());
		}
	}
	
	/**
	 * Creates and sends an email using Zend_Mail object
	 */
	protected function _sendEmail() {
		
		$email = new Zend_Mail('utf-8');
		
		$this->_receipt && $email->addHeader("Disposition-Notification-To", $this->_senderEmail);		
		$email->setFrom($this->_senderEmail, $this->_senderName);
		$email->addTo($this->_recipientEmail, $this->_recipientName);
		$email->setSubject($this->_subject);
		$email->setBodyText($this->_message);
		
		if($this->_attach) {
			$at = new Zend_Mime_Part($this->_attach);
			$at->disposition = Zend_Mime::DISPOSITION_INLINE;
			$at->encoding    = Zend_Mime::ENCODING_BASE64;
			$at->filename    = $this->_filename;
			$email->addAttachment($at);
		}
		
		if ($this->_cc) {
			foreach ($this->_cc as $cc) {
				$email->addCc($cc);
			}
		}
		
		if ($this->_bcc) {
			if (is_array($this->_bcc)) {
			foreach ($this->_bcc as $bcc) {
				$email->addBcc($bcc);
			}
			} else {
				$email->addBcc($this->_bcc);
		}
		}
		
		$send = null;
		
		if( $this->_transport ) $send = $email->send( $this->_transport ); 
		else $send = $email->send();
		
		
		return $send;
		
	}
	
	/**
	 * Checks if the passed argument represents correctly formatted email address according to RFC2822
	 * 
	 * @param string $email
	 * @return boolean
	 */
	protected function _isValidEmail($email = null) {
		
		$validator = new Zend_Validate_EmailAddress();
		
		if ($validator->isValid($email)) {
			
			return true;
			
		}
		else {
			
			return false;
			
		}
		
	}
	
}
?>