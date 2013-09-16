<?php
namespace Orion;
require_once('Emailer/iEmailer.php');
require_once('Emailer/PHPMailer/config.php');
require_once('Emailer/PHPMailer/PHPMailerException.php');
require_once('Emailer/PHPMailer/lib/class.phpmailer.php');

/*
 * @see Emailer/iEmailer.php for documentation.
 */

class PHPMailer implements iEmailer {

	private $db;

	public function __construct(\Orion\iDatabase &$database) {
		$this->db =& $database;
	}

	public function send(\Orion\iEmailParams $params) {
		if(!$params->isValid()) {
			$invalidParams = array();
			if($params->errors()->keyHasErrors('from')) $invalidParams[] = 'from';
			if($params->errors()->keyHasErrors('to')) $invalidParams[] = 'to';
			if($params->errors()->keyHasErrors('subject')) $invalidParams[] = 'subject';
			if($params->errors()->keyHasErrors('templatePathText')) $invalidParams[] = 'templatePathText';
			if($params->errors()->keyHasErrors('templatePathHtml')) $invalidParams[] = 'templatePathHtml';
			throw new \Orion\PHPMailerException('Invalid parameters: '.implode(', ', $invalidParams));
		}
		// Make sure the recipient is valid
		if(!$params->checkSubscribed() || !$this->isUnsubscribed($params->to()->email)) {
			ob_start();
			var_dump($params);
			$paramDump = ob_get_contents();
			ob_end_clean();
			$hash = md5($paramDump.time().PHPMailerConfig::$hashSalt);
			// Add the email to the database
			$emailSql = '
			INSERT INTO `_orion_emails` (
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
				`date_added`,
				`hash`
			) VALUES(?,?,?,?,?,?,?,?,?,?,NOW(),?)';

			$paramString = @json_encode($params->templateParams());

			$this->db->query(
				$emailSql,
				array(
					$params->to()->name,
					$params->to()->email,
					$params->from()->name,
					$params->from()->email,
					$params->subject(),
					$params->templatePathText(),
					$params->templatePathHtml(),
					$paramString,
					$params->checkSubscribed(),
					$params->queued(),
					$hash
				)
			);

			$emailId = $this->db->insertId();

			if(!$params->queued()) {
				$this->sendFromEmailParams($params, $emailId, $hash);
			}

			return true;
		}
		
		// The user has unsubscribed
		return false;
	}

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
	public function unsubscribe($email, $emailId, $emailHash) {
		// Make sure the email matches the email id
		$sql = '
		SELECT COUNT(*) AS `num` FROM `_orion_emails`
		WHERE `email_id` = ? AND `to_email` = ? AND `hash` = ?';

		$result = $this->db->query($sql, array($emailId, $email, $emailHash))->singleRow();
		if($result['num'] > 0) {
			$sql = '
			INSERT INTO `_orion_email_unsubscriptions`
			(`email`, `email_id`, `date_unsubscribed`)
			VALUES(?,?,NOW())';
			$this->db->query($sql, array($email, $emailId));
			return true;
		}
		return false;
	}

