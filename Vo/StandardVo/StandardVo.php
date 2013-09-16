<?php
namespace Orion;
require_once('Vo/StandardVo/config.php');
require_once('Vo/iVo.php');
require_once('Validator/SimpleValidator/SimpleValidator.php');
require_once('Vo/StandardVo/StandardVoException.php');

/*
 * Requires:
 *	- Database
 *  - Validator
 */

class StandardVo implements iVo {
	protected $name;
	protected $fields;
	protected $values;
	protected $idFieldName = null;

	protected $db = null;

	const EXCEPTION_INVALID_FIELD = 0;
	const EXCEPTION_UNEXPECTED_FIELD = 1;
	const EXCEPTION_FETCH_ERROR = 2;

	/*
	 * @param	$row 	an array of data to set default values for the vo.
	 *
	 * @throws an error if any of the values fail validation.
	 */
	public function __construct(array $row = array()) {
		if(count($row) > 0) {
			$this->setAll($row);
		}
		else {
			$fieldNames = array_keys($this->fields);
			foreach($fieldNames as $name) {
				$this->values[$name] = null;
			}
		}
	}

	/*
	 * @param	$fieldName 	the name of the property to get.
	 *
	 * @throws an error if the property doesn't exist.
	 */
	public function get($fieldName) {
		if(!array_key_exists($fieldName, $this->fields)) {
			throw new StandardVoException(
				'Invalid field name: '.$fieldName.'.',
				StandardVo::EXCEPTION_INVALID_FIELD
			);
		}
		return $this->values[$fieldName];
	}

	/*
	 * @param	$fieldName 	the name of the property to set.
	 *
	 * @param	$value 	the value to set.
	 *
	 * @throws an error if the property doesn't exist.
	 * @throws an error if the value fails validation.
	 */
	public function set($fieldName, $value) {
		if(!array_key_exists($fieldName, $this->fields)) {
			throw new StandardVoException(
				'Unexpected field name: '.$fieldName.'.',
				StandardVo::EXCEPTION_UNEXPECTED_FIELD
			);
		}
		$validator = new \Orion\SimpleValidator();
		$validationOptions = $this->fields[$fieldName];
		$result = $validator->validate($value, $validationOptions);
		if(!$result->valid && !(is_null($value) && !$validationOptions->required)) {
			throw new StandardVoException(
				'Invalid field '.$fieldName.' => '.$value,
				StandardVo::EXCEPTION_INVALID_FIELD
			);
		}

		$this->values[$fieldName] = $value;
	}

	/*
	 * @param	$row 	an array of data to set on the vo.
	 *
	 * @throws an error if any of the values fail validation.
	 */
	public function setMultiple(array $row) {
		// if(count($row) > 0) {
		// 	$validator = new \Orion\SimpleValidator();
		// 	foreach($row as $key=>$val) {
		// 		if(!array_key_exists($key, $this->fields)) {
		// 			throw new StandardVoException(
		// 				'Unexpected field: '.$key,
		// 				StandardVo::EXCEPTION_UNEXPECTED_FIELD
		// 			);
		// 		}
		// 		$validationOptions = $this->fields[$key];
		// 		$result = $validator->validate($val, $validationOptions);
		// 		if(!$result->valid && !(is_null($val) && !$validationOptions->required)) {
		// 			throw new StandardVoException(
		// 				$key.' '.$result->errorMessage,
		// 				StandardVo::EXCEPTION_INVALID_FIELD
		// 			);
		// 		}
		// 	}
		// }
		if(!is_array($this->values)) {
			$this->values = array();
		}
		if(!is_array($this->fields)) {
			$this->fields = array();
		}
		foreach(array_keys($this->fields) as $fieldName) {
			if(!array_key_exists($fieldName, $this->values)) {
				$this->values[$fieldName] = null;
			}
		}
		foreach($row as $fieldName => $val) {
			$this->set($fieldName, $val);
		}
	}

