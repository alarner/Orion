<?php
namespace Orion;

Class ErrorVo {

	private $errorCode;
	private $unformattedErrorString;
	private $params;

	/*
	 * @param	$errorCode
	 *
	 * @param	$unformattedErrorString		A string that is used along with the
	 *			passed in parameters when the getErrorString method is called.
	 *
	 * @param	$params						Additional information that can be 
	 *			added to the error string.
	 */
	function __construct($errorCode, $unformattedErrorString, array $params) {
		$this->errorCode = $errorCode;
		$this->unformattedErrorString = $unformattedErrorString;
		$this->params = $params;
	}

	/*
	 * @return the error code.
	 */
	public function getErrorCode() {
		return $this->errorCode;
	}

	/*
	 * @return the formatted error string.
	 */
	public function getErrorString() {
		return vsprintf($this->unformattedErrorString, $this->params);
	}

	/*
	 * @return the data formatted as an array
	 */
	public function toArray() {
		return array(
			'error_code' => $this->errorCode,
			'unformatted_error_string' => $this->unformattedErrorString,
			'params' => $this->params
		);
	}
}
?>