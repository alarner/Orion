<?php
namespace Orion;
require_once('Autoloader/iAutoloader.php');
require_once('Autoloader/SimpleAutoloader/SimpleAutoloaderException.php');
/*
 * No additional requirements.
 *
 * @see Autoloader/iAutoloader.php for documentation.
 */
class SimpleAutoloader implements iAutoloader {

	private $rules;

	public function __construct() {
		$this->rules = array();
		spl_autoload_register('Orion\SimpleAutoloader::autoloader');
	}

	public function addRule($regexPattern, $callback) {
		$rule = array(
			'regexPattern' => $regexPattern,
			'callback' => $callback
		);
		$this->rules[] = $rule;
	}

	private function autoloader($class) {
		$success = false;
		foreach($this->rules as $rule) {
			$matches = array();
			if(preg_match($rule['regexPattern'], $class, $matches)) {
				array_shift($matches);
				$path = $rule['callback']($matches);
				if(!@include_once($path)) {
					throw new SimpleAutoloaderException('Could not find file "'.$path.'" for class "'.$class.'"');
				}
				else {
					$success = true;
					break;
				}
			}
		}
	}
}