	/*
	 * @return true if the user has unsubscribed, else false.
	 */
	public function isUnsubscribed($email) {		
		$sql = '
		SELECT COUNT(*) AS `num` FROM `_orion_email_unsubscriptions`
		WHERE `email` = ?';

		$result = $this->db->query($sql, array($email))->singleRow();
		return $result['num'] > 0;
	}

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
	public function &sendFromDatabase($emailId, $debug = false) {
		/*
		$db = Database::instance();
		$availableErrors = array(
			Emailer::ERROR_BAD_EMAIL_ID => 'Unable to find email id %s',
			Emailer::ERROR_INVALID_PARAMS => 'The loaded email parameters are not valid.',
			Emailer::ERROR_FAILED_TO_SEND => 'The email failed to send.',
		);
		$errors = new \Orion\ErrorList($availableErrors);
		$sql = '
		SELECT
			`email_id`,
			`from_name`,
			`from_email`,
			`subject`,
			`template_path_text`,
			`template_path_html`,
			`template_params`,
			`check_subscribed`,
			`queued`,
			`date_added`,
			`date_executed`,
			`success`
		FROM `_orion_emails`
		WHERE `email_id`=?';
		$result = $db->query($sql, array($emailId))->singleRow();
		if(count($result) > 0) {
			$params = new \Orion\EmailParams();
			$params->setFrom($result['from_name'], $result['from_email']);
			$params->setSubject($result['subject']);
			$params->setTemplatePathText($result['template_path_text']);
			$params->setTemplatePathHtml($result['template_path_html']);
			$params->setTemplateParams(json_decode($result['template_params'], true));
			$params->setCheckSubscribed($result['check_subscribed']);
			$params->setQueued($result['queued']);

			$sql = '
			SELECT `email`, `name`, `email_hash`
			FROM `_orion_recipients`
			WHERE `email_id` = ?';
			$recipients = $db->query($sql, array($result['email_id']))->allRows();

			foreach($recipients as $r) {
				$params->addRecipient($r['name'], $r['email'], $r['email_hash']);
			}

			if($params->isValid()) {
				$sendErrors =& $this->sendFromEmailParams($params, $result['email_id'], $debug);
				if($sendErrors->hasErrors()) {
					$errors->addError(Emailer::ERROR_FAILED_TO_SEND);
				}
			}
			else {
				$errors->addError(Emailer::ERROR_INVALID_PARAMS);
			}
		}
		else {
			$errors->addError(Emailer::ERROR_BAD_EMAIL_ID, array($emailId));
		}
		return $errors;
		*/
	}

	private function &sendFromEmailParams(
		\Orion\iEmailParams &$params,
		$emailId,
		$hash,
		$debug = false) {

		$errorList = new PHPMailerConfig::$requires['ErrorList'](PHPMailerConfig::$availableErrors);

		$templateParams = $params->templateParams();
		$templateParams['_orion_email_id'] = $emailId;
		$templateParams['_orion_email_hash'] = $hash;

		// Text version of the email
		$text = '';
		$templatePathText = $params->templatePathText();
		if(is_array($templatePathText)) {
			$templateIndex = array_rand($templatePathText, 1);
			$templatePathText = $templatePathText[$templateIndex];
		}
		if(strlen($templatePathText) > 0) {
			$textTemplate = new PHPMailerConfig::$requires['Template'](
				$templatePathText,
				$templateParams
			);

			$text = $textTemplate->render(true);
		}

		// Html version of the email
		$html = '';
		$templatePathHtml = $params->templatePathHtml();
		if(is_array($templatePathHtml)) {
			$templateIndex = array_rand($templatePathHtml, 1);
			$templatePathHtml = $templatePathHtml[$templateIndex];
		}
		if(strlen($templatePathHtml) > 0) {
			$htmlTemplate = new PHPMailerConfig::$requires['Template'](
				$templatePathHtml,
				$templateParams
			);

			$html = $htmlTemplate->render(true);
		}

		$mail = new \PHPMailer();

		$mail->IsSMTP();											// Set mailer to use SMTP
		$mail->Host 	= PHPMailerConfig::$host;					// Specify main and backup server
		$mail->SMTPAuth = PHPMailerConfig::$auth;					// Enable SMTP authentication
		$mail->Username = PHPMailerConfig::$username;				// SMTP username
		$mail->Password = PHPMailerConfig::$password;				// SMTP password

		$mail->From = $params->from()->email;
		$mail->FromName = $params->from()->name;
		$mail->AddAddress($params->to()->email, $params->to()->name);	// Add a recipient

		// $mail->WordWrap = 50;                                 // Set word wrap to 50 characters
		// $mail->AddAttachment('/var/tmp/file.tar.gz');         // Add attachments
		// $mail->AddAttachment('/tmp/image.jpg', 'new.jpg');    // Optional name

		if($html) {
			$mail->IsHTML(true);
		}

		$mail->Subject = $params->subject();
		$mail->Body    = $html;
		$mail->AltBody = $text;

		$success = 1;
		if(!$mail->Send()) {
			$errorList->addError(PHPMailerConfig::ERROR_SMTP, array($result), 'smtp');
			$success = 0;
		}

		$sql = '
		UPDATE `_orion_emails`
		SET `date_executed` = NOW(), `success` = ?
		WHERE `email_id` = ?';

		$this->db->query($sql, array($success, $emailId));

		return $errorList;
	}
}
?>