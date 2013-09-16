<?php
namespace Orion;
class StandardVoConfig {
	public static $keyLength = 128;
	public static $emailVerification = array(
		'keyLifetime' => 168, // in hours
		'fromName' => 'Orion',
		'fromEmail' => 'test@aisnh.com',
		'subject' => 'Welcome!',
		//'templatePathText' => 'test',
		'templatePathHtml' => 'views/emails/emailVerification.php'
	);

	public static $emailPasswordReset = array(
		'keyLifetime' => 168, // in hours
		'fromName' => 'Orion',
		'fromEmail' => 'test@aisnh.com',
		'subject' => 'Reset your password',
		//'templatePathText' => 'view/emails/passwordReset.php',
		'templatePathHtml' => 'views/emails/passwordReset.php'
	);

	public static $requires = array(
		'Emailer' => array(
			'class' => '\Orion\PHPMailer',
			'file' => 'Emailer/PHPMailer/PHPMailer.php'
		),
		'EmailParams' => array(
			'class' => '\Orion\PHPMailerParams',
			'file' => 'Emailer/PHPMailer/PHPMailerParams.php'
		),
		'Database' => array(
			'class' => '\Orion\MySQLPDODatabase::instance',
			'file' => 'Database/MySQLPDODatabase/MySQLPDODatabase.php'
		)
	);

	public static $passwordSalt = 'sd2la07w8ZHAd30yA)3a&*SfhPOSiS3';
	public static $maxFailedAttempts = 10;
	public static $failedAttemptTimeLimit = 5; // in minutes
	public static $nonceSize = 64;
	public static $loginRequiresEmailVerification = false;
}
?>
