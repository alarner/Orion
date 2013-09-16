<?php
namespace Orion;
require_once('Router/SimpleRouter/config.php');
require_once('Router/iRouter.php');

/*
 * @see Router/iRouter.php for public method documentation.
 */
class SimpleRouter implements iRouter{
	private $routes;

	function __construct() {
		$this->routes = array();
	}

	public function addRoute($pattern, $function = null, $class = null, $namespace = null, $static = false) {
		$routeInfoObj = new \stdClass();
		$routeInfoObj->function = $function;
		$routeInfoObj->class = $class;
		$routeInfoObj->namespace = $namespace;
		$routeInfoObj->static = $static;

		// Convert wild-cards to RegEx
		$pattern = $this->prepUri($pattern);
		$pattern = str_replace( ':any', '.*', $pattern);
		$pattern = str_replace( ':num', '[0-9]+', $pattern);
		$pattern = str_replace( ':nonum', '[^0-9]+', $pattern);
		$pattern = str_replace( ':alpha', '[A-Za-z]+', $pattern);
		$pattern = str_replace( ':alnum', '[A-Za-z0-9]+', $pattern);
		$pattern = str_replace( ':hex', '[A-Fa-f0-9]+', $pattern);

		$pattern = '#^'.$pattern.'$#';

		$this->routes[$pattern] =& $routeInfoObj;
	}

	public function getRoute($uri) {
		$uri = $this->prepUri($uri);

		// Is there a literal match?
		if(array_key_exists($uri, $this->routes)) {
			return array(
				'uri' => $uri,
				'routeInfoObj' => $this->routes[$uri],
				'params' => array()
			);
		}

		foreach($this->routes as $pattern => $val) {
			$matches = array();
			if(preg_match($pattern, $uri, $matches) === 1) {
				array_shift($matches);
				$params = array();
				foreach($matches as $m) {
					$pieces = explode('/', $m);
					foreach($pieces as $p) {
						$params[] = $p;
					}
				}
				$routeInfoObj = clone $val;
				$this->processBackreferences($routeInfoObj, $params);

				if(strlen($routeInfoObj->function) == 0) {
					$routeInfoObj->function = SimpleRouterConfig::$defaultFunction;
				}

				if(strlen($routeInfoObj->class) == 0) {
					$routeInfoObj->class = SimpleRouterConfig::$defaultClass;
				}

				if(strlen($routeInfoObj->namespace) == 0) {

					$routeInfoObj->namespace = SimpleRouterConfig::$defaultNamespace;
				}

				return array(
					'uri' => $uri,
					'routeInfoObj' => $routeInfoObj,
					'params' => $params
				);
			}
		}

		return false;
	}

	public function executeRoute($uri) {
		$uri = $this->prepUri($uri);

		$result = $this->getRoute($uri);
		if($result !== false) {
			$routeInfoObj = $result['routeInfoObj'];
			return $this->executeRouteFromInfoObj($routeInfoObj, $result['params']);
		}
		throw new Exception('Unable to find a route that matches the specified uri.');
	}

	/*
	 * Executes the function that maps to the passed in $routeInfoObj.
	 *
	 * @param	$routeInfoObj	Specified the namespace, class, and function to
	 *							execute.
	 *
	 * @return	Any return value that the executed function passes back.
	 */
	private function executeRouteFromInfoObj(&$routeInfoObj, $params) {
		if(strlen($routeInfoObj->class) == 0) {
			$namespaceFunction = '';
			if($routeInfoObj->namespace !== null) {
				$namespaceFunction = '\\' . $routeInfoObj->namespace . '\\' . $routeInfoObj->function;
			}
			else {
				$namespaceFunction = $routeInfoObj->function;
			}

			if(!function_exists($namespaceFunction)) {
				throw new \Exception('There is no function that matches that URI.', 1112);
			}

			$result = call_user_func_array($namespaceFunction, $params);
		}
		else {
			$namespaceClass = '';
			if($routeInfoObj->namespace !== null) {
				$namespaceClass = '\\' . $routeInfoObj->namespace . '\\' . $routeInfoObj->class;
			}
			else {
				$namespaceClass = '\\' . $routeInfoObj->class;
			}

			$class = null;
			if($routeInfoObj->static) {
				$class = $namespaceClass;
			}
			else {
				$class = new $namespaceClass($routeInfoObj);
			}

			if(!method_exists($class, $routeInfoObj->function)) {
				throw new \Exception('There is no method that matches that URI.', 1112);
			}

			$result = call_user_func_array(
				array(
					$class,
					$routeInfoObj->function
				),
				$params
			);
		}
		
		return $result;
	}

	/*
	 * Massages the uri into an acceptable format (no leading or trailing backslash).
	 *
	 * @param	$uri
	 *
	 * @return	The reformatted uri.
	 */
	private function prepUri($uri) {
		$uri = rtrim(ltrim($uri, '/'), '/');
		return $uri;
	}

	/*
	 * Updates the namespace, class, and function in the passed in $routeInfoObj
	 * in order to take into account backreferenced values.
	 *
	 * @param	$routeInfoObj	Constains info about the namespace, class and
	 *							function.
	 *
	 * @param	$params			The list of parameters.
	 */
	private function processBackreferences(&$routeInfoObj, &$params) {
		$removeParams = array();
		$functionVal = $this->getBackreferenceValue($routeInfoObj->function, $params, SimpleRouterConfig::$defaultFunction);
		if($functionVal !== false) {
			$removeParams[] = $routeInfoObj->function['index'];
			$routeInfoObj->function = $functionVal;
		}
		
		$classVal = $this->getBackreferenceValue($routeInfoObj->class, $params, SimpleRouterConfig::$defaultClass);
		if($classVal !== false) {
			$removeParams[] = $routeInfoObj->class['index'];
			$routeInfoObj->class = $classVal;
		}

		$namespaceVal = $this->getBackreferenceValue($routeInfoObj->namespace, $params, SimpleRouterConfig::$defaultNamespace);
		if($namespaceVal !== false) {
			$removeParams[] = $routeInfoObj->namespace['index'];
			$routeInfoObj->namespace = $namespaceVal;
		}

		// Sort from lowest to highest so that the indexes don't get messed up
		// when we remove them one at a time.
		rsort($removeParams);

		foreach($removeParams as $index) {
			array_splice($params, $index, 1);
		}
	}

	/*
	 * Given an array with backreference information, returns the appropriate
	 * backreferenced value.
	 *
	 * @param	$brInfo	An array that contains the following keys:
	 *					- index:	The index of the backreferenced value int 
	 *								the $params array.
	 *					- prefix:	(optional) any string that should be 
	 *								prepended to the backreferenced value.
	 *					- suffix:	(optional) any value that should be appended
	 *								to the backreferenced value.
	 *
	 * @param	$params	The list of parameters.
	 *
	 * @return	tbhe appropriate backreferenced value.
	 */
	private function getBackreferenceValue(&$brInfo, &$params, $default) {
		$return = false;
		if(is_array($brInfo)) {
			if(array_key_exists('index', $brInfo)) {
				$return = '';
				$content = '';
				if(is_int($brInfo['index']) && $brInfo['index'] < count($params)) {		
					$content = $params[$brInfo['index']];
				}
				else {
					$content = $default;
				}

				if(array_key_exists('prefix', $brInfo)) {
					$return .= $brInfo['prefix'];
				}
				$return .= $content;
				if(array_key_exists('suffix', $brInfo)) {
					$return .= $brInfo['suffix'];
				}
			}
		}
		return $return;
	}
}