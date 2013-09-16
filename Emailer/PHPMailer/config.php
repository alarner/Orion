<?php
namespace Orion;
class PHPMailerConfig {
	// This salt is used for hashes that are generated for each email that 
	// allow users to unsubscribe from the email list.
	public static $hashSalt	= 'ADD_SALT_HERE';
	public static $host		= 'SMTP_HOST';
	public static $port		= 'SMTP_PORT';
	public static $auth		= true;
	public static $username	= 'SMTP_USERNAME';
	public static $password	= 'SMTP_PASSWORD';

	const ERROR_RECIPIENT_UNSUBSCRIBED = 1;
	const ERROR_INVALID_EMAIL = 2;
	const ERROR_TOO_LONG = 3;
	const ERROR_SMTP = 4;
	const ERROR_INVALID_TEXT_TEMPLATE = 5;
	const ERROR_INVALID_HTML_TEMPLATE = 6;
	public static $availableErrors = array(
		PHPMailerConfig::ERROR_RECIPIENT_UNSUBSCRIBED 		=> 'That recipient has unsubscribed.',
		PHPMailerConfig::ERROR_INVALID_EMAIL 				=> 'That email is invalid.',
		PHPMailerConfig::ERROR_TOO_LONG 					=> 'That string is too long.',
		PHPMailerConfig::ERROR_SMTP 						=> 'SMTP returned the following error "%s".',
		PHPMailerConfig::ERROR_INVALID_TEXT_TEMPLATE 		=> 'That text template is invalid.',
		PHPMailerConfig::ERROR_INVALID_HTML_TEMPLATE 		=> 'That HTML template is invalid.'
	);

	public static $requires = array(
		'Template' => '\Orion\SimpleTemplate',
		'ErrorList' => '\Orion\SimpleErrorList',
		'Validator' => '\Orion\SimpleValidator'
	);
}

// Require the required files
require_once('Template/SimpleTemplate/SimpleTemplate.php');
require_once('ErrorList/SimpleErrorList/SimpleErrorList.php');
require_once('Validator/SimpleValidator/SimpleValidator.php');

?>