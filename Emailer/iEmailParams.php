<?php
namespace Orion;

/*
 * An interface to hold all of the paramters required in order to send an email.
 */
interface iEmailParams {

	public function __construct();

	/*
	 * Set the email recipient address.
	 *
	 * @param	$name	The name of the sender.
	 *
	 * @param	$email	The email of the sender.
	 */
	public function setTo($name, $email);

	/*
	 * @return	the EmailAddress object that contains the senders information.
	 */
	public function to();

	/*
	 * Set the email senders address.
	 *
	 * @param	$name	The name of the sender.
	 *
	 * @param	$email	The email of the sender.
	 */
	public function setFrom($name, $email);

	/*
	 * @return	the EmailAddress object that contains the senders information.
	 */
	public function from();

	/*
	 * Set the email subject
	 *
	 * @param	$subject	The email subject.
	 */
	public function setSubject($subject);

	/*
	 * @return	the email subject.
	 */
	public function subject();

	/*
	 * Set the path to the text template.
	 *
	 * @param	$path	The path to the text template.
	 */
	public function setTemplatePathText($path);

	/*
	 * Set the path to the html template.
	 *
	 * @param	$path	The path to the html template.
	 */
	public function setTemplatePathHtml($path);

	/*
	 * Get the path to the text template.
	 *
	 * @return	the path to the text template.
	 */
	public function templatePathText();

	 /*
	 * Get the path to the html template.
	 *
	 * @return	the path to the html template.
	 */
	public function templatePathHtml();

	/*
	 * Set params for the footer or body templates.
	 *
	 * @param	$footerOrBody	Must be either the string "footer" or "body" 
	 *							representing the type of template that the 
	 *							params should be used for.
	 *
	 * @param	$params			The param array.
	 */
	public function setTemplateParams($params);

	/*
	 * Get the parameters for a template that should be used for the email.
	 *
	 * @return 	the parameters for the email template.
	 */
	public function templateParams();

	/*
	 * Specify whether or not to check if the email address is unsubscribed
	 * before sending the email.
	 *
	 * @param	$val	True or false.
	 */
	public function setCheckSubscribed($val);

	/*
	 * @return 	true if the emailer should verify that the email address is not
	 *			unsubscribed, else false.
	 */
	public function checkSubscribed();

	/*
	 * Specify whether or not the email should be added to the email queue or
	 * sent immediately.
	 *
	 * @param	$val	True or false.
	 */
	public function setQueued($val);

	/*
	 * @return 	true if the email should be added to the email queue, else false.
	 */
	public function queued();

	/*
	 * @return 	true if the email is valid, else false.
	 */
	public function isValid();

	/*
	 * @return 	all of the errors.
	 */
	public function &errors();
}
?>