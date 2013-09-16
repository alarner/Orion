<?php
namespace Orion;
require_once('Vo/StandardVo/StandardVo.php');

/*
 * Requires:
 *  - StandardVo
 *	- Database
 *  - Validator
 *  - Emailer
 */

class StandardUserVo extends StandardVo {

	const EXCEPTION_NO_LOGGED_IN_USER 		= 1000;
	const EXCEPTION_INVALID_LOGGED_IN_USER 	= 1001;
	const EXCEPTION_MISSING_USER_ID 		= 1002;

	const KEY_TYPE_EMAIL_VERIFICATION 		= 1;
	const KEY_TYPE_PASSWORD_RESET 			= 2;

	const AUTH_TYPE_USERNAME 				= 1;
	const AUTH_TYPE_FACEBOOK 				= 2;
	const AUTH_TYPE_KEY 					= 3;

	private $permissions 					= array();

	/*
	 * Loads the user data from the session.
	 */
	public function loadLoggedInUser() {
		if(!array_key_exists('_loggedInUser', $_SESSION) || !is_array($_SESSION['_loggedInUser'])) {
			return false;
		}

		$this->setMultiple($_SESSION['_loggedInUser']);
		if(!array_key_exists('_loggedInUserPermissions', $_SESSION) || !is_array($_SESSION['_loggedInUserPermissions'])) {
			$this->loadPermissions(null, true);
		}
		else {
			$this->loadPermissions($_SESSION['_loggedInUserPermissions'], true);
		}

		return true;
	}

	/*
	 * Loads the user data from an email address.
	 */
	public function loadFromEmail($email) {
		$sql = 'SELECT * FROM `users` WHERE `email`=? LIMIT 1';
		$row = $this->getDb()->query($sql, array($email))->singleRow();
		if($row) {
			$this->setMultiple($row);
			$this->loadPermissions(null, true);
			return true;
		}

		return false;
	}

	/*
	 * Sends an email with a link for the user to verify their email address.
	 */
	public function sendEmailVerificationEmail() {
		if($this->get('user_id') === null) {
			throw new StandardVoException(
				'Cannot send verification email without a valid user_id.',
				StandardUserVo::EXCEPTION_MISSING_USER_ID
			);
		}
		
		$verificationKey = $this->generateKey(
			$this->get('user_id'),
			StandardVoConfig::$keyLength,
			StandardUserVo::KEY_TYPE_EMAIL_VERIFICATION,
			StandardVoConfig::$emailVerification['keyLifetime']
		);

		require_once(\Orion\StandardVoConfig::$requires['Emailer']['file']);
		$emailer = new \Orion\StandardVoConfig::$requires['Emailer']['class']($this->getDb());
		require_once(\Orion\StandardVoConfig::$requires['EmailParams']['file']);
		$emailParams = new \Orion\StandardVoConfig::$requires['EmailParams']['class'];
		$emailParams->setFrom(
			StandardVoConfig::$emailVerification['fromName'],
			StandardVoConfig::$emailVerification['fromEmail']
		);
		$emailParams->setSubject(
			StandardVoConfig::$emailVerification['subject']
		);
		$emailParams->setTo(null, $this->get('email'));
		if(array_key_exists('templatePathText', StandardVoConfig::$emailVerification)) {
			$emailParams->setTemplatePathText(StandardVoConfig::$emailVerification['templatePathText']);
		}
		if(array_key_exists('templatePathHtml', StandardVoConfig::$emailVerification)) {
			$emailParams->setTemplatePathHtml(StandardVoConfig::$emailVerification['templatePathHtml']);
		}

		$emailParams->setTemplateParams(
			array(
				'user' => $this,
				'verificationKey' => $verificationKey
			)
		);
		
		return $emailer->send($emailParams);
	}

