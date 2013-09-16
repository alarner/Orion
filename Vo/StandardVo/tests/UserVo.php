<?php
require_once('Vo/StandardVo/StandardUserVo.php');

class UserVo extends \Orion\StandardUserVo {
	public function __construct(array $row = array()) {
		$this->name = 'users';
		$this->fields = array(
			'user_id' => new \Orion\ValidatorOptions('int'),
			'email' => new \Orion\ValidatorOptions('email'),
			'is_email_verified' => new \Orion\ValidatorOptions('boolean'),
			'is_account_disabled' => new \Orion\ValidatorOptions('boolean'),
			'last_login' => new \Orion\ValidatorOptions('date', array(), false),
			'date_created' => new \Orion\ValidatorOptions('date', array('set-on-create' => true), false)
		);
		$this->idFieldName = 'user_id';
		parent::__construct($row);
	}
}
?>