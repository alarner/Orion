<?php
namespace Orion;
require_once('Vo/StandardVo/AuthModels/AbstractAuthModel.php');
require_once('Vo/StandardVo/config.php');

/*
 * Requires:
 *	- Database
 */

class AuthModelKey extends AbstractAuthModel {

	/*
	 * See AbstractAuthModel for documentation.
	 */
	public function authenticate(array $authParams) {
		$validator = new \Orion\SimpleValidator();
		$validationOptions = new \Orion\ValidatorOptions(
			'array',
			array('keyedValidationOptions' => array(
				'key' => new \Orion\ValidatorOptions('length', array('min' => 1, 'max' => 255))
			))
		);
		$result = $validator->validate($authParams, $validationOptions);
		if(!$result->valid) {
			$authIdentifier = '';
			if(array_key_exists('key', $authParams)) {
				$authIdentifier = $authParams['key'];
			}
			$this->recordAuthAttempt(
				StandardUserVo::AUTH_TYPE_KEY,
				$authIdentifier,
				AuthLoginResult::AUTH_ERROR_INVALID_PARAMS
			);
			throw new AuthModelException('Invalid auth params.');
		}

		$sql = '
		SELECT u.*, uao.`date_expires`
		FROM `user_auth_options` uao
		INNER JOIN `users` u ON u.`user_id`=uao.`user_id`
		WHERE uao.`auth_type`=? AND uao.`auth_identifier`=? LIMIT 1';

		$user = $this->db->query($sql, array(
			StandardUserVo::AUTH_TYPE_KEY,
			$authParams['key'],
		))->singleRow();

		if(!$user) {
			$this->recordAuthAttempt(
				StandardUserVo::AUTH_TYPE_KEY,
				$authParams['key'],
				AuthLoginResult::AUTH_ERROR_USER_NOT_FOUND
			);
			return new AuthLoginResult(
				false,
				AuthLoginResult::AUTH_ERROR_USER_NOT_FOUND
			);
		}

		if($user['is_account_disabled']) {
			$this->recordAuthAttempt(
				StandardUserVo::AUTH_TYPE_KEY,
				$authParams['key'],
				AuthLoginResult::AUTH_ERROR_ACCOUNT_DISABLED
			);
			return new AuthLoginResult(
				false,
				AuthLoginResult::AUTH_ERROR_ACCOUNT_DISABLED
			);
		}

		if(!is_null($user['date_expires']) && (strtotime($user['date_expires']) < time())) {
			$this->recordAuthAttempt(
				StandardUserVo::AUTH_TYPE_KEY,
				$authParams['key'],
				AuthLoginResult::AUTH_ERROR_OPTION_EXPIRED
			);
			return new AuthLoginResult(
				false,
				AuthLoginResult::AUTH_ERROR_OPTION_EXPIRED
			);
		}

		if(StandardVoConfig::$loginRequiresEmailVerification) {
			if(!$user['is_email_verified']) {
				$this->recordAuthAttempt(
					StandardUserVo::AUTH_TYPE_KEY,
					$authParams['key'],
					AuthLoginResult::AUTH_ERROR_EMAIL_NOT_VERIFIED
				);
				return new AuthLoginResult(
					false,
					AuthLoginResult::AUTH_ERROR_EMAIL_NOT_VERIFIED
				);
			}
		}

		$this->recordAuthAttempt(
			StandardUserVo::AUTH_TYPE_KEY,
			$authParams['key'],
			null
		);

		unset($user['date_expires']);

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
				'key' => new \Orion\ValidatorOptions('length', array('min' => 1, 'max' => 255)),
				'set_cookie' => new \Orion\ValidatorOptions('boolean'),
				'cookie_lifetime' => new \Orion\ValidatorOptions('int', array('min' => 0), false),
			))
		);
		$result = $validator->validate($authParams, $validationOptions);
		if(!$result->valid) {
			throw new AuthModelException('Invalid auth params: '.json_encode($authParams));
		}

		$sql = '
		INSERT INTO `user_auth_options`	(`user_id`, `auth_type`, `auth_identifier`, `auth_password`, `nonce`, `date_created`)
		VALUES(?,?,?,?,?,NOW()) ON DUPLICATE KEY UPDATE `user_id` = `user_id`';
		$nonce = $this->generateNonce(StandardVoConfig::$nonceSize);
		$this->db->query($sql, array(
			intval($userId),
			StandardUserVo::AUTH_TYPE_KEY,
			$authParams['key'],
			null,
			$nonce
		));

		if($authParams['set_cookie']) {
			$cookieLifetime = 7*24*60*60;
			if(array_key_exists('cookie_lifetime', $authParams)) {
				$cookieLifetime = intval($authParams['cookie_lifetime']);
			}

			$cookieExpireDate = time() + $cookieLifetime;

			// PHP running from the command line
			if(php_sapi_name() == 'cli' && empty($_SERVER['REMOTE_ADDR'])) {
				throw new AuthModelException('setcookie(\'_user_auth_key\', \''.$authParams['key'].'\', '.$cookieExpireDate.')');
			}
			else {
				setcookie('_user_auth_key', $authParams['key'], $cookieExpireDate, '/');
			}
		}

		return new AuthRegisterResult();
	}

	public function changePassword($authIdentifier, $password) {
		
	}
}
?>