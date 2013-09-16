<?php
namespace Orion;
class LastPost {
	public $post = array();

	public function __construct() {
		if(array_key_exists('_orion_last_post', $_SESSION)) {
			$this->post = $_SESSION['_orion_last_post'];
		}
		$_SESSION['_orion_last_post'] = $_POST;
	}

	public function get($key=null, $default='') {
		if(is_null($key)) {
			return $this->post;
		}
		else if(array_key_exists($key, $this->post)) {
			return $this->post[$key];
		}
		else {
			return $default;
		}
	}

	public function show($key, $default='') {
		echo $this->get($key, $default);
	}

	public function showIfKeyExists($key, $showValue) {
		if(array_key_exists($key, $this->post) && $this->post[$key]) {
			echo $showValue;
		}
	}

	public function sike($key, $showValue) {
		$this->showIfKeyExists($key, $showValue);
	}
}
?>