	public function verifyEmail($verificationKey, $autoLogin = false) {
		$userId = $this->useKey($verificationKey, StandardUserVo::KEY_TYPE_EMAIL_VERIFICATION);
		if($userId === false) return false;

		$sql = 'UPDATE `users` SET `is_email_verified`=1 WHERE `user_id`=?';
		$this->getDb()->query($sql, array($userId));

		if($autoLogin) {
			$this->loginFromUserId($userId);
		}
		return true;
	}

	public function loginFromUserId($userId) {
		$sql = 'SELECT * FROM `users` WHERE `user_id`=?';
		$row = $this->getDb()->query($sql, array($userId))->singleRow();
		if($row) {
			$_SESSION['_loggedInUser'] = $row;
			$this->setMultiple($row);
			return true;
		}
		$this->loadPermissions(null, true);

		return false;
	}

	/*
	 * Emails the user a page to reset their password.
	 */
	public function sendPasswordResetEmail() {
		if($this->get('user_id') === null) {
			throw new StandardVoException(
				'Cannot send password reset email without a valid user_id.',
				StandardUserVo::EXCEPTION_MISSING_USER_ID
			);
		}
		
		$resetKey = $this->generateKey(
			$this->get('user_id'),
			StandardVoConfig::$keyLength,
			StandardUserVo::KEY_TYPE_PASSWORD_RESET,
			StandardVoConfig::$emailPasswordReset['keyLifetime']
		);

		require_once(\Orion\StandardVoConfig::$requires['Emailer']['file']);
		$emailer = new \Orion\StandardVoConfig::$requires['Emailer']['class']($this->getDb());
		require_once(\Orion\StandardVoConfig::$requires['EmailParams']['file']);
		$emailParams = new \Orion\StandardVoConfig::$requires['EmailParams']['class'];
		$emailParams->setFrom(
			StandardVoConfig::$emailPasswordReset['fromName'],
			StandardVoConfig::$emailPasswordReset['fromEmail']
		);
		$emailParams->setSubject(
			StandardVoConfig::$emailPasswordReset['subject']
		);
		$emailParams->setTo(null, $this->get('email'));
		if(array_key_exists('templatePathText', StandardVoConfig::$emailPasswordReset)) {
			$emailParams->setTemplatePathText(StandardVoConfig::$emailPasswordReset['templatePathText']);
		}
		if(array_key_exists('templatePathHtml', StandardVoConfig::$emailPasswordReset)) {
			$emailParams->setTemplatePathHtml(StandardVoConfig::$emailPasswordReset['templatePathHtml']);
		}

		$emailParams->setTemplateParams(
			array(
				'user' => $this,
				'resetKey' => $resetKey
			)
		);
		
		return $emailer->send($emailParams);
	}

	/*
	 * Register auth option
	 */
	public function registerAuthOption($authType, $authParams, $autoLogin = false) {
		if($this->get('user_id') === null) {
			throw new StandardVoException(
				'Cannot register auth option without a valid user_id.',
				StandardUserVo::EXCEPTION_MISSING_USER_ID
			);
		}
		$authModel = $this->buildAuthModelFromAuthType($authType);
		$result = $authModel->register($this->get('user_id'), $authParams);

		if($result->valid && $autoLogin) {
			$this->loginFromUserId($this->get('user_id'));
		}

		return $result;
	}

	/*
	 * Change tha auth password
	 */
	public function changeAuthPassword($authType, $authIdentifier, $password) {
		$authModel = $this->buildAuthModelFromAuthType($authType);
		$authModel->changePassword($authIdentifier, $password);
	}

	/*
	 * Logs the user into the system.
	 */
	public function logIn($authType, $authParams) {
		$authModel = $this->buildAuthModelFromAuthType($authType);
		$result = $authModel->authenticate($authParams);
		if($result->valid) {
			unset($result->user['password']);
			$_SESSION['_loggedInUser'] = $result->user;
			$this->setMultiple($result->user);
		}
		return $result;
	}

	/*
	 * @return true if the user is logged in, else false
	 */
	public function isLoggedIn() {
		if(empty($_SESSION)) return false;
		return 	array_key_exists('_loggedInUser', $_SESSION) &&
				is_array($_SESSION['_loggedInUser']) &&
				($_SESSION['_loggedInUser']['user_id'] == $this->get('user_id'));
	}

