<?php
require_once(dirname(__FILE__).'/../../../config.php');
set_include_path(get_include_path() . PATH_SEPARATOR . OrionConfig::$basePath);

require_once('Router/SimpleRouter/SimpleRouter.php');
require_once('Router/SimpleRouter/tests/TestNamespace.php');

\Orion\SimpleRouterConfig::$defaultClass = null;
\Orion\SimpleRouterConfig::$defaultFunction = null;
\Orion\SimpleRouterConfig::$defaultNamespace = null;

class RouterTest extends PHPUnit_Framework_TestCase {
	
	public function testFunctionNoParams() {
		$uri = 'test1';

		$router = new \Orion\SimpleRouter();
		$router->addRoute('test1/', 'g');

		$result = $router->getRoute($uri);

		$this->assertEquals('test1', $result['uri']);
		$this->assertEquals('g', $result['routeInfoObj']->function);
		$this->assertEquals(null, $result['routeInfoObj']->class);
		$this->assertEquals(null, $result['routeInfoObj']->namespace);

		$result = $router->executeRoute($uri);

		$this->assertEquals(7, $result);

	}

	public function testFunctionNoParamsTrailingSlash() {
		$uri = 'test1/';

		$router = new \Orion\SimpleRouter();
		$router->addRoute('test1', 'g');

		$result = $router->getRoute($uri);

		$this->assertEquals('test1', $result['uri']);
		$this->assertEquals('g', $result['routeInfoObj']->function);
		$this->assertEquals(null, $result['routeInfoObj']->class);
		$this->assertEquals(null, $result['routeInfoObj']->namespace);

		$result = $router->executeRoute($uri);

		$this->assertEquals(7, $result);

	}

	public function testFunctionOneParam() {
		$uri = 'test1/pie';

		$router = new \Orion\SimpleRouter();
		$router->addRoute('test1/(:any)', 'h');
		$router->addRoute('test1/', 'g');

		$result = $router->getRoute($uri);

		$this->assertEquals('test1/pie', $result['uri']);
		$this->assertEquals('h', $result['routeInfoObj']->function);
		$this->assertEquals(null, $result['routeInfoObj']->class);
		$this->assertEquals(null, $result['routeInfoObj']->namespace);

		$result = $router->executeRoute($uri);

		$this->assertEquals('pie', $result);
	}

	public function testFunctionOneParamTrailingSlash() {
		$uri = 'test1/pie/';

		$router = new \Orion\SimpleRouter();
		$router->addRoute('test1/(:any)', 'h');
		$router->addRoute('test1/', 'g');

		$result = $router->getRoute($uri);

		$this->assertEquals('test1/pie', $result['uri']);
		$this->assertEquals('h', $result['routeInfoObj']->function);
		$this->assertEquals(null, $result['routeInfoObj']->class);
		$this->assertEquals(null, $result['routeInfoObj']->namespace);

		$result = $router->executeRoute($uri);

		$this->assertEquals('pie', $result);
	}

	public function testFunctionTwoParams() {
		$uri = 'test1/hello/world';

		$router = new \Orion\SimpleRouter();
		$router->addRoute('test1/(:any)/(:any)', 'i');
		$router->addRoute('test1/(:any)', 'h');
		$router->addRoute('test1/', 'g');

		$result = $router->getRoute($uri);

		$this->assertEquals('test1/hello/world', $result['uri']);
		$this->assertEquals('i', $result['routeInfoObj']->function);
		$this->assertEquals(null, $result['routeInfoObj']->class);
		$this->assertEquals(null, $result['routeInfoObj']->namespace);

		$result = $router->executeRoute($uri);

		$this->assertEquals('helloworld', $result);
	}

	public function testMethodNoParams() {
		$uri = 'TestObject/test1/';

		$router = new \Orion\SimpleRouter();
		$router->addRoute('TestObject/test1/', 'a', 'TestClass');
		$router->addRoute('test1/(:any)/(:any)', 'i');
		$router->addRoute('test1/(:any)', 'h');
		$router->addRoute('test1/', 'g');

		$result = $router->getRoute($uri);

		$this->assertEquals('TestObject/test1', $result['uri']);
		$this->assertEquals('a', $result['routeInfoObj']->function);
		$this->assertEquals('TestClass', $result['routeInfoObj']->class);
		$this->assertEquals(null, $result['routeInfoObj']->namespace);

		$result = $router->executeRoute($uri);

		$this->assertEquals(1, $result);
	}

