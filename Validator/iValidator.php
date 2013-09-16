<?php
namespace Orion;

interface iValidator {

	public function __construct();

	/*
	 * @param	$data		The data to validate.
	 *
	 * @param	$options	Options associated with the validation:
	 *							- validation type
	 *							- additional validation parameters
	 *							- whether or not the data is required
	 *
	 * @return	a ValidatorResult object.
	 */
	public function validate($data, \Orion\iValidatorOptions &$options);
}