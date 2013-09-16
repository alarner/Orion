<?php
namespace Orion;

/*
 * Stores information about an email recipient including their name, email, and hash. 
 * The hash can be used to allow them to unsubscribe to an email.
 */
class EmailAddress {
	public $name;	// Recipient name
	public $email;	// Recipient email

	function __construct($name = null, $email = null) {
		$this->name = $name;
		$this->email = $email;
	}

	/*
	 * @return a composite email address in the format "name <email>"
	 */
	public function compositeAddress() {
		if(strlen($this->name) > 0) {
			return $this->name.' <'.$this->email.'>';
		}
		return $this->email;
	}
}
?>