<?php
namespace Orion;
require_once('ErrorList/iErrorList.php');
require_once('ErrorList/ErrorVo.php');
require_once('ErrorList/SimpleErrorList/config.php');

/*
 * Requires:
 *	- Template
 */

class SimpleErrorList implements iErrorList {
	private $availableErrors;
	private $defaultTemplatePath;
	private $errors;
	
	/*
	 * @param	$availableErrors		an array keyed by the error code. The 
	 *			value of each array element is an unformatted string that can
	 *			take an array of arguments using the vsprintf php function.
	 *
	 * @param	$defaultTemplatePath	the file path to a template that  
	 *			describes how the list of errors should be displayed when the 
	 *			echoErrors method is called. The template should loop over a  
	 *			variable called $errors. The value of each element in the passed 
	 *			in $errors array is the text of the error to display.
	 */
	function __construct(array $availableErrors = array(), $defaultTemplatePath = 'ErrorList/SimpleErrorList/DefaultTemplate.php') {
		$this->availableErrors = $availableErrors;
		$this->defaultTemplatePath = $defaultTemplatePath;
		$this->errors = array();
	}

	/*
	 * @param	$errorCode
	 *
	 * @param	$params		an array of parameters that will get passed into the 
	 *			unformatted error string to product the formatted error string.
	 *
	 * @param	$key		a string whose purpose is to give some infomation 
	 *			about what the error is associated with. For example: on a 
	 *			registration form you may return several errors. One because the
	 *			username field was empty and one because the email address was
	 *			invalid. The keys for each of these errors could be 'username' 
	 *			and 'email' respectivly.
	 *
	 * @return void
	 */
	public function addError
	($errorCode, array $params = array(), $key = 'default') {
		if(!array_key_exists($key, $this->errors)) {
			$this->errors[$key] = array();
		}
		$this->errors[$key][] = new \Orion\ErrorVo(
			$errorCode,
			$this->getUnformattedErrorStringFromCode($errorCode),
			$params
		);
	}

	/*
	 * @param	$key		the key of the error string you want to get.
	 *
	 * @param	$index		the index of the error string you want to get.
	 *
	 * @return the error vo.
	 */
	public function getError($key = 'default', $index = 0) {
		if(!array_key_exists($key, $this->errors)) {
			return null;
		}

		if(count($this->errors[$key]) <= $index) {
			return null;
		}

		return $this->errors[$key][$index];
	}

	/*
	 * @return a multi dimensional array of errors (with error keys)
	 */
	public function getErrors($key = 'default', $index = 0) {
		return $this->errors;
	}
	
	/*
	 * @param	$key		the key of the error string you want to get.
	 *
	 * @param	$index		the index of the error string you want to get.
	 *
	 * @return the formatted error string.
	 */
	public function getErrorString($key = 'default', $index = 0) {
		$errorObject = $this->getError($key, $index);
		if($errorObject === null) {
			return '';
		}

		return $errorObject->getErrorString();
	}

	/*
	 * @param	$key		the key of the error string you want to get.
	 *
	 * @param	$index		the index of the error string you want to get.
	 *
	 * @return the formatted error string.
	 */
	public function getErrorCode($key = 'default', $index = 0) {
		$errorObject = $this->getError($key, $index);
		if($errorObject === null) {
			return -1;
		}

		return $errorObject->getErrorCode();
	}

	/*
	 * @param	$key		the key of the error string you want to echo.
	 *
	 * @param	$index		the index of the error string you want to echo.
	 */
	public function echoErrorString($key = 'default', $index = 0) {
		echo $this->getErrorString($key, $index);
	}

	/*
	 * @param	$key		the key of the errors that you want to echo.
	 */
	public function echoErrorsHtml($key = 'default', $templatePath = null) {
		echo $this->getErrorsHtml($key, $templatePath);
	}

	/*
	 * @param	$key		the key of the errors that you want to get.
	 */
	public function getErrorsHtml($key = 'default', $templatePath = null) {
		if(!array_key_exists($key, $this->errors)) {
			return '';
		}

		$errors = array();
		foreach($this->errors[$key] as $error) {
			$errors[] = $error->getErrorString();
		}

		if($templatePath == null) {
			$templatePath = $this->defaultTemplatePath;
		}

		$tpl = new SimpleErrorListConfig::$requires['Template']($templatePath, array('errors' => $errors));
		return $tpl->render(true);
	}

	/*
	 * Save the current list of errors to an array. Commonly you will pass in an
	 * element of the session global to pass errors between different page loads.
	 *
	 * @param	$array	The session object where the data should be
	 *			stored.
	 */
	public function saveToArray(&$array) {
		$array = array(
			'errors' => array(),
			'availableErrors' => $this->availableErrors
		);
		foreach($this->errors as $key => $errors) {
			$array['errors'][$key] = array();
			foreach($errors as $e) {
				$array['errors'][$key][] = $e->toArray();
			}
		}
	}

	/*
	 * Load errors from an array. Note: this will replace any existing 
	 * errors.
	 */
	public function loadFromArray(&$array) {
		if(is_array($array)) {
			$this->errors = array();
			$this->availableErrors = $array['availableErrors'];
			foreach($array['errors'] as $key => $errors) {
				foreach($errors as $e) {
					$this->addError($e['error_code'], $e['params'], $key);
				}
			}
			unset($array);
		}
	}

	/*
	 * @return true if there are one or more errors, else false.
	 */
	public function hasErrors() {
		return count($this->errors) > 0;
	}

	/*
	 * @return true if there are one or more errors, else false.
	 */
	public function keyHasErrors($key = 'default') {
		return array_key_exists($key, $this->errors);
	}

	/*
	 * Remove all errors for a specified key.
	 */
	public function clearKey($key = 'default') {
		if(array_key_exists($key, $this->errors)) {
			unset($this->errors[$key]);
		}
	}

	/*
	 * Remove all errors.
	 */
	public function clearAll() {
		$this->errors = array();
	}

	/*
	 * Gets an unformatted error string that can be passed into vsprintf
	 */
	private function getUnformattedErrorStringFromCode($errorCode) {
		if(!array_key_exists($errorCode, $this->availableErrors)) {
			echo '<pre>';
			echo $errorCode;
			print_r($this->availableErrors);
			print_r(debug_backtrace());
			echo '<pre>';
			throw new \Exception('Unknown error code: '.$errorCode);
		}
		return $this->availableErrors[$errorCode];
	}
}
?>