	/*
	 * Logs out currently logged in user.
	 */
	public function logOut() {
		// First get user auth options and log out from each option
		if(array_key_exists('_user_auth_key', $_COOKIE)) {
			setcookie('_user_auth_key', '', 0, '/');
			$sql = '
			UPDATE `user_auth_options`
			SET `date_expires`=NOW()
			WHERE `user_id`=? AND `auth_type`=? AND `auth_identifier`=?';
			$this->getDb()->query($sql, array(
				$this->get('user_id'),
				StandardUserVo::AUTH_TYPE_KEY,
				$_COOKIE['_user_auth_key']
			));
		}
		unset($_SESSION['_loggedInUser']);
		unset($_SESSION['_loggedInUserPermissions']);
	}

	/*
	 * @return true if the users email address is currently in use by another user, else false.
	 */
	public function isEmailInUse() {
		$params = array($this->get('email'));
		$sql = '
		SELECT COUNT(*) AS `num` FROM `users` WHERE `email`=?';
		if(!is_null($this->get('user_id'))) {
			$params[] = $this->get('user_id');
			$sql .= ' AND `user_id` != ?';
		}

		$result = $this->getDb()->query($sql, $params)->singleRow();
		return ($result['num'] > 0);
	}

	/*
	 * @param	$fieldName 	the name of the property to set.
	 *
	 * @param	$value 	the value to set.
	 *
	 * @throws an error if the property doesn't exist.
	 * @throws an error if the value fails validation.
	 */
	public function set($fieldName, $value) {
		parent::set($fieldName, $value);
		if($this->isLoggedIn()) {
			$_SESSION['_loggedInUser'][$fieldName] = $value;
		}
	}

	/*
	 * Loads the permissions for this user.
	 *
	 * @param $permissions 	An array keyed by permission code with values of one or zero to
	 * 						specify whether or not the user has the associated permission code.
	 *
	 * @throws an error if the user id is not specified
	 */
	public function loadPermissions($permissions = null) {
		if($this->get('user_id') === null) {
			throw new StandardVoException(
				'Cannot load permissions without a valid user_id.',
				StandardUserVo::EXCEPTION_MISSING_USER_ID
			);
		}
		if($this->get('user_id') && is_null($permissions)) {
			$sql = 'SELECT `permission_code`, `value` FROM `user_permissions` WHERE `user_id`=?';
			$permissionRows = $this->getDb()->query($sql, array($this->get('user_id')))->allRows();
			$this->permissions = array();
			foreach($permissionRows as $p) {
				$this->permissions[$p['permission_code']] = intval($p['value']);
			}
		}
		else if(is_array($permissions)) {
			$this->permissions = $permissions;
		}
		else {
			$this->permissions = array();
		}

		
		$_SESSION['_loggedInUserPermissions'] = $this->permissions;
	}

	/*
	 * Determines if the user has the specified permission or not.
	 *
	 * @param $permissionCode 	An integer value specified in mvc/UserPermissions.php that
	 *							denotes an individual permission that a user may or may not have.
	 *
	 * @return 1 or 0 depending on whether or not the user has the permission associated with the
	 *			passed in permission code.
	 */
	public function hasPermission($permissionCode) {
		$permissionCode = intval($permissionCode);
		if(!array_key_exists($permissionCode, $this->permissions)) return false;
		return intval($this->permissions[$permissionCode]);
	}

