<?php
namespace Orion;
require_once('Validator/SimpleValidator/ValidationTypes/ValidationType.php');
class ValidateBoolean extends ValidationType {
	const ERROR_NOT_A_BOOLEAN = 0;

	protected $type = 'boolean';
	protected $defaultErrorMessages = array(
		ValidateBoolean::ERROR_NOT_A_BOOLEAN => 'The data is not a boolean.',
	);
	
	public function validate() {
		if(!is_bool($this->data) && $this->data != 1 && $this->data != 0) {
			return $this->getErrorResult(ValidateBoolean::ERROR_NOT_A_BOOLEAN);
		}
		
		return new ValidatorResult();
	}
}
?>