	public function testMethodOneParam() {
		$uri = 'TestObject/test1/first param';

		$router = new \Orion\SimpleRouter();
		$router->addRoute('TestObject/test1/(:nonum)/', 'b', 'TestClass');
		$router->addRoute('TestObject/test1/', 'a', 'TestClass');
		$router->addRoute('test1/(:any)/(:any)', 'i');
		$router->addRoute('test1/(:any)', 'h');
		$router->addRoute('test1/', 'g');

		$result = $router->getRoute($uri);

		$this->assertEquals('TestObject/test1/first param', $result['uri']);
		$this->assertEquals('b', $result['routeInfoObj']->function);
		$this->assertEquals('TestClass', $result['routeInfoObj']->class);
		$this->assertEquals(null, $result['routeInfoObj']->namespace);

		$result = $router->executeRoute($uri);

		$this->assertEquals('first param', $result);
	}

	public function testMethodTwoParams() {
		$uri = 'TestObject/test1/first param/123/';

		$router = new \Orion\SimpleRouter();
		$router->addRoute('TestObject/test1/(:nonum)/(:nonum)', 'notused', 'TestClass');
		$router->addRoute('TestObject/test1/(:nonum)/(:num)', 'c', 'TestClass');
		$router->addRoute('TestObject/test1/(:nonum)/', 'b', 'TestClass');
		$router->addRoute('TestObject/test1/', 'a', 'TestClass');
		$router->addRoute('test1/(:any)/(:any)', 'i');
		$router->addRoute('test1/(:any)', 'h');
		$router->addRoute('test1/', 'g');

		$result = $router->getRoute($uri);

		$this->assertEquals('TestObject/test1/first param/123', $result['uri']);
		$this->assertEquals('c', $result['routeInfoObj']->function);
		$this->assertEquals('TestClass', $result['routeInfoObj']->class);
		$this->assertEquals(null, $result['routeInfoObj']->namespace);

		$result = $router->executeRoute($uri);

		$this->assertEquals('first param123', $result);
	}

	public function testStaticMethodNoParams() {
		$uri = 'TestObject/test2/';

		$router = new \Orion\SimpleRouter();
		$router->addRoute('TestObject/test1/', 'a', 'TestClass');
		$router->addRoute('TestObject/test2/', 'd', 'TestClass', null, true);
		$router->addRoute('test1/(:any)/(:any)', 'i');
		$router->addRoute('test1/(:any)', 'h');
		$router->addRoute('test1/', 'g');

		$result = $router->getRoute($uri);

		$this->assertEquals('TestObject/test2', $result['uri']);
		$this->assertEquals('d', $result['routeInfoObj']->function);
		$this->assertEquals('TestClass', $result['routeInfoObj']->class);
		$this->assertEquals(null, $result['routeInfoObj']->namespace);

		$result = $router->executeRoute($uri);

		$this->assertEquals(4, $result);
	}

	public function testStaticMethodOneParam() {
		$uri = 'TestObject/test2/abc';

		$router = new \Orion\SimpleRouter();
		$router->addRoute('TestObject/test1/(:nonum)/', 'b', 'TestClass');
		$router->addRoute('TestObject/test2/(:alpha)/', 'e', 'TestClass', null, true);
		$router->addRoute('TestObject/test1/', 'a', 'TestClass');
		$router->addRoute('test1/(:any)/(:any)', 'i');
		$router->addRoute('test1/(:any)', 'h');
		$router->addRoute('test1/', 'g');

		$result = $router->getRoute($uri);

		$this->assertEquals('TestObject/test2/abc', $result['uri']);
		$this->assertEquals('e', $result['routeInfoObj']->function);
		$this->assertEquals('TestClass', $result['routeInfoObj']->class);
		$this->assertEquals(null, $result['routeInfoObj']->namespace);

		$result = $router->executeRoute($uri);

		$this->assertEquals('abc', $result);
	}

	public function testStaticMethodTwoParams() {
		$uri = 'TestObject/test2/first param/abc123/';

		$router = new \Orion\SimpleRouter();
		$router->addRoute('TestObject/test1/(:nonum)/(:num)', 'c', 'TestClass');
		$router->addRoute('TestObject/test2/(:nonum)/(:alnum)', 'f', 'TestClass', null, true);
		$router->addRoute('TestObject/test1/(:nonum)/(:nonum)', 'notused', 'TestClass');
		$router->addRoute('TestObject/test1/(:nonum)/', 'b', 'TestClass');
		$router->addRoute('TestObject/test1/', 'a', 'TestClass');
		$router->addRoute('test1/(:any)/(:any)', 'i');
		$router->addRoute('test1/(:any)', 'h');
		$router->addRoute('test1/', 'g');

		$result = $router->getRoute($uri);

		$this->assertEquals('TestObject/test2/first param/abc123', $result['uri']);
		$this->assertEquals('f', $result['routeInfoObj']->function);
		$this->assertEquals('TestClass', $result['routeInfoObj']->class);
		$this->assertEquals(null, $result['routeInfoObj']->namespace);

		$result = $router->executeRoute($uri);

		$this->assertEquals('first paramabc123', $result);
	}

