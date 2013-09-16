<?php
namespace Orion;

/*
 * Requires nothing.
 */
interface iTemplate {

	/*
	 * @param	$templatePath	the file path to a template.
	 *
	 * @param	$params			paramters that should be passed into the 
	 *			template so that they can be used as variables.
	 */
	public function __construct($templatePath, array $params = array());

	/*
	 * Renders the template. Variables are prefixed with _orion_ to avoid symbol
	 * collision.
	 *
	 * @param	$return		if true: return result, otherwise echo result.
	 */
	public function render($return = false);
}
?>