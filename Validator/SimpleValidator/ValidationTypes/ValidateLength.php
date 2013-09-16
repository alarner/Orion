<?php
namespace Orion;
require_once('Validator/SimpleValidator/ValidationTypes/ValidationType.php');
class ValidateLength extends ValidationType {
	const ERROR_TOO_LONG = 0;
	const ERROR_TOO_SHORT = 1;

	protected $type = 'length';
	protected $defaultErrorMessages = array(
		ValidateLength::ERROR_TOO_LONG => 'The string is too long.',
		ValidateLength::ERROR_TOO_SHORT => 'The string is too short.'
	);
	
	public function validate() {
		// There are no length restrictions
		if(!is_array($this->options->params)) {
			return new ValidatorResult();
		}

		if(array_key_exists('min', $this->options->params)) {
			if(strlen($this->data) < intval($this->options->params['min'])) {
				return $this->getErrorResult(ValidateLength::ERROR_TOO_SHORT);
			}
		}

		if(array_key_exists('max', $this->options->params)) {
			if(strlen($this->data) > intval($this->options->params['max'])) {
				return $this->getErrorResult(ValidateLength::ERROR_TOO_LONG);
			}
		}
		
		return new ValidatorResult();
	}
}
?>