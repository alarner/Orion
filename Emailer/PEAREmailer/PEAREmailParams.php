<?php
namespace Orion;
require_once('Emailer/iEmailParams.php');
require_once('Emailer/PEAREmailer/config.php');
require_once('Emailer/EmailAddress.php');
require_once('Validator/ValidatorOptions.php');

/*
 * A class to hold all of the paramters required in order to send an email.
 */
class PEAREmailParams implements iEmailParams {
	private $errors;
	private $validator;

	private $to;
	private $from;
	private $subject;
	private $checkSubscribed;
	private $queued;
	private $templatePathText;
	private $templatePathHtml;
	private $templateParams;

	function __construct() {
		$this->from = null;
		$this->validator = new PEAREmailerConfig::$requires['Validator'];

		$this->errors = new PEAREmailerConfig::$requires['ErrorList'](PEAREmailerConfig::$availableErrors);

		$this->params = new \stdClass();

		$this->checkSubscribed = true;
		$this->queued = false;

		$this->templatePathText = null;
		$this->templatePathHtml = null;
		$this->templateParams = null;
	}

	public function setTo($name, $email) {
		$this->errors->clearKey('to');
		$this->to = new EmailAddress($name, $email);
		$options = new \Orion\ValidatorOptions('email');
		$result = $this->validator->validate($email, $options);
		if(!$result->valid) {
			$this->errors->addError(PEAREmailerConfig::ERROR_INVALID_EMAIL, array(), 'to');
		}
	}


	public function to() {return $this->to;}

	public function setFrom($name, $email) {
		$this->errors->clearKey('from');
		$this->from = new EmailAddress($name, $email);
		$options = new \Orion\ValidatorOptions('email');
		$result = $this->validator->validate($email, $options);
		if(!$result->valid) {
			$this->errors->addError(PEAREmailerConfig::ERROR_INVALID_EMAIL, array(), 'from');
		}
	}

	public function from() {return $this->from;}

	public function setSubject($subject) {
		$this->errors->clearKey('subject');
		$this->subject = $subject;
		$options = new \Orion\ValidatorOptions('length', array('max' => 78));
		$result = $this->validator->validate($subject, $options);
		if(!$result->valid) {
			$this->errors->addError(PEAREmailerConfig::ERROR_TOO_LONG, array(), 'subject');
		}
	}

	public function subject() {return $this->subject;}

	public function setTemplatePathText($pathOrArray) {
		$this->templatePathText = $pathOrArray;

		$textTemplateValid = false;
		if(is_array($this->templatePathText)) {
			$textTemplateValid = true;
			foreach($this->templatePathText as $template) {
				if(strlen($template) == 0) {
					$textTemplateValid = false;
					break;
				}
			}

		}
		else {
			$textTemplateValid = (strlen($this->templatePathText) > 0);
		}

		if(!$textTemplateValid) {
			$this->errors->addError(PEAREmailerConfig::ERROR_INVALID_TEXT_TEMPLATE, array(), 'templatePathText');
		}
	}

	public function setTemplatePathHtml($path) {
		$this->templatePathHtml = $path;

		$htmlTemplateValid = false;
		if(is_array($this->templatePathHtml)) {
			$htmlTemplateValid = true;
			foreach($this->templatePathHtml as $template) {
				if(strlen($template) == 0) {
					$htmlTemplateValid = false;
					break;
				}
			}

		}
		else {
			$htmlTemplateValid = (strlen($this->templatePathHtml) > 0);
		}

		if(!$htmlTemplateValid) {
			$this->errors->addError(PEAREmailerConfig::ERROR_INVALID_HTML_TEMPLATE, array(), 'templatePathHtml');
		}
	}

	public function templatePathText() {
		return $this->templatePathText;
	}

	public function templatePathHtml() {
		return $this->templatePathHtml;
	}

	public function setTemplateParams($params) {
		$this->templateParams = $params;
	}

	public function templateParams() {
		$return = array();
		if(!empty($this->templateParams)) {
			$return = $this->templateParams;
		}
		return $return;
	}

	public function setCheckSubscribed($val) {
		$this->checkSubscribed = $val;
	}

	public function checkSubscribed() {return $this->checkSubscribed;}

	public function setQueued($val) {
		$this->queued = $val;
	}

	public function queued() {return $this->queued;}

	public function isValid() {
		$valid = true;
		$valid = $valid && !$this->errors->keyHasErrors('from');	// valid from address
		$valid = $valid && !$this->errors->keyHasErrors('to');		// valid to address
		$valid = $valid && !$this->errors->keyHasErrors('subject');	// valid subject
		$valid = $valid && !(
			$this->errors->keyHasErrors('templatePathText') && 		// valid template path
			$this->errors->keyHasErrors('templatePathHtml')
		); 
		return $valid;
	}

	public function &errors() {
		return $this->errors;
	}
}
?>