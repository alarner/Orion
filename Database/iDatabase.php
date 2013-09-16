<?php
namespace Orion;

/*
 * This is a simplified interface for interaction with a database.
 */

interface iDatabase {

	/*
	 * @return The a singleton instance of the database object.
	 */
	public static function instance();

	/*
	 * Queries the database
	 *
	 * @param	$sql	The sql statement string.
	 *
	 * @param	$params	The parameters to use with the sql statement.
	 *
	 * @return 	an IDatabaseResult object.
	 */
	public function query($sql, $params);

	/*
	 * @return the last insert id.
	 */
	public function insertId();

	/*
	 * @return the last query that was run with all parameters filled in.
	 */
	public function lastQuery();

	/*
	 * Prepares a query string and list of params for execution. Takes a potentially 
	 * 2d params array and converts it to a 1d list. For example if the following 
	 * parameters were passed in:
	 *	- $sql = "SELECT * FROM `users` WHERE `user_id`=? AND `type` IN (?)"
	 *	- $params = array(1, array('regular', 'admin'))
	 * the resulting $sql and $params variables would be:
	 *	- $sql = "SELECT * FROM `users` WHERE `user_id`=? AND `type` IN (?,?)"
	 *	- $params = array(1, 'regular', 'admin')
	 *
	 * @param	$sql	The sql statement string.
	 *
	 * @param	$params	The parameters to use with the sql statement.
	 */
	public function prepareSql(&$sql, &$params);

	/*
	 * This method should prevent the singleton from being cloned.
	 */
	public function __clone();
	
	/*
	 * This method should prevent the singleton from being userialized.
	 */
	public function __wakeup();
}
?>