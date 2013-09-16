<?php
namespace Orion;
require_once('Validator/SimpleValidator/ValidationTypes/ValidationType.php');
class ValidateDomain extends ValidationType {
	const ERROR_NOT_A_DOMAIN = 0;

	protected $type = 'domain';
	protected $defaultErrorMessages = array(
		ValidateDomain::ERROR_NOT_A_DOMAIN => 'The data is not a domain.',
	);
	
	public function validate() {
		$domain = (
			preg_match("/^([a-z\d](-*[a-z\d])*)(\.([a-z\d](-*[a-z\d])*))*$/i", $this->data) 	//valid chars check
			&& preg_match("/^.{1,253}$/", $this->data)											//overall length check
			&& preg_match("/^[^\.]{1,63}(\.[^\.]{1,63})*$/", $this->data)						//length of each label
		);
		if(!$domain) {
			return $this->getErrorResult(ValidateDomain::ERROR_NOT_A_DOMAIN);
		}
		
		return new ValidatorResult();
	}
}
?>