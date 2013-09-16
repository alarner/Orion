<?php
namespace Orion;
require_once('MVC/config.php');

if(MVCConfig::$isDev) {
	error_reporting(E_ALL);
	ini_set('display_errors', '1');
}

$uri = $_SERVER['REQUEST_URI'];
$pieces = explode('/', $uri);
$lastPiece = $pieces[count($pieces)-1];
$removeGetParams = explode('?', $lastPiece);
$pieces[count($pieces)-1] = array_shift($removeGetParams);
$uri = implode('/', $pieces);

$getString = implode('?', $removeGetParams);
if(strlen($getString) > 0) {
	$getPieces = explode('&', $getString);
	foreach($getPieces as $g) {
		if(strpos($g, '=') !== false) {
			list($key, $val) = explode('=', $g);
			$_GET[$key] = $val;
		}
	}
}

if(empty($router)) {
	$router = new \AppConfig::$requires['Router'];
	$router->addRoute(
		'(:any)',
		array('index' => 1, 'suffix' => 'Action'),
		array('index' => 0)
	);
}

$routeInfo = $router->getRoute($uri);

require_once('MVC/Controller.php');
require_once('MVC/Model.php');
$filePath = stream_resolve_include_path('controllers/'.ucfirst($routeInfo['routeInfoObj']->class).'.php');
if(!$filePath) {
	header('HTTP/1.0 404 Not Found');
	echo 'Uh oh the page was not found!';
	exit;
}
else {
	include_once($filePath);
}

try {
	$result = $router->executeRoute($uri);
}
catch(\Exception $e) {
	if($e->getCode() == 1112 || !MVCConfig::$isDev) {
		header('HTTP/1.0 404 Not Found');
		echo 'Uh oh the page was not found!';
	}
	else {
		if(MVCConfig::$isDev) {
			echo $e->getMessage();
			echo $e->getCode();
			echo '<pre>';
			print_r($e->getTrace());
			echo '</pre>';
		}
	}
	exit;
}
?>