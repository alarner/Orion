<?php
namespace Orion;

class ValidatorResult {
	public $valid;
	public $errorCode;
	public $errorMessage;

	function __construct($valid = true, $errorCode = null, $errorMessage = null) {
		$this->valid = $valid;
		$this->errorCode = $errorCode;
		$this->errorMessage = $errorMessage;
	}
}