	/*
	 * @param	$row 	an array of data to set on the vo.
	 *
	 * @throws an error if any of the values fail validation or if there are any missing values.
	 */
	public function setAll(array $row) {
		$validator = new \Orion\SimpleValidator();
		$validationOptions = new \Orion\ValidatorOptions(
			'array',
			array('keyedValidationOptions' => $this->fields)
		);
		$result = $validator->validate($row, $validationOptions);
		if(!$result->valid) {
			throw new StandardVoException(
				$result->errorMessage,
				StandardVo::EXCEPTION_INVALID_FIELD
			);
		}

		$this->values = $row;
		$fieldNames = array_keys($this->fields);
		foreach($fieldNames as $name) {
			if(!array_key_exists($name, $this->values)) {
				$this->values[$name] = null;
			}
		}
		// if(count($row) > 0) {
		// 	$validator = new \Orion\SimpleValidator();
		// 	foreach($row as $key=>$val) {
		// 		if(!array_key_exists($key, $this->fields)) {
		// 			throw new StandardVoException(
		// 				'Unexpected field: '.$key,
		// 				StandardVo::EXCEPTION_UNEXPECTED_FIELD
		// 			);
		// 		}
		// 		$validationOptions = $this->fields[$key];
		// 		$result = $validator->validate($val, $validationOptions);
		// 		if(!$result->valid && !(is_null($val) && !$validationOptions->required)) {
		// 			throw new StandardVoException(
		// 				$key.' '.$result->errorMessage,
		// 				StandardVo::EXCEPTION_INVALID_FIELD
		// 			);
		// 		}
		// 	}
		// }
		// if(!is_array($this->values)) {
		// 	$this->values = array();
		// }
		// if(!is_array($this->fields)) {
		// 	$this->fields = array();
		// }
		// foreach(array_keys($this->fields) as $fieldName) {
		// 	if(!array_key_exists($fieldName, $this->values)) {
		// 		$this->values[$fieldName] = null;
		// 	}
		// }
		// foreach($row as $fieldName => $val) {
		// 	$this->set($fieldName, $val);
		// }
	}

	/*
	 * 
	 *
	 *
	 * @throws an error if there was a problem saving to the database
	 */
	public function save() {
		// Create
		if(is_null($this->idFieldName) || is_null($this->values[$this->idFieldName])) {
			$this->create();
		}
		// Update
		else {
			$this->update();
		}
	}

	/*
	 * Fetches a specific object form the database.
	 *
	 * @param	$id 	The id of the object to fetch. This can be a single value or an
	 *					array of values in the case of objects with multiple fields that 
	 *					make up the primary key.
	 */
	public function fetch($id) {
		if(is_array($id) && !is_array($this->idFieldName)) {
			throw new StandardVoException(
				'An array was passed as a record id when a scalar was expected.',
				StandardVo::EXCEPTION_FETCH_ERROR
			);
		}
		else if(!is_array($id) && is_array($this->idFieldName)) {
			throw new StandardVoException(
				'A scalar was passed as a record id when an array was expected.',
				StandardVo::EXCEPTION_FETCH_ERROR
			);
		}
		else if(is_array($id) && is_array($this->idFieldName) && count($id) != count($this->idFieldName)) {
			throw new StandardVoException(
				'The length of the id was incorrect. Length = '.
				count($id).' was supplied and length = '.count($this->idFieldName).' was expected.',
				StandardVo::EXCEPTION_FETCH_ERROR
			);
		}

		$effectiveIdFieldNames = $this->idFieldName;
		if(!is_array($id)) {
			$id = array($id);
			$effectiveIdFieldNames = array($this->idFieldName);
		}

		if(!is_null($this->idFieldName)) {
			$sql = 'SELECT ';
			foreach($this->fields as $fieldName => $field) {
				if($field->type != 'array') {
					$sql .= '`'.$fieldName.'`,';
				}
			}
			$sql = substr($sql, 0, strlen($sql)-1);
			$sql .= ' FROM `'.$this->name.'` WHERE ';
			foreach($effectiveIdFieldNames as $name) {
				$sql .= '`'.$name.'` = ? AND ';
			}
			$sql = substr($sql, 0, strlen($sql)-5).' LIMIT 1';
			$row = $this->getDb()->query($sql, $id)->singleRow();
			if($row) {
				$this->setMultiple($row);
				return true;
			}
			return false;
		}
	}

