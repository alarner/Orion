<?php
require_once('ErrorVo.php');
set_include_path(get_include_path() . PATH_SEPARATOR . '/home/alarner/orion');
class ErrorVoTest extends PHPUnit_Framework_TestCase {
	
	public function testErrorVoStringFormatting() {
		$evo = new \Orion\ErrorVo(
			1,
			'The username "%s" is not valid.', 
			array('alarner')
		);

		$result = $evo->getErrorString();
		$expected = 'The username "alarner" is not valid.';
		$this->assertEquals($result, $expected);
	}
}
?>