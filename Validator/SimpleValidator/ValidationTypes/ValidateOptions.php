<?php
namespace Orion;
require_once('Validator/SimpleValidator/ValidationTypes/ValidationType.php');
class ValidateOptions extends ValidationType {
	const ERROR_PARAMETERS = 0;
	const ERROR_OPTIONS = 1;
	const ERROR_DATA = 2;

	protected $type = 'options';
	protected $defaultErrorMessages = array(
		ValidateOptions::ERROR_PARAMETERS => 'Invalid parameters.',
		ValidateOptions::ERROR_OPTIONS => 'No list of valid options specified in the parameters.',
		ValidateOptions::ERROR_DATA => 'Invalid data.'
	);
	
	public function validate() {
		if(!is_array($this->options->params) || !array_key_exists('options', $this->options->params)) {
			return $this->getErrorResult(ValidateOptions::ERROR_PARAMETERS);
		}
		else if(!is_array($this->options->params['options'])) {
			return $this->getErrorResult(ValidateOptions::ERROR_OPTIONS);
		}

		$result = in_array($this->data, $this->options->params['options']);
		if(!$result) {
			return $this->getErrorResult(ValidateOptions::ERROR_DATA);
		}

		return new ValidatorResult();
	}
}
?>