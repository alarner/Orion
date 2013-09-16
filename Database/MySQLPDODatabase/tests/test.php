<?php
require_once(dirname(__FILE__).'/../../../config.php');
set_include_path(get_include_path() . PATH_SEPARATOR . OrionConfig::$basePath);

require_once('Database/MySQLPDODatabase/MySQLPDODatabase.php');
require_once('Database/MySQLPDODatabase/config.php');

require_once('PHPUnit/Extensions/Database/TestCase.php');
class DatabaseTest extends PHPUnit_Extensions_Database_TestCase {

	private $db;
	public function __construct() {
		$this->db = \Orion\MySQLPDODatabase::instance();
	}

	/**
	 * @return PHPUnit_Extensions_Database_DB_IDatabaseConnection
	 */
	public function getConnection() {
		$pdo = new \PDO(
			'mysql:host='.\Orion\MySQLPDODatabaseConfig::$host.';dbname='.\Orion\MySQLPDODatabaseConfig::$database,
			\Orion\MySQLPDODatabaseConfig::$user,
			\Orion\MySQLPDODatabaseConfig::$password,
			array(\PDO::ATTR_PERSISTENT => true)
		);
		return $this->createDefaultDBConnection($pdo, \Orion\MySQLPDODatabaseConfig::$database);
	}

	/**
	 * @return PHPUnit_Extensions_Database_DataSet_IDataSet
	 */
	public function getDataSet() {
		return $this->createFlatXMLDataSet(dirname(__FILE__).'/seed.xml');
	}

	public function testPrepareSql() {
		$sql = 'SELECT * FROM `test` WHERE `id` IN `name`=? AND `id` IN(?)';
		$params = array('aaron', array(1,2,3,4,5));
		$this->db->prepareSql($sql, $params);
		$this->assertEquals(
			'SELECT * FROM `test` WHERE `id` IN `name`=? AND `id` IN(?,?,?,?,?)',
			$sql
		);
		$this->assertEquals('aaron', $params[1]);
		$this->assertEquals(1, $params[2]);
		$this->assertEquals(2, $params[3]);
		$this->assertEquals(3, $params[4]);
		$this->assertEquals(4, $params[5]);
		$this->assertEquals(5, $params[6]);

		$sql = 'SELECT * FROM `test` WHERE `id` IN `name`=? AND `id` IN(?,?)';
		$params = array('aaron', array(1,2,3,4,5), 6);
		$this->db->prepareSql($sql, $params);
		$this->assertEquals(
			'SELECT * FROM `test` WHERE `id` IN `name`=? AND `id` IN(?,?,?,?,?,?)',
			$sql
		);
		$this->assertEquals('aaron', $params[1]);
		$this->assertEquals(1, $params[2]);
		$this->assertEquals(2, $params[3]);
		$this->assertEquals(3, $params[4]);
		$this->assertEquals(4, $params[5]);
		$this->assertEquals(5, $params[6]);
		$this->assertEquals(6, $params[7]);
	}

	public function testBasicSelect() {
		// Make sure we can query without any parameters
		$sql = 'SELECT * FROM `_orion_users`';
		$result = $this->db->query($sql)->singleRow();

		$sql = 'SELECT * FROM `_orion_users` WHERE `user_id`=?';
		$result = $this->db->query($sql, array(2))->singleRow();

		$this->assertArrayHasKey('user_id', $result);
		$this->assertArrayHasKey('username', $result);
		$this->assertArrayHasKey('password', $result);
		$this->assertArrayHasKey('state', $result);
		$this->assertArrayHasKey('date_created', $result);
		$this->assertArrayHasKey('last_login', $result);

		$this->assertEquals(2, $result['user_id']);
		$this->assertEquals('rsmith', $result['username']);
		$this->assertEquals('aaa', $result['password']);
		$this->assertEquals(1, $result['state']);
		$this->assertEquals('2011-12-21 00:00:00', $result['date_created']);
		$this->assertEquals(null, $result['last_login']);
	}

	public function testTwoParamSelect() {
		$sql = 'SELECT * FROM `_orion_users` WHERE `username`=? AND `password`=?';
		$result = $this->db->query($sql, array('amann', 'jk4p3'))->allRows();

		$this->assertEquals(1, count($result));
		$result = $result[0];
		$this->assertArrayHasKey('user_id', $result);
		$this->assertArrayHasKey('username', $result);
		$this->assertArrayHasKey('password', $result);
		$this->assertArrayHasKey('state', $result);
		$this->assertArrayHasKey('date_created', $result);
		$this->assertArrayHasKey('last_login', $result);

		$this->assertEquals(6, $result['user_id']);
		$this->assertEquals('amann', $result['username']);
		$this->assertEquals('jk4p3', $result['password']);
		$this->assertEquals(3, $result['state']);
		$this->assertEquals('2011-12-26 00:00:00', $result['date_created']);
		$this->assertEquals(null, $result['last_login']);
	}
}
?>