<?php
namespace Orion;

/*
 * This interface standardizes the way we interact with a database result set.
 */
interface iDatabaseResult {

	/*
	 * @param 	$resultObject	The result in some other format.
	 */
	public function __construct(&$resultObject);

	/*
	 * @return 	an array of all of the queried rows. Each element in the 
	 *			return array is an array keyed by the row field names.
	 */
	public function allRows();

	/*
	 * @return an array of the first queried row keyed by the field names.
	 */
	public function singleRow();

	/*
	 * @return the number of rows that were effected by the query
	 */
	public function numAffected();
}
?>