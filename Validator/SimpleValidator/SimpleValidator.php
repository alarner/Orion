<?php
namespace Orion;
require_once('Validator/SimpleValidator/config.php');
require_once('Validator/ValidatorOptions.php');
require_once('Validator/ValidatorResult.php');

class SimpleValidator {

	function __construct() {
	}

	public function validate($data, \Orion\ValidatorOptions &$options) {
		if($options->type !== null) {
			$className = $this->getClassNameFromType($options->type);
			require_once('Validator/SimpleValidator/ValidationTypes/'.$className.'.php');
			$className = '\\Orion\\'.$className;
			$obj = new $className();
			if($obj) {
				$obj->setOptions($options);
				$obj->setData($data);
				$obj->setValidator($this);
				return $obj->validate();
			}
			else {
				throw new \Exception('Invalid validation type "'.$options->type.'"');
			}
		}
		return new ValidatorResult();
	}

	private function getClassNameFromType($type) {
		$pieces = explode('-', $type);
		$return = 'Validate';
		foreach($pieces as $p) {
			$return .= ucfirst($p);
		}
		return $return;
	}
}