	public function testNamespacedFunctionNoParams() {
		$uri = 'test1';

		$router = new \Orion\SimpleRouter();
		$router->addRoute('test1/', 'g', null, 'Test');

		$result = $router->getRoute($uri);

		$this->assertEquals('test1', $result['uri']);
		$this->assertEquals('g', $result['routeInfoObj']->function);
		$this->assertEquals(null, $result['routeInfoObj']->class);
		$this->assertEquals('Test', $result['routeInfoObj']->namespace);

		$result = $router->executeRoute($uri);

		$this->assertEquals(7, $result);

	}

	public function testNamespacedFunctionOneParam() {
		$uri = 'test1/pie';

		$router = new \Orion\SimpleRouter();
		$router->addRoute('test1/(:any)', 'h', null, 'Test');
		$router->addRoute('test1/', 'g', null, 'Test');

		$result = $router->getRoute($uri);

		$this->assertEquals('test1/pie', $result['uri']);
		$this->assertEquals('h', $result['routeInfoObj']->function);
		$this->assertEquals(null, $result['routeInfoObj']->class);
		$this->assertEquals('Test', $result['routeInfoObj']->namespace);

		$result = $router->executeRoute($uri);

		$this->assertEquals('pie', $result);
	}

	public function testNamespacedFunctionTwoParams() {
		$uri = 'test1/hello/world';

		$router = new \Orion\SimpleRouter();
		$router->addRoute('test1/(:any)/(:any)', 'i', null, 'Test');
		$router->addRoute('test1/(:any)', 'h', null, 'Test');
		$router->addRoute('test1/', 'g', null, 'Test');

		$result = $router->getRoute($uri);

		$this->assertEquals('test1/hello/world', $result['uri']);
		$this->assertEquals('i', $result['routeInfoObj']->function);
		$this->assertEquals(null, $result['routeInfoObj']->class);
		$this->assertEquals('Test', $result['routeInfoObj']->namespace);

		$result = $router->executeRoute($uri);

		$this->assertEquals('helloworld', $result);
	}

	public function testNamespacedMethodNoParams() {
		$uri = 'TestObject/test1/';

		$router = new \Orion\SimpleRouter();
		$router->addRoute('TestObject/test1/', 'a', 'TestClass', 'Test');
		$router->addRoute('test1/(:any)/(:any)', 'i');
		$router->addRoute('test1/(:any)', 'h');
		$router->addRoute('test1/', 'g');

		$result = $router->getRoute($uri);

		$this->assertEquals('TestObject/test1', $result['uri']);
		$this->assertEquals('a', $result['routeInfoObj']->function);
		$this->assertEquals('TestClass', $result['routeInfoObj']->class);
		$this->assertEquals('Test', $result['routeInfoObj']->namespace);

		$result = $router->executeRoute($uri);

		$this->assertEquals(1, $result);
	}

	public function testNamespacedMethodOneParam() {
		$uri = 'TestObject/test1/first param';

		$router = new \Orion\SimpleRouter();
		$router->addRoute('TestObject/test1/(:nonum)/', 'b', 'TestClass', 'Test');
		$router->addRoute('TestObject/test1/', 'a', 'TestClass', 'Test');
		$router->addRoute('test1/(:any)/(:any)', 'i');
		$router->addRoute('test1/(:any)', 'h');
		$router->addRoute('test1/', 'g');

		$result = $router->getRoute($uri);

		$this->assertEquals('TestObject/test1/first param', $result['uri']);
		$this->assertEquals('b', $result['routeInfoObj']->function);
		$this->assertEquals('TestClass', $result['routeInfoObj']->class);
		$this->assertEquals('Test', $result['routeInfoObj']->namespace);

		$result = $router->executeRoute($uri);

		$this->assertEquals('first param', $result);
	}