	public function toArray() {
		return $this->values;
	}

	protected function &getDb() {
		if($this->db === null) {
			require_once(\Orion\StandardVoConfig::$requires['Database']['file']);
			$this->db = call_user_func(\Orion\StandardVoConfig::$requires['Database']['class']);
		}
		return $this->db;
	}

	private function create() {
		$sql = 'INSERT INTO `'.$this->name.'` (';
		$fieldNames = array_keys($this->fields);
		for($i=0; $i<count($fieldNames); $i++) {
			if($fieldNames[$i] != $this->idFieldName) {
				$sql .= '`'.$fieldNames[$i].'`,';
			}
		}
		$sql = substr($sql, 0, strlen($sql)-1);
		$sql .= ') VALUES(';
		$values = array();
		foreach($this->fields as $fieldName => $options) {
			if($fieldName != $this->idFieldName) {
				$setToNow = ($options->type == 'date') &&
							is_array($options->params) &&
							array_key_exists('set-on-create', $options->params) &&
							$options->params['set-on-create'];
				if($setToNow) {
					$sql .= 'NOW(),';
					$this->set($fieldName, Date('Y-m-d H:i:s'));
				}
				else {
					$sql .= '?,';
					$values[] = $this->values[$fieldName];
				}
			}
		}
		$sql = substr($sql, 0, strlen($sql)-1);
		$sql .= ') ON DUPLICATE KEY UPDATE ';

		foreach($this->fields as $fieldName => $options) {
			if($fieldName != $this->idFieldName) {
				$setOnCreate = 	($options->type == 'date') &&
								is_array($options->params) &&
								array_key_exists('set-on-create', $options->params) &&
								$options->params['set-on-create'];
				if(!$setOnCreate) {
					$setToNow = ($options->type == 'date') &&
								is_array($options->params) &&
								array_key_exists('reset-on-update', $options->params) &&
								$options->params['reset-on-update'];
					$sql .= '`'.$fieldName.'`=';
					if($setToNow) {
						$sql .= 'NOW(),';
						$this->set($fieldName, Date('Y-m-d H:i:s'));
					}
					else {
						$sql .= '?,';
						$values[] = $this->values[$fieldName];
					}
				}
			}
		}

		$sql = substr($sql, 0, strlen($sql)-1);

		$this->getDb()->query($sql, $values);
		if(!is_null($this->idFieldName)) {
			$this->set($this->idFieldName, $this->getDb()->insertId());
		}
	}

	private function update() {
		$sql = 'UPDATE `'.$this->name.'` SET ';
		$fieldNames = array_keys($this->fields);
		$values = array();
		foreach($this->values as $fieldName => $value) {
			$ignore = 	($fieldName == $this->idFieldName) ||
						($this->fields[$fieldName]->type == 'date') &&
						is_array($this->fields[$fieldName]->params) &&
						array_key_exists('set-on-create', $this->fields[$fieldName]->params) &&
						$this->fields[$fieldName]->params['set-on-create'];
			if(!$ignore) {
				$sql .= '`'.$fieldName.'`=';
				$setToNow = ($this->fields[$fieldName]->type == 'date') &&
							is_array($this->fields[$fieldName]->params) &&
							array_key_exists('reset-on-update', $this->fields[$fieldName]->params) &&
							$this->fields[$fieldName]->params['reset-on-update'];
				if($setToNow) {
					$sql .= 'NOW(),';
					$this->set($fieldName, Date('Y-m-d H:i:s'));
				}
				else {
					$sql .= '?,';
					$values[] = $this->values[$fieldName];
				}
			}
		}
		$sql = substr($sql, 0, strlen($sql)-1);
		$sql .= ' WHERE `'.$this->idFieldName.'`=?';
		$values[] = $this->values[$this->idFieldName];

		$this->getDb()->query($sql, $values);
	}
}
?>