	/*
	 * Set a permission value for this user.
	 *
	 * @param $permissionCode 	An integer value specified in mvc/UserPermissions.php that
	 *							denotes an individual permission that a user may or may not have.
	 * @param $value 			A value, 1 or 0, depending on whether or not the user should have
	 *							the permission associated with the passed in permission code.
	 *
	 * @throws an error if the user id is not specified
	 */
	public function setPermission($permissionCode, $value) {
		if($this->get('user_id') === null) {
			throw new StandardVoException(
				'Cannot set a permission without a valid user_id.',
				StandardUserVo::EXCEPTION_MISSING_USER_ID
			);
		}
		$permissionCode = intval($permissionCode);
		$value = intval($value);
		$value = $value ? 1 : 0;
		$sql = '
		INSERT INTO `user_permissions` (`user_id`, `permission_code`, `value`)
		VALUES(?,?,?) ON DUPLICATE KEY UPDATE `value`=?';
		$this->getDb()->query($sql, array($this->get('user_id'), $permissionCode, $value, $value));
		$this->permissions[$permissionCode] = $value;
		if(array_key_exists('_loggedInUserPermissions', $_SESSION) && is_array($_SESSION['_loggedInUserPermissions'])) {
			$_SESSION['_loggedInUserPermissions'][$permissionCode] = $value;
		}
	}

	/*
	 * Marks a key as used (sets the date_used field to now)
	 *
	 * @param	$key 		The key to use.
	 * @param	$keyType 	The type of the key.
	 *
	 * @return 	The user_id associated with the key or false if the key was not found.
	 */
	public function useKey($key, $keyType) {
		$sql = '
		SELECT `user_id`
		FROM `user_keys`
		WHERE
			`user_key`=? AND
			`type`=? AND 
			`date_expires` > NOW() AND
			`date_used` IS NULL
		LIMIT 1';

		$row = $this->getDb()->query($sql, array(
			$key,
			$keyType
		))->singleRow();

		if(!$row) return false;

		$sql = 'UPDATE `user_keys` SET `date_used`=NOW() WHERE `user_key`=?';
		$this->getDb()->query($sql, array($key));
		return $row['user_id'];
	}

	/*
	 * Generates a key used for verifying an email or resetting a password.
	 *
	 * @param	$userId 	The id of the user to generate the key for.
	 * @param	$length 	The length of the key.
	 * @param	$type 		The key type (either for email verification or password reset).
	 * @param 	$lifetime	How long the key will be valid (in hours).
	 *
	 * @return 	The generated key.
	 */
	private function generateKey($userId, $length, $type, $lifetime) {
		$unique = false;
		$sql = '
		SELECT COUNT(*) AS num
		FROM user_keys
		WHERE user_key=?';
		$key = '';
		$db =& $this->getDb();
		$db->query('START TRANSACTION');
		while(!$unique) {
			$key = '';
			for($i=0; $i<$length; $i++) {
				$ascii = rand(0, 61);
				// 0-9
				if($ascii <= 9) {
					$ascii += 48;
				}
				// A-Z
				else if($ascii <= 35) {
					$ascii += 55;
				}
				// a-z
				else {
					$ascii += 61;
				}
				$key .= chr($ascii);
			}
			$result = $db->query($sql, array($key))->singleRow();
			$unique = ($result['num'] == 0);
		}
		$sql = '
		INSERT INTO user_keys
		(user_key, user_id, type, date_created, date_expires)
		VALUES(?,?,?,NOW(), DATE_ADD(NOW(), INTERVAL ? HOUR))';
		$db->query($sql, array($key, $userId, $type, $lifetime));
		$db->query('COMMIT');
		return $key;
	}

	private function buildAuthModelFromAuthType($authenticationType) {
		switch($authenticationType) {
			case StandardUserVo::AUTH_TYPE_USERNAME:
				require_once('Vo/StandardVo/AuthModels/Username/Username.php');
				return new AuthModelUsername();
			case StandardUserVo::AUTH_TYPE_FACEBOOK:
				require_once('Vo/StandardVo/AuthModels/Facebook/Facebook.php');
				return new AuthModelFacebook();
			case StandardUserVo::AUTH_TYPE_KEY:
				require_once('Vo/StandardVo/AuthModels/Key/Key.php');
				return new AuthModelKey();
		}

		throw new StandardVoException('Invalid authentication type: '.$authenticationType);
	}
}
?>