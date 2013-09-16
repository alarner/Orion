<?php
namespace Orion;

/*
 * Requires:
 *	- Template
 */

interface iErrorList {
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
	function __construct(array $availableErrors, $defaultTemplatePath);

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
	public function addError($errorCode, array $params, $key);

	/*
	 * @param	$key		the key of the error string you want to get.
	 *
	 * @param	$index		the index of the error string you want to get.
	 *
	 * @return the error vo.
	 */
	public function getError($key, $index);
	
	/*
	 * @param	$key		the key of the error string you want to get.
	 *
	 * @param	$index		the index of the error string you want to get.
	 *
	 * @return the formatted error string.
	 */
	public function getErrorString($key, $index = 0);

	/*
	 * @param	$key		the key of the error string you want to get.
	 *
	 * @param	$index		the index of the error string you want to get.
	 *
	 * @return the formatted error string.
	 */
	public function getErrorCode($key, $index);

	/*
	 * @param	$key		the key of the error string you want to echo.
	 *
	 * @param	$index		the index of the error string you want to echo.
	 */
	public function echoErrorString($key, $index);

	/*
	 * @param	$key		the key of the errors that you want to echo.
	 */
	public function echoErrorsHtml($key, $templatePath);

	/*
	 * @param	$key		the key of the errors that you want to get.
	 */
	public function getErrorsHtml($key, $templatePath);

	/*
	 * Save the current list of errors to an array. Commonly you will pass in an
	 * element of the session global to pass errors between different page loads.
	 *
	 * @param	$array	The session object where the data should be
	 *			stored.
	 */
	public function saveToArray(&$array);

	/*
	 * Load errors from an array. Note: this will replace any existing 
	 * errors.
	 */
	public function loadFromArray(&$array);

	/*
	 * @return true if there are one or more errors, else false.
	 */
	public function hasErrors();

	/*
	 * @return true if there are one or more errors, else false.
	 */
	public function keyHasErrors($key);

	/*
	 * Remove all errors for a specified key.
	 */
	public function clearKey($key);

	/*
	 * Remove all errors.
	 */
	public function clearAll();
}
?>