	public function testNamespacedMethodTwoParams() {
		$uri = 'TestObject/test1/first param/123/';

		$router = new \Orion\SimpleRouter();
		$router->addRoute('TestObject/test1/(:nonum)/(:num)', 'c', 'TestClass', 'Test');
		$router->addRoute('TestObject/test1/(:nonum)/(:nonum)', 'notused', 'TestClass', 'Test');
		$router->addRoute('TestObject/test1/(:nonum)/', 'b', 'TestClass', 'Test');
		$router->addRoute('TestObject/test1/', 'a', 'TestClass', 'Test');
		$router->addRoute('test1/(:any)/(:any)', 'i');
		$router->addRoute('test1/(:any)', 'h');
		$router->addRoute('test1/', 'g');

		$result = $router->getRoute($uri);

		$this->assertEquals('TestObject/test1/first param/123', $result['uri']);
		$this->assertEquals('c', $result['routeInfoObj']->function);
		$this->assertEquals('TestClass', $result['routeInfoObj']->class);
		$this->assertEquals('Test', $result['routeInfoObj']->namespace);

		$result = $router->executeRoute($uri);

		$this->assertEquals('first param123', $result);
	}

	public function testBackreferences() {
		$router = new \Orion\SimpleRouter();
		$router->addRoute(
			'test1/(:any)/(:any)/(:any)',
			array('index' => 2),
			array('index' => 1),
			array('index' => 0)
		);
		$router->addRoute(
			'test1/(:any)/(:any)',
			array('index' => 1),
			array('index' => 0),
			null
		);
		$router->addRoute('test1/', 'g');
		$router->addRoute('test1/(:any)', 'h');

		$result = $router->getRoute('test1/TestClass/a');

		$this->assertEquals('test1/TestClass/a', $result['uri']);
		$this->assertEquals('a', $result['routeInfoObj']->function);
		$this->assertEquals('TestClass', $result['routeInfoObj']->class);
		$this->assertEquals(null, $result['routeInfoObj']->namespace);

		$result = $router->getRoute('test1/Test/TestClass/a');

		$this->assertEquals('test1/Test/TestClass/a', $result['uri']);
		$this->assertEquals('a', $result['routeInfoObj']->function);
		$this->assertEquals('TestClass', $result['routeInfoObj']->class);
		$this->assertEquals('Test', $result['routeInfoObj']->namespace);
	}
	
	public function testWildcard() {
		$router = new \Orion\SimpleRouter();
		$router->addRoute(
			'(:any)',
			array('index' => 2),
			array('index' => 1),
			array('index' => 0)
		);

		$result = $router->getRoute('Test/TestClass/a');

		$this->assertEquals('Test/TestClass/a', $result['uri']);
		$this->assertEquals('a', $result['routeInfoObj']->function);
		$this->assertEquals('TestClass', $result['routeInfoObj']->class);
		$this->assertEquals('Test', $result['routeInfoObj']->namespace);

		$result = $router->getRoute('Test/TestClass/b/HALLO!!');

		$this->assertEquals('Test/TestClass/b/HALLO!!', $result['uri']);
		$this->assertEquals('b', $result['routeInfoObj']->function);
		$this->assertEquals('TestClass', $result['routeInfoObj']->class);
		$this->assertEquals('Test', $result['routeInfoObj']->namespace);

		$result = $router->executeRoute('Test/TestClass/b/HALLO!!');

		$this->assertEquals('HALLO!!', $result);
	}

	public function testDefaultConfigs() {
		$router = new \Orion\SimpleRouter();
		$router->addRoute(
			'(:any)',
			array('index' => 1),
			array('index' => 0)
		);

		\Orion\SimpleRouterConfig::$defaultClass = 'TestClass';
		\Orion\SimpleRouterConfig::$defaultFunction = 'a';

		$result = $router->getRoute('');
		$this->assertEquals('', $result['uri']);
		$this->assertEquals('a', $result['routeInfoObj']->function);
		$this->assertEquals('TestClass', $result['routeInfoObj']->class);
		$this->assertEquals(null, $result['routeInfoObj']->namespace);

		$result = $router->executeRoute('');

		$this->assertEquals(1, $result);

		$result = $router->getRoute('TestClass');
		$this->assertEquals('TestClass', $result['uri']);
		$this->assertEquals('a', $result['routeInfoObj']->function);
		$this->assertEquals('TestClass', $result['routeInfoObj']->class);
		$this->assertEquals(null, $result['routeInfoObj']->namespace);

		$result = $router->executeRoute('TestClass');

		$this->assertEquals(1, $result);

		$result = $router->getRoute('TestClass/a');
		$this->assertEquals('TestClass/a', $result['uri']);
		$this->assertEquals('a', $result['routeInfoObj']->function);
		$this->assertEquals('TestClass', $result['routeInfoObj']->class);
		$this->assertEquals(null, $result['routeInfoObj']->namespace);

		$result = $router->executeRoute('TestClass');

		$this->assertEquals(1, $result);
	}
}

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