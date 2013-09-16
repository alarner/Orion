<?php
namespace Orion;
require_once('Emailer/iEmailParams.php');

/*
 * Requires:
 *	- Template.php
 *	- ErrorList.php
 */

interface iEmailer {
	public function __construct(\Orion\iDatabase &$database);

	/*
	 * Sends an email based on the passed in parameters. Emails can be marked to be 
	 * queued, so they might not get sent immediately.
	 *
	 * @param	$params		An EmailParams object that stores all information 
	 *						necessary to send the email.
	 *
	 * @return true if the email was sent, false if the user was unsibscribed
	 */
	public function send(\Orion\iEmailParams $params);

	/*
	 * Unsubscribes a user from future emails.
	 *
	 * @param	$email		The users email address.
	 *
	 * @param	$emailId	The id of the email that caused the user 
	 *						to unsubscribe.
	 *
	 * @param	$emailHash	The email hash from the email that caused
	 *						the user to unsubscribe.
	 *
	 * @return true on success, else false.
	 */
	public function unsubscribe($email, $emailId, $emailHash);

	/*
	 * @return true if the user has unsubscribed, else false.
	 */
	public function isUnsubscribed($email);

	/*
	 * Send an email given a email id from the database.
	 *
	 * @param	$emailId	The email id that can be referenced to get all of 
	 *						the necessary email parameters.
	 *
	 * @param	$debug		True if debug information should be included in 
	 *						the email, else false.
	 *
	 * @return a list of errors.
	 */
	public function &sendFromDatabase($emailId, $debug = false);
}
?>