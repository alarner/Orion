<?php
namespace Orion;
class Controller {
	protected $previousInput;
	protected $unescaped;
	protected $routeInfo;

	function __construct($routeInfo) {
		session_start();
		$this->routeInfo = $routeInfo;
		if(array_key_exists('_INPUT', $_SESSION)) {
			$this->previousInput = $_SESSION['_INPUT'];
			unset($_SESSION['_INPUT']);
		}
		else {
			$this->previousInput = array(
				'POST' => array(),
				'GET' => array()
			);
		}

		$this->unescaped = array(
			'GET' => $_GET,
			'POST' => $_POST,
		);

		$_GET = $this->cleanArray($_GET);
		$_POST = $this->cleanArray($_POST);
	}

	protected function redirect($url) {
		header('Location: '.$url);
		exit;
	}

	protected function saveInput() {
		$_SESSION['_INPUT'] = array(
			'GET' => $_GET,
			'POST' => $_POST
		);
	}

	protected function show404() {
		$tpl = new MVCConfig::$requires['Template'](MVCConfig::$view404);
		$tpl->render();
		exit();
	}

	protected function unescaped($type, $key) {
		return $this->unescaped[$type][$key];
	}

	protected function cleanArray($array) {
		$newArray = array();
		foreach($array as $key => $val) {
			$newKey = $this->cleanString($key);
			if(is_array($val)) {
				$newArray[$newKey] = $this->cleanArray($val);
			}
			else {
				$newArray[$newKey] = $this->cleanString($val);
			}
		}
		return $newArray;
	}

	protected function cleanString($string) {
		return htmlentities($string);
	}
}

?>