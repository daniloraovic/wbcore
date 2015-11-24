<?php 
class My_Validate_DateGreaterThanToday extends Zend_Validate_Abstract
{
	const DATE_INVALID = 'dateInvalid';

	protected $_messageTemplates = array(
			self::DATE_INVALID => "'%value%' is not greater than today"
	);

	public function isValid($value) {
		$this->_setValue($value);
		
		$value = date('Y-d-m', strtotime($value));

		$date = new Zend_Date($value);
		$date->addDay(1);
		$now = new Zend_Date();

		// expecting $value to be YYYY-MM-DD
		if ($now->isLater($date)) {
			$this->_error(self::DATE_INVALID);
			return false;
		}

		return true;
	}
}