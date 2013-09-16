<?php
namespace Orion;
require_once('Validator/SimpleValidator/ValidationTypes/ValidationType.php');
class ValidateInt extends ValidationType {
	const ERROR_NOT_INT = 0;
	const ERROR_TOO_SMALL = 1;
	const ERROR_TOO_LARGE = 2;

	protected $type = 'int';
	protected $defaultErrorMessages = array(
		ValidateInt::ERROR_NOT_INT => 'The value is not an integer.',
		ValidateInt::ERROR_TOO_SMALL => 'The integer is too small.',
		ValidateInt::ERROR_TOO_LARGE => 'The integer is too large.'
	);
	
	public function validate() {
		$valid = (preg_match('/^[\-]?[0-9]+$/', $this->data) > 0);
		if($valid) {
			if(array_key_exists('min', $this->options->params)) {
				if(intval($this->data) < intval($this->options->params['min'])) {
					return $this->getErrorResult(ValidateInt::ERROR_TOO_SMALL);
				}
			}

			if(array_key_exists('max', $this->options->params)) {
				if(intval($this->data) > intval($this->options->params['max'])) {
					return $this->getErrorResult(ValidateInt::ERROR_TOO_LARGE);
				}
			}
		}
		else {
			return $this->getErrorResult(ValidateInt::ERROR_NOT_INT);
		}

		return new ValidatorResult();
	}
}
?>