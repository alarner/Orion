<?php
namespace Orion;
require_once('Validator/SimpleValidator/ValidationTypes/ValidationType.php');
class ValidateDate extends ValidationType {
	const ERROR_NOT_REAL_DATE = 0;
	const ERROR_INVALID_FORMAT = 1;

	protected $type = 'date';
	protected $defaultErrorMessages = array(
		ValidateDate::ERROR_NOT_REAL_DATE => 'Not a real date [%s].',
		ValidateDate::ERROR_INVALID_FORMAT => 'Invalid formatted date [%s].'
	);
	
	public function validate() {
		$date = explode(' ', strval($this->data));
		$pieces = explode('-', $date[0]);
		if(count($pieces) == 3) {
			if(!checkdate($pieces[1], $pieces[2], $pieces[0])) {
				$this->getErrorResult(ValidateDate::ERROR_NOT_REAL_DATE, array($this->data));
			}
		}
		else {
			$this->getErrorResult(ValidateDate::ERROR_INVALID_FORMAT, array($this->data));
		}
		return new ValidatorResult();
	}
}
?>