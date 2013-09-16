<?php
namespace Orion;

class AuthLoginResult {
	const AUTH_ERROR_INVALID_PARAMS = 1;
	const AUTH_ERROR_EXCESSIVE_FAILED_ATTEMPTS = 2;
	const AUTH_ERROR_USER_NOT_FOUND = 3;
	const AUTH_ERROR_INVALID_PASSWORD = 4;
	const AUTH_ERROR_USER_QUERY_FAILED = 5;
	const AUTH_ERROR_ACCOUNT_DISABLED = 6;
	const AUTH_ERROR_EMAIL_NOT_VERIFIED = 7;
	const AUTH_ERROR_OPTION_EXPIRED = 8;

	public $valid;
	public $errorCode;
	public $user;

	function __construct($valid = true, $errorCode = null, $user = array()) {
		$this->valid = $valid;
		$this->errorCode = $errorCode;
		$this->user = $user;
	}
}