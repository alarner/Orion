<?php
require_once('Vo/StandardVo/StandardVo.php');

class TestVo extends \Orion\StandardVo {
	public function __construct(array $row = array()) {
		$this->name = 'test';
		$this->fields = array(
			'test_id' => new \Orion\ValidatorOptions('int', array(), false),
			'email' => new \Orion\ValidatorOptions('email'),
			'password' => new \Orion\ValidatorOptions('length', array('min' => 7)),
			'sex' => new \Orion\ValidatorOptions('options', array('options' => array('male', 'female')), false),
			'date_created' => new \Orion\ValidatorOptions('date', array('set-on-create' => true), false),
			'date_updated' => new \Orion\ValidatorOptions('date', array('reset-on-update' => true), false)
		);
		$this->idFieldName = 'test_id';
		parent::__construct($row);
	}
}
?>