<?php
require_once(dirname(__FILE__).'/../../../config.php');
set_include_path(get_include_path() . PATH_SEPARATOR . OrionConfig::$basePath);

require_once('ErrorList/SimpleErrorList/SimpleErrorList.php');

class ErrorListTest extends PHPUnit_Framework_TestCase {
	
	public function testAddingErrorWithDefaults() {
		$errorList = new \Orion\SimpleErrorList(
			$this->getAvailableErrors(),
			'ErrorList/SimpleErrorList/DefaultTemplate.php'
		);

		$errorList->addError(1, array('fakefake'));
		$errorString = $errorList->getErrorString();

		$this->assertEquals($errorString, 'fakefake is not a valid username.');

		$errorString = $errorList->getErrorString('default');

		$this->assertEquals($errorString, 'fakefake is not a valid username.');
	}
	
	public function testAddingErrors() {
		$errorList = new \Orion\SimpleErrorList(
			$this->getAvailableErrors(),
			'ErrorList/SimpleErrorList/DefaultTemplate.php'
		);

		$errorList->addError(1, array('fakefake'), 'username');
		$errorList->addError(1, array('imnotreal'), 'username');
		
		$errorString1 = $errorList->getErrorString('username', 0);
		$errorString2 = $errorList->getErrorString('username', 1);

		$this->assertEquals($errorString1, 'fakefake is not a valid username.');

		$this->assertEquals($errorString2, 'imnotreal is not a valid username.');

		$this->assertEquals(true, $errorList->hasErrors());
		$this->assertEquals(false, $errorList->keyHasErrors('test'));
		$this->assertEquals(true, $errorList->keyHasErrors('username'));

		$errorList->clearAll();

		$this->assertEquals(false, $errorList->hasErrors());
		$this->assertEquals(false, $errorList->keyHasErrors('username'));
	}

	public function testAddingDifferentTypesOfErrors() {
		$errorList = new \Orion\SimpleErrorList(
			$this->getAvailableErrors(),
			'ErrorList/SimpleErrorList/DefaultTemplate.php'
		);

		$this->assertEquals(false, $errorList->hasErrors());

		$errorList->addError(1, array('fakefake'), 'username');

		$this->assertEquals(true, $errorList->hasErrors());

		$errorList->addError(2, array(), 'username');
		$errorList->addError(3, array(), 'test');
		$errorList->addError(4, array('unused'), 'test');
		$errorList->addError(5, array(1, 2, 'red', 'blue'), 'test');

		$this->assertEquals(true, $errorList->hasErrors());
		
		$errorString1 = $errorList->getErrorString('username', 0);
		$errorString2 = $errorList->getErrorString('username', 1);
		$errorString3 = $errorList->getErrorString('test', 0);
		$errorString4 = $errorList->getErrorString('test', 1);
		$errorString5 = $errorList->getErrorString('test', 2);

		$this->assertEquals($errorString1, 'fakefake is not a valid username.');
		$this->assertEquals($errorString2, 'The email field cannot be blank.');
		$this->assertEquals($errorString3, 'You cannot choose a date before today.');
		$this->assertEquals($errorString4, 'Please enter a password.');
		$this->assertEquals($errorString5, '1 fish, 2 fish, red fish, blue fish!');

		$errorList->clearKey('username');
		$this->assertEquals('', $errorList->getErrorString('username', 0));
		$this->assertEquals('You cannot choose a date before today.', $errorList->getErrorString('test', 0));
	}

	public function testEchoingErrors() {
		$errorList = new \Orion\SimpleErrorList(
			$this->getAvailableErrors(),
			'ErrorList/SimpleErrorList/DefaultTemplate.php'
		);

		$errorList->addError(1, array('fakefake'), 'test');
		$errorList->addError(2, array(), 'test');
		$errorList->addError(3, array(), 'test');
		$errorList->addError(4, array('unused'), 'test');
		$errorList->addError(5, array(1, 2, 'red', 'blue'), 'test');

$expected = 
'<ul>
	<li>fakefake is not a valid username.</li>
	<li>The email field cannot be blank.</li>
	<li>You cannot choose a date before today.</li>
	<li>Please enter a password.</li>
	<li>1 fish, 2 fish, red fish, blue fish!</li>
</ul>';
		
		$result = $errorList->getErrorsHtml('test');

		$this->assertEquals($expected, $result);
	}

	public function testSaveToArrayAndLoadFromArray() {
		$errorList = new \Orion\SimpleErrorList(
			$this->getAvailableErrors(),
			'ErrorList/SimpleErrorList/DefaultTemplate.php'
		);

		$errorList->addError(1, array('fakefake'), 'test');
		$errorList->addError(2, array(), 'test');
		$errorList->addError(3, array(), 'test');
		$errorList->addError(4, array('unused'), 'test');
		$errorList->addError(5, array(1, 2, 'red', 'blue'), 'test');

		$array = array();
		$errorList->saveToArray($array);
		$this->assertEquals(1, count($array));
		$this->assertEquals(5, count($array['test']));
		foreach($array['test'] as $error) {
			$this->assertInstanceOf('\Orion\ErrorVo', $error);
		}

		$newErrorList = new \Orion\SimpleErrorList(
			$this->getAvailableErrors(),
			'ErrorList/SimpleErrorList/DefaultTemplate.php'
		);

		$newErrorList->loadFromArray($array);

$expected = 
'<ul>
	<li>fakefake is not a valid username.</li>
	<li>The email field cannot be blank.</li>
	<li>You cannot choose a date before today.</li>
	<li>Please enter a password.</li>
	<li>1 fish, 2 fish, red fish, blue fish!</li>
</ul>';
		
		$result = $newErrorList->getErrorsHtml('test');

		$this->assertEquals($expected, $result);
	}

	private function getAvailableErrors() {
		return array(
			1 => '%s is not a valid username.',
			2 => 'The email field cannot be blank.',
			3 => 'You cannot choose a date before today.',
			4 => 'Please enter a password.',
			5 => '%d fish, %d fish, %s fish, %s fish!'
		);
	}
}
?>