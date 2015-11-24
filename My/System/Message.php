<?php
class My_System_Message implements My_System_Message_Interface
{
    // Message arrays
    protected $_success   = array();
    protected $_attention = array();
    protected $_error = array();
    protected $_information = array();
    
    private static $_instance;
    

    protected $_lastMessageCode = null;

    /**
     * Create new message object
     *
     * @param $message
     * @param string $type
     */
    public function  __construct( $message = false, $type = self::DEFAULT_TYPE ) {
    	
        if($message != false) {
            $this->addMessage($message, $type);
        }
        
    }
    
    public static function getInstance( $message = false, $type = self::DEFAULT_TYPE ) {
    	
    	if (empty(self::$_instance)) {
			self::$_instance = new My_System_Message( $message, $type );
		}
		
		return self::$_instance;
    }
    
    /**
     * Add certain message defined by type
     *
     * @param string $message
     * @param string $type
     */
    public function addMessage($message, $type = self::DEFAULT_TYPE) {
        
        $this->_lastMessageCode = $type;
        switch($type) {
        	case self::SUCCESS: 
                $this->_success[] = $message;
                break;
            case self::ATTENTION:
                $this->_attention[] = $message;
                break;    
            case self::ERROR:
                $this->_error[] = $message;
                break;
            case self::INFORMATION: default:
                $this->_information[] = $message;
                break;
        }
    }
    
    public function addMessages(array $messages, $type) {

    	foreach($messages as $message) {
    		$this->addMessage($message, $type);
    	}
    	
    }
    
	public function addFormMessages(array $messages, $type) 
    {
    	
    	if(!empty($messages)) {
	    	foreach($messages as $elementMessages) {
	    		if(!empty($elementMessages)) {
	    			$this->addMessages($elementMessages, $type);
	    		}
	    	}
    	}
    	
    }
    
    /**
     * Get array of message objects
     *
     * @return array|null
     */
    public function getAll()
    {
        $return = array();
        
     	if($this->getInformations()) {
            foreach ($this->getInformations() as $message) {
                $obj = null;
                $obj->type = self::INFORMATION;
                $obj->message = $message;
                $return['information'][] = $obj;
            }
        }
        
     	if($this->getErrors()) {
            foreach ($this->getErrors() as $message) {
                $obj = new stdClass;
                $obj->type = self::ERROR;
                $obj->message = $message;
                $return['error'][] = $obj;
            }
        }
  
    	if($this->getAttentions()) {
            foreach ($this->getAttentions() as $message) {
                $obj = new stdClass;
                $obj->type = self::ATTENTION;
                $obj->message = $message;
                $return['attention'][] = $obj;
            }
        }

    	if($this->getSuccesses()) {
            foreach ($this->getSuccesses() as $message) {
                $obj = null;
                $obj->type = self::SUCCESS;
                $obj->message = $message;
                $return['success'][] = $obj;
            }
        }

        return count($return) ? $return : null;
    }
    
	/**
     * Get success messages
     *
     * @return array|null
     */
    public function getSuccesses()
    {
        return count($this->_success) ? $this->_success : null;
    }

    /**
     * Get attention messages
     *
     * @return array|null
     */
    public function getAttentions()
    {
        return count($this->_attention) ? $this->_attention : null;
    }

    /**
     * Get error messages
     *
     * @return array|null
     */
    public function getErrors()
    {
        return count($this->_error) ? $this->_error : null;
    }
    
	/**
     * Get information messages
     *
     * @return array|null
     */
    public function getInformations()
    {
        return count($this->_information) ? $this->_information : null;
    }

    /**
     * Get last added message code.
     *
     * @return int Message const
     */
    public function getMessageCode()
    {
        return $this->_lastMessageCode;
    }

   

    /**
     * Check if object has error messages.
     *
     * @return boolean
     */
    public function hasError()
    {
        return count($this->_error) ? true : false;
    }

    /**
     * Check if there is at least one message in
     * errors, warnings and other messages.
     *
     * @return boolean
     */
    public function hasMessages()
    {
        if(count($this->_error) && count($this->_message) && count($this->_warning)) return true;
        return false;
    }
}