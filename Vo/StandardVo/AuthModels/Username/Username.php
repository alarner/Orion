<?php
namespace Orion;
require_once('Vo/StandardVo/AuthModels/AbstractAuthModel.php');
require_once('Vo/StandardVo/config.php');

/*
 * Requires:
 *	- Database
 */

class AuthModelUsername extends AbstractAuthModel {

	/*
	 * See AbstractAuthModel for documentation.
	 */
	public function authenticate(array $authParams) {
		$validator = new \Orion\SimpleValidator();
		$validationOptions = new \Orion\ValidatorOptions(
			'array',
			array('keyedValidationOptions' => array(
				'username' => new \Orion\ValidatorOptions('length', array('min' => 0, 'max' => 255)),
				'password' => new \Orion\ValidatorOptions('length', array('min' => 0))
			))
		);
		$result = $validator->validate($authParams, $validationOptions);
		if(!$result->valid) {
			$authIdentifier = '';
			if(array_key_exists('username', $authParams)) {
				$authIdentifier = $authParams['username'];
			}
			$this->recordAuthAttempt(
				StandardUserVo::AUTH_TYPE_USERNAME,
				$authIdentifier,
				AuthLoginResult::AUTH_ERROR_INVALID_PARAMS
			);
			throw new AuthModelException('Invalid auth params.');
		}

		$sql = '
		SELECT COUNT(*) AS `num`
		FROM `user_auth_attempts`
		WHERE `auth_type`=? AND `auth_identifier`=? AND `auth_error` IS NOT NULL AND `date` > DATE_SUB(NOW(), INTERVAL ? MINUTE)';

		$result = $this->db->query($sql, array(
			StandardUserVo::AUTH_TYPE_USERNAME,
			$authParams['username'],
			StandardVoConfig::$failedAttemptTimeLimit
		))->singleRow();

		if($result['num'] >= StandardVoConfig::$maxFailedAttempts) {
			return new AuthLoginResult(
				false,
				AuthLoginResult::AUTH_ERROR_EXCESSIVE_FAILED_ATTEMPTS
			);
		}

		$sql = '
		SELECT `user_id`, `auth_type`, `auth_identifier`, `auth_password`, `nonce`, `date_expires`
		FROM `user_auth_options` WHERE `auth_type`=? AND `auth_identifier`=? LIMIT 1';

		$authOption = $this->db->query($sql, array(
			StandardUserVo::AUTH_TYPE_USERNAME,
			$authParams['username'],
		))->singleRow();

		if(!$authOption) {
			$this->recordAuthAttempt(
				StandardUserVo::AUTH_TYPE_USERNAME,
				$authParams['username'],
				AuthLoginResult::AUTH_ERROR_USER_NOT_FOUND
			);
			return new AuthLoginResult(
				false,
				AuthLoginResult::AUTH_ERROR_USER_NOT_FOUND
			);
		}

		if(!is_null($authOption['date_expires']) && (strtotime($authOption['date_expires']) < time())) {
			$this->recordAuthAttempt(
				StandardUserVo::AUTH_TYPE_USERNAME,
				$authParams['username'],
				AuthLoginResult::AUTH_ERROR_OPTION_EXPIRED
			);
			return new AuthLoginResult(
				false,
				AuthLoginResult::AUTH_ERROR_OPTION_EXPIRED
			);
		}

		if($this->hash($authParams['password'], $authOption['nonce']) != $authOption['auth_password']) {
			$this->recordAuthAttempt(
				StandardUserVo::AUTH_TYPE_USERNAME,
				$authParams['username'],
				AuthLoginResult::AUTH_ERROR_INVALID_PASSWORD
			);
			return new AuthLoginResult(
				false,
				AuthLoginResult::AUTH_ERROR_INVALID_PASSWORD
			);
		}

		$sql = 'SELECT * FROM `users` WHERE `user_id` = ? LIMIT 1';
		
		$user = $this->db->query($sql, array($authOption['user_id']))->singleRow();
		if(!$user) {
			$this->recordAuthAttempt(
				StandardUserVo::AUTH_TYPE_USERNAME,
				$authParams['username'],
				AuthLoginResult::AUTH_ERROR_USER_QUERY_FAILED
			);
			throw new AuthModelException('User query failed.');
		}

		if($user['is_account_disabled']) {
			$this->recordAuthAttempt(
				StandardUserVo::AUTH_TYPE_USERNAME,
				$authParams['username'],
				AuthLoginResult::AUTH_ERROR_ACCOUNT_DISABLED
			);
			return new AuthLoginResult(
				false,
				AuthLoginResult::AUTH_ERROR_ACCOUNT_DISABLED,
				$user
			);
		}

		if(StandardVoConfig::$loginRequiresEmailVerification) {
			if(!$user['is_email_verified']) {
				$this->recordAuthAttempt(
					StandardUserVo::AUTH_TYPE_USERNAME,
					$authParams['username'],
					AuthLoginResult::AUTH_ERROR_EMAIL_NOT_VERIFIED
				);
				return new AuthLoginResult(
					false,
					AuthLoginResult::AUTH_ERROR_EMAIL_NOT_VERIFIED,
					$user
				);
			}
		}

		$this->recordAuthAttempt(
			StandardUserVo::AUTH_TYPE_USERNAME,
			$authParams['username'],
			null
		);

		$result = new AuthLoginResult();
		$result->valid = true;
		$result->user = $user;

		$sql = 'UPDATE `users` SET `last_login` = NOW() WHERE `user_id`=?';
		$this->db->query($sql, array($user['user_id']));

		$result->user['last_login'] = Date('Y-m-d H:i:s');

		return $result;
	}

