<?php
namespace Orion;
require_once('Validator/SimpleValidator/ValidationTypes/ValidationType.php');
class ValidateArray extends ValidationType {
	const ERROR_NOT_ARRAY = 0;
	const ERROR_UNEXPECTED_KEY = 1;
	const ERROR_INVALID_KEY = 2;
	const ERROR_MISSING_KEY = 3;
	const ERROR_INVALID_INDEX = 4;

	protected $type = 'array';
	protected $defaultErrorMessages = array(
		ValidateArray::ERROR_NOT_ARRAY => 'The data is not an array.',
		ValidateArray::ERROR_UNEXPECTED_KEY => 'The key was not expected [%s].',
		ValidateArray::ERROR_INVALID_KEY => 'The key is invalid [%s].',
		ValidateArray::ERROR_MISSING_KEY => 'The key is required but missing [%s].',
		ValidateArray::ERROR_INVALID_INDEX => 'The index [%s] is invalid.',
	);
	
	public function validate() {
		if(!is_array($this->data)) {
			return $this->getErrorResult(ValidateArray::ERROR_NOT_ARRAY);
		}
		else if(!empty($this->options->params) && is_array($this->options->params)) {
			if(array_key_exists('keyedValidationOptions', $this->options->params)) {
				$validationArray =& $this->options->params['keyedValidationOptions'];
				
				foreach($this->data as $key => $val) {
					if(!array_key_exists($key, $validationArray)) {
						return $this->getErrorResult(ValidateArray::ERROR_UNEXPECTED_KEY, array($key));
					}
					else {
						$options =& $validationArray[$key];
						$result = $this->validator->validate($val, $options);
						if(!$result->valid) {
							return $this->getErrorResult(ValidateArray::ERROR_INVALID_KEY, array($key));
						}
					}
				}
				
				foreach($validationArray as $key => $opt) {
					if($opt->required) {
						if(!array_key_exists($key, $this->data)) {
							return $this->getErrorResult(ValidateArray::ERROR_MISSING_KEY, array($key));
						}
					}
				}
			}
			else if(array_key_exists('indexedValidationOptions', $this->options->params)) {
				$options =& $this->options->params['indexedValidationOptions'];
				foreach($this->data as $index => $val) {
					$result = $this->validator->validate($val, $options);
					if(!$result->valid) {
						return $this->getErrorResult(ValidateArray::ERROR_INVALID_INDEX, array($index));
					}
				}
			}
		}
		return new ValidatorResult();
	}
}
?>