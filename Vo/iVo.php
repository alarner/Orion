<?php
namespace Orion;

/*
 * Requires:
 *	- Template
 */

interface iVo {
	/*
	 * @param	$row 	an array of data to set default values for the vo.
	 *
	 * @throws an error if any of the values fail validation.
	 */
	function __construct(array $row);

	/*
	 * @param	$fieldName 	the name of the property to get.
	 *
	 * @throws an error if the property doesn't exist.
	 */
	public function get($fieldName);

	/*
	 * @param	$fieldName 	the name of the property to set.
	 *
	 * @param	$value 	the value to set.
	 *
	 * @throws an error if the property doesn't exist.
	 * @throws an error if the value fails validation.
	 */
	public function set($fieldName, $value);

	/*
	 * Saves the vo to the database.
	 *
	 * @param	$db 	an object the implements iDatabase
	 *
	 * @throws an error if there was a problem saving to the database
	 */
	public function save();

	/*
	 * Fetches a specific object from the database.
	 *
	 * @param	$id 	The id of the object to fetch.
	 */
	public function fetch($id);

}