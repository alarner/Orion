<?php
namespace Orion;
require_once('Database/MySQLPDODatabase/config.php');
require_once('Database/iDatabase.php');
require_once('Database/MySQLPDODatabase/MySQLPDODatabaseResult.php');

/*
 * Requires:
 *	- PDO [http://www.php.net/manual/en/book.pdo.php]
 *
 * @see Database/iDatabase.php for documentation.
 */

class MySQLPDODatabase implements iDatabase {

	private static $instance = array();
	private $pdo = array();
	private $lastQuery = array();

	private function __construct($key, $host, $database, $user, $password) {
		$this->pdo[$key] = @new \PDO(
			'mysql:host='.$host.';dbname='.$database,
			$user,
			$password,
			array(\PDO::ATTR_PERSISTENT => true)
		);
		$this->pdo[$key]->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
		$this->lastQuery[$key] = '';
	}

	public static function instance($key='default', $host=null, $database=null, $user=null, $password=null) {
		if($key == 'default') {
			$host = MySQLPDODatabaseConfig::$host;
			$database = MySQLPDODatabaseConfig::$database;
			$user = MySQLPDODatabaseConfig::$user;
			$password = MySQLPDODatabaseConfig::$password;
		}
		if(!array_key_exists($key, self::$instance)) {
			self::$instance[$key] = new MySQLPDODatabase($key, $host, $database, $user, $password);
		}
		return self::$instance[$key];
	}

	public function query($sql, $params = array(), $key='default') {
		$this->prepareSql($sql, $params);
		$this->lastQuery[$key] = $sql;
		$stmt = $this->pdo[$key]->prepare($sql);
		foreach($params as $k => $val) {
			$stmt->bindValue(intval($k), $val);
			$this->lastQuery[$key] = preg_replace('/\?/', '"'.$val.'"', $this->lastQuery[$key], 1);
		}
		try {
			$stmt->execute();
		}
		catch(PDOException $e) {
			echo '<pre>';
			print_r($e);
			echo '</pre>';
		}

		return new MySQLPDODatabaseResult($stmt);
	}

	public function insertId($key='default') {
		return $this->pdo[$key]->lastInsertId();
	}

	public function lastQuery($key='default') {
		return $this->lastQuery[$key];
	}

	public function prepareSql(&$sql, &$params) {
		$linearParams = array();
		$offset = 0;
		$counter = 1;
		foreach($params as $p) {
			$pos = strpos($sql, '?', $offset);
			if(!is_array($p)) {
				$linearParams[$counter] = $p;
				$counter++;
				$offset = $pos+1;
			}
			else {
				$inStr = str_repeat('?,', count($p));
				$inStr = substr($inStr, 0, strlen($inStr)-1);
				$before = substr($sql, 0, $pos);
				$after = substr($sql, $pos+1);
				$sql = $before.$inStr.$after;
				$offset = $pos + strlen($inStr);
				foreach($p as $inP) {
					$linearParams[$counter] = $inP;
					$counter++;
				}
			}
		}
		$params = $linearParams;
	}

	public function __clone() {
		trigger_error('Clone is not allowed.', E_USER_ERROR);
	}

	public function __wakeup() {
		trigger_error('Unserializing is not allowed.', E_USER_ERROR);
	}
}
?>