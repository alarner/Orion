<?php
require_once(dirname(__FILE__).'/../../../config.php');
set_include_path(get_include_path() . PATH_SEPARATOR . OrionConfig::$basePath);

require_once('Emailer/PEAREmailer/PEAREmailer.php');
require_once('Emailer/PEAREmailer/PEAREmailParams.php');
require_once('Database/MySQLPDODatabase/MySQLPDODatabase.php');

class EmailerTest extends PHPUnit_Extensions_Database_TestCase {
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
		return $this->createXMLDataSet(dirname(__FILE__).'/emailer-seed.xml');
	}

	public function testConnection() {
		$this->assertEquals(0, $this->getConnection()->getRowCount('_orion_emails'));
	}

	public function testBasicEmail() {
		$emailer = new \Orion\PEAREmailer($this->db);
		$params = new \Orion\PEAREmailParams();
		$params->setFrom('Orion Studio', 'info@orionstudiomadison.com');
		$params->setSubject('An email send from EmailerTest::testSendEmail');
		$params->setTo('Aaron Larner', 'anlarner@gmail.com');
		$params->setTemplatePathText('Emailer/views/text.php');
		$params->setTemplatePathHtml('Emailer/views/html.php');
		$success = $emailer->send($params);
		$this->assertEquals(true, $success);

		$queryTable = $this->getLimitedEmailsTable();
		$expectedTable = $this->createXMLDataSet(
			dirname(__FILE__).'/expected-state-testBasicEmail.xml'
		)->getTable('_orion_emails');
		$this->assertTablesEqual($expectedTable, $queryTable);
	}

	public function testUnsubscription() {
		$emailer = new \Orion\PEAREmailer($this->db);
		$params = new \Orion\PEAREmailParams();
		$params->setFrom('Orion Studio', 'info@orionstudiomadison.com');
		$params->setSubject('An email send from EmailerTest::testSendEmail');
		$params->setTo('Bob Smith', 'aero4x@gmail.com');
		$params->setTemplatePathText('Emailer/views/text.php');
		$success = $emailer->send($params);
		$this->assertEquals(true, $success);

		// Get the hash
		$db = \Orion\MySQLPDODatabase::instance();
		$result = $db->query('
			SELECT `hash`
			FROM `_orion_emails`
			WHERE `email_id`=1'
		)->singleRow();

		$success = $emailer->unsubscribe('aero4x@gmail.com', 1, $result['hash']);
		$this->assertEquals(true, $success);

		$params = new \Orion\PEAREmailParams();
		$params->setFrom('Orion Studio', 'info@orionstudiomadison.com');
		$params->setSubject('An email send from EmailerTest::testSendEmail');
		$params->setTo('Bob Smith', 'aero4x@gmail.com');
		$params->setTemplatePathText('Emailer/views/text.php');
		$success = $emailer->send($params);
		$this->assertEquals(false, $success);

		$queryTable = $this->getLimitedEmailsTable();
		$expectedTable = $this->createXMLDataSet(
			dirname(__FILE__).'/expected-state-testUnsubscription.xml'
		)->getTable('_orion_emails');
		$this->assertTablesEqual($expectedTable, $queryTable);

		$queryTable = $this->getLimitedEmailUnsubscriptionsTable();
		$expectedTable = $this->createXMLDataSet(
			dirname(__FILE__).'/expected-state-testUnsubscription.xml'
		)->getTable('_orion_email_unsubscriptions');
		$this->assertTablesEqual($expectedTable, $queryTable);

	}

	public function testComplexEmail() {
		$emailer = new \Orion\PEAREmailer($this->db);
		$params = new \Orion\PEAREmailParams();
		$params->setFrom('Orion Studio', 'info@orionstudiomadison.com');
		$params->setSubject('An email sent from EmailerTest::testSendEmail');
		$params->setTo('Aaron Larner', 'aero4x@gmail.com');
		$params->setTemplatePathText('Emailer/views/text.php');
		$params->setTemplatePathHtml('Emailer/views/html.php');
		$params->setTemplateParams(array('name'=>'Aaron', 'user_id'=>7));
		$params->setCheckSubscribed(false);
		$params->setQueued(true);
		$success = $emailer->send($params);
		$this->assertEquals(true, $success);

		$queryTable = $this->getLimitedEmailsTable();
		$expectedTable = $this->createXMLDataSet(
			dirname(__FILE__).'/expected-state-testComplexEmail.xml'
		)->getTable('_orion_emails');
		$this->assertTablesEqual($expectedTable, $queryTable);
	}

	public function testInvalidParams() {
		$params = new \Orion\PEAREmailParams();
		$params->setFrom('Orion Studio', '.info@orionstudiomadison.com');
		$params->setSubject('An email send from EmailerTest::testSendEmail');
		$params->setTo('Orion Studio', 'info@orionstudiomadison.com');
		$params->setTemplatePathText('Emailer/views/text.php');
		$this->assertEquals(false, $params->isValid());

		$params->setFrom('Orion Studio', 'info@orionstudiomadison.com');
		$this->assertEquals(true, $params->isValid());
		
		$params->setSubject('123456789012345678901234567890123456789012345678901234567890123456789012345678');
		$this->assertEquals(true, $params->isValid());

		$params->setSubject('1234567890123456789012345678901234567890123456789012345678901234567890123456789');
		$this->assertEquals(false, $params->isValid());

		$params->setSubject('123');
		$this->assertEquals(true, $params->isValid());

		$params->setTo('Orion Studio', 'info@orionstudiomadison.com');
		$this->assertEquals(true, $params->isValid());

		$params->setTemplatePathText('');
		$this->assertEquals(false, $params->isValid());
	}

	/**
     * @expectedException 			\Orion\PEAREmailerException
     * @expectedExceptionMessage	Invalid parameters.
     * @expectedExceptionCode		0
     */
	public function testSendWithInvalidParams() {
		$emailer = new \Orion\PEAREmailer($this->db);
		$params = new \Orion\PEAREmailParams();
		$params->setFrom('Orion Studio', '.info@orionstudiomadison.com');
		$params->setSubject('An email send from EmailerTest::testSendEmail');
		$params->setTo('Orion Studio', 'info@orionstudiomadison.com');
		$params->setTemplatePathText('Emailer/views/text.php');
		$this->assertEquals(false, $params->isValid());
		$success = $emailer->send($params);
	}

	private function getLimitedEmailsTable() {
		return $this->getConnection()->createQueryTable(
			'_orion_emails',
			'SELECT
				`email_id`,
				`to_name`,
				`to_email`,
				`from_name`,
				`from_email`,
				`subject`,
				`template_path_text`,
				`template_path_html`,
				`template_params`,
				`check_subscribed`,
				`queued`,
				`success`
			FROM `_orion_emails`'
		);
	}

	private function getLimitedEmailUnsubscriptionsTable() {
		return $this->getConnection()->createQueryTable(
			'_orion_email_unsubscriptions',
			'SELECT
				`email`,
				`email_id`
			FROM `_orion_email_unsubscriptions`'
		);
	}
}
?>