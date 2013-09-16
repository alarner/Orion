<?php
namespace Orion;
class MVCConfig {
	public static $webRoot = '/path/to/web/root';
	public static $view404 = 'views/404.php';
	public static $requires = array(
		'Router' => '\Orion\SimpleRouter',
		'Template' => '\Orion\SimpleTemplate',
		'Database' => '\Orion\MySQLPDODatabase::instance',
		'ErrorList' => '\Orion\SimpleErrorList'
	);
	public static $isDev = true;
}

// Requires
require_once('Router/SimpleRouter/SimpleRouter.php');
require_once('Template/SimpleTemplate/SimpleTemplate.php');
require_once('Database/MySQLPDODatabase/MySQLPDODatabase.php');
require_once('ErrorList/SimpleErrorList/SimpleErrorList.php');

?>