	public function register($userId, array $authParams) {
		$validator = new \Orion\SimpleValidator();
		$validationOptions = new \Orion\ValidatorOptions(
			'array',
			array('keyedValidationOptions' => array(
				'username' => new \Orion\ValidatorOptions('length', array('min' => 0, 'max' => 255)),
				'password' => new \Orion\ValidatorOptions('length', array('min' => 1))
			))
		);
		$result = $validator->validate($authParams, $validationOptions);
		if(!$result->valid) {
			throw new AuthModelException('Invalid auth params: '.json_encode($authParams));
		}

		// Make sure the user isn't already registered
		$sql = '
		SELECT COUNT(*) AS `num`
		FROM `user_auth_options`
		WHERE `auth_type`=? AND `auth_identifier`=?';
		$resultCount = $this->db->query(
			$sql,
			array(StandardUserVo::AUTH_TYPE_USERNAME, $authParams['username'])
		)->singleRow();
		if($resultCount['num'] > 0) {
			return new AuthRegisterResult(
				false,
				AuthRegisterResult::AUTH_ERROR_IDENTIFIER_ALREADY_EXISTS
			);
		}

		$sql = '
		INSERT INTO `user_auth_options`	(`user_id`, `auth_type`, `auth_identifier`, `auth_password`, `nonce`, `date_created`)
		VALUES(?,?,?,?,?, NOW()) ON DUPLICATE KEY UPDATE `auth_password`=?, `nonce`=?';
		$nonce = $this->generateNonce(StandardVoConfig::$nonceSize);
		$hashedPass = $this->hash($authParams['password'], $nonce);
		$this->db->query($sql, array(
			intval($userId),
			StandardUserVo::AUTH_TYPE_USERNAME,
			$authParams['username'],
			$hashedPass,
			$nonce,
			$hashedPass,
			$nonce
		));

		return new AuthRegisterResult();
	}

	public function changePassword($authIdentifier, $password) {
		$sql = '
		UPDATE `user_auth_options` SET `auth_password`=?, `nonce`=? WHERE `auth_type`=? AND `auth_identifier`=?';
		$nonce = $this->generateNonce(StandardVoConfig::$nonceSize);
		$hashedPass = $this->hash($password, $nonce);
		$this->db->query($sql, array(
			$hashedPass,
			$nonce,
			StandardUserVo::AUTH_TYPE_USERNAME,
			$authIdentifier
		));

	}

	private function hash($password, $nonce) {
		return hash_hmac(
			'sha512',
			$password . $nonce,
			StandardVoConfig::$passwordSalt
		);
	}
}
?>