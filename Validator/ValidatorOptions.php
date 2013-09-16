<?php
namespace Orion;

class ValidatorOptions {
	public $type;
	public $params;
	public $required;
	public $errorMessages;

	function __construct($type = null, $params = array(), $required = true, array $errorMessages = array()) {
		$this->type = $type;
		$this->params = $params;
		$this->required = $required;
		$this->errorMessages = $errorMessages;
	}
}