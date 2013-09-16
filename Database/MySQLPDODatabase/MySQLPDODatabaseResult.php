<?php
namespace Orion;
require_once('Database/iDatabaseResult.php');
/*
 * @see Database/iDatabaseResult.php for documentation.
 */
class MySQLPDODatabaseResult implements iDatabaseResult{

	private $pdoStatement;

	public function __construct(&$resultObject) {
		$this->pdoStatement =& $resultObject;
	}

	public function allRows() {
		return $this->pdoStatement->fetchAll(\PDO::FETCH_ASSOC);
	}

	public function singleRow() {
		return $this->pdoStatement->fetch(\PDO::FETCH_ASSOC);
	}

	public function numAffected() {
		return $this->pdoStatement->rowCount();
	}
}
?>