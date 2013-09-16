<?php
namespace Test;

class TestClass {
	public function a() {
		return 1;
	}

	public function b($p1) {
		return $p1;
	}

	public function c($p1, $p2) {
		return $p1.$p2;
	}

	public static function d() {
		return 4;
	}

	public static function e($p1) {
		return $p1;
	}
	
	public static function f($p1, $p2) {
		return $p1.$p2;
	}
}

function g() {
	return 7;
}

function h($p1) {
	return $p1;
}

function i($p1, $p2) {
	return $p1.$p2;
}
?>