<?php
require_once(dirname(__FILE__).'/../../../config.php');
set_include_path(get_include_path() . PATH_SEPARATOR . OrionConfig::$basePath);

require_once('Template/SimpleTemplate/SimpleTemplate.php');

class TemplateTest extends PHPUnit_Framework_TestCase {
	
	public function testBasicTemplating() {
		$view = new \Orion\SimpleTemplate('Template/SimpleTemplate/tests/TemplateTestHtml1.php', array('name' => 'Aaron'));
		$result = $view->render(true);

$expected = 
'<html>
<body>
Hey there! <b>Aaron</b>
</body>
</html>';

		$this->assertEquals($result, $expected);
	}

	public function testEmbeddedTemplating() {
		$view = new \Orion\SimpleTemplate(
			'Template/SimpleTemplate/tests/TemplateTestHtml2.php',
			array(
				'name' => 'Aaron',
				'embedded' => new \Orion\SimpleTemplate(
					'Template/SimpleTemplate/tests/TemplateTestHtml3.php',
					array(
						'number' => 5,
						'name' => 'Bob'
					)
				)
			)
		);

$expected = 
'<html>
<body>
Hey there! <b>Aaron</b><br />
this is some embedded text with the number 5<br />
other name = Bob<br /></body>
</html>';

		$result = $view->render(true);
		$this->assertEquals($result, $expected);
	}

	public function testInvalidFileTemplating() {
		
	}
}
?>