<?php
namespace Orion;
class Model {
	protected $db;

	function __construct() {
		$this->db = call_user_func(MVCConfig::$requires['Database']);
	}
}

?>