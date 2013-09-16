<?php
require_once(dirname(__FILE__).'/../../../config.php');
set_include_path(get_include_path() . PATH_SEPARATOR . OrionConfig::$basePath);

require_once('Vo/StandardVo/StandardVo.php');
require_once('Vo/StandardVo/StandardUserVo.php');
require_once('Vo/StandardVo/tests/TestVo.php');
class VoTest extends PHPUnit_Framework_TestCase {
	/**
	 * @expectedException			\Orion\StandardVoException
	 * @expectedExceptionCode		1
	 * @expectedExceptionMessage	Unexpected field name: foo.
	 */
	public function testSetNonexistantField() {
		$vo = new TestVo();
		$vo->set('foo', 'bar');
	}

	/**
	 * @expectedException			\Orion\StandardVoException
	 * @expectedExceptionCode		0
	 * @expectedExceptionMessage	Invalid field name: foo.
	 */
	public function testGetNonexistantField() {
		$vo = new TestVo();
		$vo->get('foo');
	}

	public function testGetUnsetField() {
		$vo = new TestVo();
		$this->assertEquals(null, $vo->get('email'));
	}

	/**
	 * @expectedException			\Orion\StandardVoException
	 * @expectedExceptionCode		0
	 * @expectedExceptionMessage	Invalid field email => aaron
	 */
	public function testSetInvalidField() {
		$vo = new TestVo();
		$vo->set('email', 'aaron');
	}

	public function testSetValidField() {
		$vo = new TestVo();
		$vo->set('email', 'aaron@gmail.com');
		$this->assertEquals('aaron@gmail.com', $vo->get('email'));
	}

	/**
	 * @expectedException			\Orion\StandardVoException
	 * @expectedExceptionCode		0
	 * @expectedExceptionMessage	Invalid field password => 123
	 */
	public function testInvalidInitialization() {
		$vo = new TestVo(array('email' => 'aaron@gmail.com', 'password' => '123'));
	}

	/**
	 * @expectedException			\Orion\StandardVoException
	 * @expectedExceptionCode		1
	 * @expectedExceptionMessage	Unexpected field name: pass.
	 */
	public function testInitializationWithNonexistantField() {
		$vo = new TestVo(array('email' => 'aaron@gmail.com', 'pass' => '123'));
	}

	public function testValidInitialization() {
		$vo = new TestVo(array('email' => 'aaron@gmail.com', 'password' => '1234567'));
		$this->assertEquals('aaron@gmail.com', $vo->get('email'));
		$this->assertEquals('1234567', $vo->get('password'));
		$this->assertEquals(null, $vo->get('sex'));
	}

	/**
	 * @expectedException			\Orion\StandardVoException
	 * @expectedExceptionCode		0
	 * @expectedExceptionMessage	Invalid field password => 123
	 */
	public function testInvalidSetMultiple() {
		$vo = new TestVo();
		$vo->setMultiple(array('email' => 'aaron@gmail.com', 'password' => '123'));
	}

	/**
	 * @expectedException			\Orion\StandardVoException
	 * @expectedExceptionCode		1
	 * @expectedExceptionMessage	Unexpected field name: pass.
	 */
	public function testSetMultipleWithNonexistantField() {
		$vo = new TestVo();
		$vo->setMultiple(array('email' => 'aaron@gmail.com', 'pass' => '123'));
	}

	public function testValidSetMultiple() {
		$vo = new TestVo();
		$vo->setMultiple(array('email' => 'aaron@gmail.com', 'password' => '1234567'));
		$this->assertEquals('aaron@gmail.com', $vo->get('email'));
		$this->assertEquals('1234567', $vo->get('password'));
		$this->assertEquals(null, $vo->get('sex'));
	}

	/*
	The following tests require this table.

	CREATE TABLE `test` (
	  `test_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
	  `email` varchar(128) DEFAULT NULL,
	  `password` varchar(128) DEFAULT NULL,
	  `sex` varchar(6) DEFAULT NULL,
	  `date_created` datetime DEFAULT NULL,
	  `date_updated` datetime DEFAULT NULL,
	  PRIMARY KEY (`test_id`)
	) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=latin1$$
	*/

	public function testSaveCreate() {
		$vo = new TestVo();
		$vo->setMultiple(array('email' => 'aaron@gmail.com', 'password' => '1234567'));
		$this->assertEquals('aaron@gmail.com', $vo->get('email'));
		$this->assertEquals('1234567', $vo->get('password'));
		$this->assertEquals(null, $vo->get('sex'));
		$this->assertEquals(false, intval($vo->get('test_id')) != 0); // Verify that the id is not set
		$vo->save();
		$this->assertEquals(true, intval($vo->get('test_id')) != 0); // Verify that the id is set
	}

	public function testSaveUpdate() {
		$vo = new TestVo();
		$vo->setMultiple(array('email' => 'aaron@gmail.com', 'password' => '1234567'));
		$this->assertEquals('aaron@gmail.com', $vo->get('email'));
		$this->assertEquals('1234567', $vo->get('password'));
		$this->assertEquals(null, $vo->get('sex'));
		$vo->save();
		$oldId = $vo->get('test_id');
		$vo->set('email', 'newemail@facebook.com');
		sleep(2);
		$vo->save();
		$this->assertEquals($oldId, $vo->get('test_id'));
	}
}
?>