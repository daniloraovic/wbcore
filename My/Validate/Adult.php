<?php

class My_Validate_Adult extends Zend_Validate_Abstract
{
    const DATE_INVALID = 'dateInvalid';

    protected $_messageTemplates = array(
        self::DATE_INVALID => "You must be over 21."
    );

    public function isValid($value)
    {
        $this->_setValue($value);
        
        $time = strtotime("-21 year", time());
        $date = date("Y-m-d", $time);

        // expecting $value to be YYYY-MM-DD
        if ($value > $date) {
            $this->_error(self::DATE_INVALID);
            return false;
        }

        return true;
    }
}