<?php
namespace Orion;

interface iAutoloader {
	/*
	 * Add an autoloader rule.
	 *
	 * @param 	$regexPattern 	A regex pattern that defines when the rule should be used.
	 *							If a class name matches multiple rules then the first rule
	 *							added will be used.
	 *
	 * @param 	$callback 		A function that returns the path to be included. The
	 *							function should take the following arguments:
	 * 								1. $matches - an array of values that were matched
	 *											  (by parentheses) in the regular expression.
	 *
	 * @return A string representing the path that should be auto loaded.
	 */
	public function addRule($regexPattern, $callback);
}