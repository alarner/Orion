<?php
namespace Orion;

class AuthRegisterResult {
	const AUTH_ERROR_INVALID_PARAMS = 1;
	const AUTH_ERROR_IDENTIFIER_ALREADY_EXISTS = 2;

	public $valid;
	public $errorCode;

	function __construct($valid = true, $errorCode = null) {
		$this->valid = $valid;
		$this->errorCode = $errorCode;
	}
}