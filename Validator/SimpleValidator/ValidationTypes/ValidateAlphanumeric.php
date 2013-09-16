<?php
namespace Orion;
require_once('Validator/SimpleValidator/ValidationTypes/ValidationType.php');
class ValidateAlphanumeric extends ValidationType {

	const ERROR_INVALID = 0;

	protected $type = 'alphanumeric';
	protected $defaultErrorMessages = array(
		ValidateAlphanumeric::ERROR_INVALID => 'The string is not alphanumeric.'
	);
	
	public function validate() {
		$tmp = $this->data;
		$tmp = strtolower($tmp);
		for($i=0; $i<strlen($tmp); $i++) {
			$ord = ord($tmp[$i]);
			if($ord < 48 || ($ord > 57 && $ord < 97) || $ord > 122) {
				return $this->getErrorResult(ValidateAlphanumeric::ERROR_INVALID);
			}
		}
		return new ValidatorResult();
	}
}
?>