<?php
namespace Orion;

class SimpleValidatorException extends \Exception {
	public $type = 'Unknown';
	public function __construct($message = null, $code = 0, Exception $previous = null, $type = 'Unknown') {
		$type = $type;
		parent::__construct($message, $code, $previous);
	}
}