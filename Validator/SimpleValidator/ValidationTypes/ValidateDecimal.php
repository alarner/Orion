<?php
namespace Orion;
require_once('Validator/SimpleValidator/ValidationTypes/ValidationType.php');
class ValidateDecimal extends ValidationType {
	const ERROR_NOT_NUMBER = 0;

	protected $type = 'decimal';
	protected $defaultErrorMessages = array(
		ValidateDecimal::ERROR_NOT_NUMBER => 'Not a number.',
	);
	
	public function validate() {
		if(!is_numeric($this->data)) {
			return $this->getErrorResult(ValidateDecimal::ERROR_NOT_NUMBER);
		}
		
		return new ValidatorResult();
	}
}
?>