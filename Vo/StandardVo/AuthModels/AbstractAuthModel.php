<?php
namespace Orion;
require_once('Vo/StandardVo/AuthModels/AuthRegisterResult.php');
require_once('Vo/StandardVo/AuthModels/AuthLoginResult.php');
require_once('Vo/StandardVo/AuthModels/AuthModelException.php');

abstract class AbstractAuthModel {
	protected $db;

	/*
	 * @param	$db A database object.
	 */
	public function __construct() {
		require_once(\Orion\StandardVoConfig::$requires['Database']['file']);
		$this->db = call_user_func(\Orion\StandardVoConfig::$requires['Database']['class']);
	}

	protected function generateNonce($size) {
		// 32 - 126 are printable ascii characters
		$return = '';
		for($i=0; $i<$size; $i++) {
			$ascii = rand(32, 126);
			$return .= chr($ascii);
		}
		return $return;
	}

	protected function recordAuthAttempt($authType, $authIdentifier, $result) {
		$sql = '
		INSERT INTO `user_auth_attempts` (`auth_type`, `auth_identifier`, `auth_error`, `ip`, `date`)
		VALUES(?,?,?,?,NOW())';

		$ip = null;
		if(array_key_exists('REMOTE_ADDR', $_SERVER)) {
			$ip = $_SERVER['REMOTE_ADDR'];
		}

		$this->db->query($sql, array($authType, $authIdentifier, $result, $ip));
	}

	/*
	 * @param	$authParams	An array of authentication parameters that are used
	 *						to authenicate the account.
	 *
	 * @throws AuthModelException if the $authParams are invalid.
	 *
	 * @return An AuthLoginResult object.
	 */
	abstract public function authenticate(array $authParams);

	/*
	 * @param	$userId		The user id to associate with the auth option.
	 * @param	$authParams	An array of authentication parameters that are used
	 *						to register the account.
	 *
	 * @throws AuthModelException if the $authParams are invalid.
	 *
	 * @return An AuthRegisterResult object.
	 */
	abstract public function register($userId, array $authParams);

	/*
	 * @param	$authIdentifier		The auth identifier of the user.
	 * @param	$password			The new password.
	 */
	abstract public function changePassword($authIdentifier, $password);
}