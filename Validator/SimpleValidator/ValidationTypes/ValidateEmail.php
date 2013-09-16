<?php
namespace Orion;
require_once('Validator/SimpleValidator/ValidationTypes/ValidationType.php');
class ValidateEmail extends ValidationType {
	const ERROR_MISSING_AT_SYMBOL 			= 0;
	const ERROR_LOCAL_PIECE_TOO_LONG	 	= 1;
	const ERROR_LOCAL_PIECE_TOO_SHORT	 	= 2;
	const ERROR_DOMAIN_TOO_LONG		 		= 3;
	const ERROR_DOMAIN_TOO_SHORT		 	= 4;
	const ERROR_LOCAL_START_INVALID			= 5;
	const ERROR_LOCAL_END_INVALID			= 6;
	const ERROR_LOCAL_HAS_CONSECUTIVE_DOTS	= 7;
	const ERROR_DOMAIN_HAS_INVALID_CHARS	= 8;
	const ERROR_DOMAIN_HAS_CONSECUTIVE_DOTS	= 9;
	const ERROR_LOCAL_PIECE_INVALID_CHARS	= 10;
	const ERROR_DOMAIN_INVALID				= 11;

	protected $type = 'email';
	protected $defaultErrorMessages = array(
		ValidateEmail::ERROR_MISSING_AT_SYMBOL => 'The email address must contain an @ symbol.',
		ValidateEmail::ERROR_LOCAL_PIECE_TOO_LONG => 'The local piece is too long.',
		ValidateEmail::ERROR_LOCAL_PIECE_TOO_SHORT => 'The local piece is too short.',
		ValidateEmail::ERROR_DOMAIN_TOO_LONG => 'The domain is too long.',
		ValidateEmail::ERROR_DOMAIN_TOO_SHORT => 'The domain is too short.',
		ValidateEmail::ERROR_LOCAL_START_INVALID => 'The local piece cannot start with a dot.',
		ValidateEmail::ERROR_LOCAL_END_INVALID => 'The local piece cannot end with a dot.',
		ValidateEmail::ERROR_LOCAL_HAS_CONSECUTIVE_DOTS => 'The local piece cannot have two or more consecutive dots.',
		ValidateEmail::ERROR_DOMAIN_HAS_INVALID_CHARS => 'The domain has invalid characters.',
		ValidateEmail::ERROR_DOMAIN_HAS_CONSECUTIVE_DOTS => 'The domain cannot have two or more consecutive dots.',
		ValidateEmail::ERROR_LOCAL_PIECE_INVALID_CHARS => 'The local piece has invalid characters.',
		ValidateEmail::ERROR_DOMAIN_INVALID => 'The email domain is invalid.'
	);
	
	public function validate() {
		$isValid = true;
		$atIndex = strrpos($this->data, "@");
		if($atIndex === false) {
			$isValid = false;
			return $this->getErrorResult(ValidateEmail::ERROR_MISSING_AT_SYMBOL);
		}
		else {
			$ip = false;
			$domain = substr($this->data, $atIndex+1);
			$local = substr($this->data, 0, $atIndex);
			$localLen = strlen($local);
			$domainLen = strlen($domain);
			if($localLen < 1) {
				$isValid = false;
				return $this->getErrorResult(
					ValidateEmail::ERROR_LOCAL_PIECE_TOO_SHORT
				);
			}
			else if($localLen > 64) {
				$isValid = false;
				return $this->getErrorResult(
					ValidateEmail::ERROR_LOCAL_PIECE_TOO_LONG
				);
			}
			else if($domainLen < 1) {
				$isValid = false;
				return $this->getErrorResult(
					ValidateEmail::ERROR_DOMAIN_TOO_SHORT
				);
			}
			else if($domainLen > 255) {
				$isValid = false;
				return $this->getErrorResult(
					ValidateEmail::ERROR_DOMAIN_TOO_LONG
				);
			}
			else if($local[0] == '.') {
				$isValid = false;
				return $this->getErrorResult(
					ValidateEmail::ERROR_LOCAL_START_INVALID
				);
			}
			else if($local[$localLen-1] == '.') {
				$isValid = false;
				return $this->getErrorResult(
					ValidateEmail::ERROR_LOCAL_END_INVALID
				);
			}
			else if(preg_match('/\\.\\./', $local)) {
				$isValid = false;
				return $this->getErrorResult(
					ValidateEmail::ERROR_LOCAL_HAS_CONSECUTIVE_DOTS
				);
			}
			else if(!preg_match('/^[A-Za-z0-9\\-\\.]+$/', $domain) && !preg_match('/^\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\]$/', $domain)) {
				$isValid = false;
				return $this->getErrorResult(
					ValidateEmail::ERROR_DOMAIN_HAS_INVALID_CHARS
				);
			}
			else if(preg_match('/\\.\\./', $domain)) {
				$isValid = false;
				return $this->getErrorResult(
					ValidateEmail::ERROR_DOMAIN_HAS_CONSECUTIVE_DOTS
				);
			}
			else if(!preg_match('/^(\\\\.|[A-Za-z0-9!#%&`_=\\/$\'*+?^{}|~.-])+$/', str_replace("\\\\","",$local))) {
				// character not valid in local part unless 
				// local part is quoted
				if(!preg_match('/^"(\\\\"|[^"])+"$/', str_replace("\\\\","",$local))) {
					$isValid = false;
					return $this->getErrorResult(
						ValidateEmail::ERROR_LOCAL_PIECE_INVALID_CHARS
					);
				}
			}
		}

		if($isValid && !preg_match('/^\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\]$/', $domain)) {
			$pieces = explode('.', $domain);
			if(count($pieces) > 1) {
				foreach($pieces as $p) {
					if(!preg_match('/^[a-z\d][a-z\d-]{0,62}$/i', $p) || preg_match('/-$/', $p)) {
						$isValid = false;
						return $this->getErrorResult(
							ValidateEmail::ERROR_DOMAIN_INVALID
						);
						break;
					}
				}

				// The last piece of a domain cannot be all digits
				if($isValid && preg_match('/^[0-9]+$/', $p)) {
					$isValid = false;
					return $this->getErrorResult(
						ValidateEmail::ERROR_DOMAIN_INVALID
					);
				}
			}
			else {
				$isValid = false;
				return $this->getErrorResult(
					ValidateEmail::ERROR_DOMAIN_INVALID
				);
			}
		}

		return new ValidatorResult();
	}
}
?>