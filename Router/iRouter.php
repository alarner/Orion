<?php
namespace Orion;

interface iRouter {
	public function __construct();

	/*
	 * Add a route to the Router. The route specifies which namespace, class, 
	 * and method should be executed given a uri pattern.
	 *
	 * @param	$pattern	The pattern to match. The pattern can be a literal 
	 *						uri (like index/login) or it can contain wildcards 
	 *						(like index/user/(:num)). Valid wildcards are...
	 *							- (:any)
	 *							- (:num)
	 *							- (:nonum)
	 *							- (:alpha)
	 *							- (:alnum)
	 *							- (:hex)
	 *
	 * @param	$function	The function or class method that should be called
	 *						if the uri pattern is matched. This parameter can be
	 *						a string or an array. If it is a string then the 
	 *						function with the specified name will be called. 
	 *						Alternativly, if it is an array, the function name
	 *						references a wildcard. The array should have the
	 *						following keys:
	 *							- index:  The index of the parameter that should
	 *									  be used for the function name.
	 *							- prefix: (optional) Any string that should be 
	 *									  prepended tothe parameter at the 
	 *									  specified index to form the function 
	 *									  name.
	 *							- suffix: (optional) Any string that should be 
	 *									  appended to the parameter at the 
	 *									  specified index to form the function 
	 *									  name.
	 *						
	 *						For example: if the value of the $function parameter
	 *						is array('index' => 0, 'suffix' => 'Action') and the
	 *						$pattern is 'test/(:any)' then a uri of 'test/hello'
	 *						would translate to a function name of 'helloAction'.
	 *
	 * @param	$class 		The class that should be called if the uri pattern 
	 *						is matched. 
	 *
	 * @param	$namespace	The namespace that the class and/or function belongs
	 *						to. This parameter can be a string, an array, or 
	 *						null if the class and/or function belongs to the 
	 *						global namespace. If it is a string then the 
	 *						namespace with the specified name will be called. 
	 *						Alternativly, if it is an array, the namespace 
	 *						references a wildcard inthe same way that the 
	 *						$function parameter behaves.
	 *
	 * @param	$namespace	True if the specified function is static, else false.
	 */
	public function addRoute($pattern, $function = null, $class = null, $namespace = null, $static = false);

	/*
	 * Gets information about a route given a uri.
	 *
	 * @param	$uri	The uri to match.
	 *
	 * @return 	an array with route information. The return value will have 
	 *			the following properties:
	 *				- uri: 			The uri that was matched.
	 *				- routeInfoObj: A stdClass with information about where the
	 *								route should map. It has the following
	 *								properties:
	 *									- namespace: The namespace to use, or
	 *									  null for the global namespace.
	 *									- class: The class to use, or null for
	 *									  if the function doesn't belong to a
	 *									  class.
	 *									- function: The function to call.
	 *				- params:		Any parameters that should be passed into
	 *								the specified function.
	 */
	public function getRoute($uri);

	/*
	 * Executes the function that maps to the passed in uri.
	 *
	 * @param	$uri	The uri to match.
	 *
	 * @return	Any return value that the executed function passes back.
	 */
	public function executeRoute($uri);
}