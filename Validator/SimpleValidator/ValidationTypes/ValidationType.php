<?php
namespace Orion;
abstract class ValidationType {
	protected $type;
	protected $options;
	protected $data;
	protected $availableErrors = array();
	protected $validator;
	protected $defaultErrorMessages = array();

	public final function setOptions(&$options) {
		if($options->type != $this->type) {
			throw new \Exception('Passed in options has invalid type. Type was "'.$options->type.'" but expected "'.$this->type.'"');
		}
		$this->options =& $options;
	}

	public final function setData(&$data) {
		$this->data =& $data;
	}

	public final function setValidator(&$validator) {
		$this->validator =& $validator;
	}

	public final function getErrorResult($errorCode, $params = array()) {
		$message = 'An unknown error occured.';
		if(array_key_exists($errorCode, $this->options->errorMessages)) {
			$message = $this->options->errorMessages[$errorCode];
		}
		else if(array_key_exists($errorCode, $this->defaultErrorMessages)) {
			$message = $this->defaultErrorMessages[$errorCode];
		}
		$message = vsprintf($message, $params);
		return new ValidatorResult(false, $errorCode, $message);
	}

	abstract public function validate();
}
?>