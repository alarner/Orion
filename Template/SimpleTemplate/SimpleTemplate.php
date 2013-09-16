<?php
namespace Orion;
require_once('Template/iTemplate.php');
require_once('Template/SimpleTemplate/SimpleTemplateException.php');

/*
 * No additional requirements.
 *
 * @see Template/iTemplate.php for documentation.
 */
class SimpleTemplate implements iTemplate {
	
	private $templatePath;
	private $params;

	public function __construct($templatePath, array $params = array()) {
		$this->templatePath = $templatePath;
		$this->params = $params;
	}

	public function render($return = false) {
		if(!file_exists(stream_resolve_include_path($this->templatePath))) {
			throw new SimpleTemplateException('Error loading template file: '.$this->templatePath);
		}

		$_orion_return = $return;
		
		if($_orion_return) {
			ob_start();
		}
		foreach($this->params as $_orion_key => $_orion_not_ued) {
			$$_orion_key =& $this->params[$_orion_key];
		}

		include($this->templatePath);
		if($_orion_return) {
			$return = ob_get_contents();
			ob_end_clean();
			return $return;
		}
	}
}
?>