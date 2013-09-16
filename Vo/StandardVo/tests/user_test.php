<?php
require_once(dirname(__FILE__).'/../../../config.php');
set_include_path(get_include_path() . PATH_SEPARATOR . OrionConfig::$basePath);
require_once('Vo/StandardVo/tests/UserVo.php');
require_once('Vo/StandardVo/config.php');
require_once('Database/MySQLPDODatabase/MySQLPDODatabase.php');

class UserVoTest extends PHPUnit_Framework_TestCase {
	public function testSendEmailVerificationEmail() {
		\Orion\StandardVoConfig::$emailVerification['templatePathText'] = null;
		\Orion\StandardVoConfig::$emailVerification['templatePathHtml'] = 
			'Vo/StandardVo/tests/test_verification_email_template.php';

		$user = new UserVo();
		$user->set('user_id', 7);
		$user->set('email', 'aero4x@gmail.com');
		$user->set('is_email_verified', false);
		$user->set('is_account_disabled', false);

		$result = $user->sendEmailVerificationEmail();
	}

	public function testSendEmailPasswordResetEmail() {
		\Orion\StandardVoConfig::$emailPasswordReset['templatePathText'] = null;
		\Orion\StandardVoConfig::$emailPasswordReset['templatePathHtml'] = 
			'Vo/StandardVo/tests/test_password_reset_email_template.php';

		$user = new UserVo();
		$user->set('user_id', 7);
		$user->set('email', 'aero4x@gmail.com');
		$user->set('is_email_verified', false);
		$user->set('is_account_disabled', false);

		$result = $user->sendPasswordResetEmail();
	}

	public function testCreateUser() {
		$user = new UserVo();
		$user->set('email', 'aero4x@gmail.com');
		$user->set('is_email_verified', false);
		$user->set('is_account_disabled', false);
		$user->save();
	}

	public function testRegisterUserAuthMethod() {
		$this->truncateTables();
		$user = new UserVo();
		$user->set('email', 'aaron@babblespark.com');
		$user->set('is_email_verified', false);
		$user->set('is_account_disabled', false);
		$user->save();
		$user->registerAuthOption(
			\Orion\StandardUserVo::AUTH_TYPE_USERNAME,
			array('username' => $user->get('email'), 'password' => 'test')
		);
	}

	public function testAuthenticateUser() {
		$this->truncateTables();
		$user = new UserVo();
		$user->set('email', 'aaron@babblespark.com');
		$user->set('is_email_verified', false);
		$user->set('is_account_disabled', false);
		$user->save();
		$user->registerAuthOption(
			\Orion\StandardUserVo::AUTH_TYPE_USERNAME,
			array('username' => $user->get('email'), 'password' => 'test')
		);
		$result = $user->logIn(
			\Orion\StandardUserVo::AUTH_TYPE_USERNAME,
			array('username' => $user->get('email'), 'password' => 'test')
		);
		$this->assertEquals(true, $result->valid);
	}

	/**
	 * @expectedException 			\Orion\AuthModelException
	 * @expectedExceptionMessage	Invalid auth params.
	 */
	public function testAuthenticateUserInvalidParams() {
		$this->truncateTables();
		$user = new UserVo();
		$user->set('email', 'aaron@babblespark.com');
		$user->set('is_email_verified', false);
		$user->set('is_account_disabled', false);
		$user->save();
		$user->registerAuthOption(
			\Orion\StandardUserVo::AUTH_TYPE_USERNAME,
			array('username' => $user->get('email'), 'password' => 'test')
		);
		$result = $user->logIn(
			\Orion\StandardUserVo::AUTH_TYPE_USERNAME,
			array('username' => $user->get('email'), 'password' => 'test')
		);
		$this->assertEquals(true, $result->valid);

		$result = $user->logIn(
			\Orion\StandardUserVo::AUTH_TYPE_USERNAME,
			array('username' => $user->get('email'), 'face' => 'test')
		);
		$this->assertEquals(true, $result->valid);
	}

	public function testAuthenticateUserTooManyAttempts() {
		$this->truncateTables();
		$user = new UserVo();
		$user->set('email', 'aaron@babblespark.com');
		$user->set('is_email_verified', false);
		$user->set('is_account_disabled', false);
		$user->save();
		$user->registerAuthOption(
			\Orion\StandardUserVo::AUTH_TYPE_USERNAME,
			array('username' => $user->get('email'), 'password' => 'test')
		);
		for($i=0; $i<10; $i++) {
			$result = $user->logIn(
				\Orion\StandardUserVo::AUTH_TYPE_USERNAME,
				array('username' => $user->get('email'), 'password' => '1')
			);
			$this->assertEquals(false, $result->valid);
			$this->assertEquals(
				\Orion\AuthLoginResult::AUTH_ERROR_INVALID_PASSWORD,
				$result->errorCode
			);
		}

		$result = $user->logIn(
			\Orion\StandardUserVo::AUTH_TYPE_USERNAME,
			array('username' => $user->get('email'), 'password' => '1')
		);
		$this->assertEquals(false, $result->valid);
		$this->assertEquals(
			\Orion\AuthLoginResult::AUTH_ERROR_EXCESSIVE_FAILED_ATTEMPTS,
			$result->errorCode
		);
	}

	public function testAuthenticateUserNotFound() {
		$this->truncateTables();
		$user = new UserVo();
		$user->set('email', 'aaron@babblespark.com');
		$user->set('is_email_verified', false);
		$user->set('is_account_disabled', false);
		$user->save();

		$result = $user->logIn(
			\Orion\StandardUserVo::AUTH_TYPE_USERNAME,
			array('username' => $user->get('email'), 'password' => '1')
		);
		$this->assertEquals(false, $result->valid);
		$this->assertEquals(
			\Orion\AuthLoginResult::AUTH_ERROR_USER_NOT_FOUND,
			$result->errorCode
		);
	}

	/**
	 * @expectedException 			\Orion\AuthModelException
	 * @expectedExceptionMessage	User query failed.
	 */
	public function testAuthenticateUserQueryFailed() {
		$this->truncateTables();
		$user = new UserVo();
		$user->set('email', 'aaron@babblespark.com');
		$user->set('is_email_verified', false);
		$user->set('is_account_disabled', false);
		$user->save();
		$user->registerAuthOption(
			\Orion\StandardUserVo::AUTH_TYPE_USERNAME,
			array('username' => $user->get('email'), 'password' => 'test')
		);

		$db = \Orion\MySQLPDODatabase::instance();
		$db->query('DELETE FROM `users` WHERE `user_id`=?', array($user->get('user_id')));

		$result = $user->logIn(
			\Orion\StandardUserVo::AUTH_TYPE_USERNAME,
			array('username' => $user->get('email'), 'password' => 'test')
		);
		$this->assertEquals(false, $result->valid);
		$this->assertEquals(
			\Orion\AuthLoginResult::AUTH_ERROR_USER_QUERY_FAILED,
			$result->errorCode
		);
	}

	public function testAuthenticateAccountDisabled() {
		$this->truncateTables();
		$user = new UserVo();
		$user->set('email', 'aaron@babblespark.com');
		$user->set('is_email_verified', false);
		$user->set('is_account_disabled', true);
		$user->save();
		$user->registerAuthOption(
			\Orion\StandardUserVo::AUTH_TYPE_USERNAME,
			array('username' => $user->get('email'), 'password' => 'test')
		);
		$result = $user->logIn(
			\Orion\StandardUserVo::AUTH_TYPE_USERNAME,
			array('username' => $user->get('email'), 'password' => 'test')
		);
		$this->assertEquals(false, $result->valid);
		$this->assertEquals(
			\Orion\AuthLoginResult::AUTH_ERROR_ACCOUNT_DISABLED,
			$result->errorCode
		);
	}

	public function testAuthenticateUserEmailNotVerified() {
		\Orion\StandardVoConfig::$loginRequiresEmailVerification = true;
		$this->truncateTables();
		$user = new UserVo();
		$user->set('email', 'aaron@babblespark.com');
		$user->set('is_email_verified', false);
		$user->set('is_account_disabled', false);
		$user->save();
		$user->registerAuthOption(
			\Orion\StandardUserVo::AUTH_TYPE_USERNAME,
			array('username' => $user->get('email'), 'password' => 'test')
		);
		$result = $user->logIn(
			\Orion\StandardUserVo::AUTH_TYPE_USERNAME,
			array('username' => $user->get('email'), 'password' => 'test')
		);
		$this->assertEquals(false, $result->valid);
		$this->assertEquals(
			\Orion\AuthLoginResult::AUTH_ERROR_EMAIL_NOT_VERIFIED,
			$result->errorCode
		);
		\Orion\StandardVoConfig::$loginRequiresEmailVerification = false;
	}

	public function testRegisterKeyAuthMethod() {
		$this->truncateTables();
		$user = new UserVo();
		$user->set('email', 'aaron@babblespark.com');
		$user->set('is_email_verified', false);
		$user->set('is_account_disabled', false);
		$user->save();
		$user->registerAuthOption(
			\Orion\StandardUserVo::AUTH_TYPE_KEY,
			array('key' => md5($user->get('email')), 'set_cookie' => false)
		);
	}

	/**
	 * @expectedException 			Orion\AuthModelException
	 * @expectedExceptionMessage 	Invalid auth params: {"key":"f67b8ed84c165d45e278356d1faaf349","set_cookie":false,"foo":"bar"}
	 */
	public function testRegisterKeyAuthMethodInvalidParams() {
		$this->truncateTables();
		$user = new UserVo();
		$user->set('email', 'aaron@babblespark.com');
		$user->set('is_email_verified', false);
		$user->set('is_account_disabled', false);
		$user->save();
		$user->registerAuthOption(
			\Orion\StandardUserVo::AUTH_TYPE_KEY,
			array('key' => md5($user->get('email')), 'set_cookie' => false, 'foo' => 'bar')
		);
	}

	/**
	 * @expectedException 			Orion\AuthModelException
	 * @expectedExceptionMessage 	setcookie('_user_auth_key', 'f67b8ed84c165d45e278356d1faaf349', 
	 */
	public function testRegisterKeyAuthMethodWithCookie() {
		$this->truncateTables();
		$user = new UserVo();
		$user->set('email', 'aaron@babblespark.com');
		$user->set('is_email_verified', false);
		$user->set('is_account_disabled', false);
		$user->save();
		$user->registerAuthOption(
			\Orion\StandardUserVo::AUTH_TYPE_KEY,
			array('key' => md5($user->get('email')), 'set_cookie' => true)
		);
	}

	/**
	 * @expectedException 			Orion\AuthModelException
	 * @expectedExceptionMessage 	setcookie('_user_auth_key', 'f67b8ed84c165d45e278356d1faaf349', 
	 */
	public function testRegisterKeyAuthMethodWithCookieAndLifetime() {
		$this->truncateTables();
		$user = new UserVo();
		$user->set('email', 'aaron@babblespark.com');
		$user->set('is_email_verified', false);
		$user->set('is_account_disabled', false);
		$user->save();
		$user->registerAuthOption(
			\Orion\StandardUserVo::AUTH_TYPE_KEY,
			array('key' => md5($user->get('email')), 'set_cookie' => true, 'cookie_lifetime' => 100)
		);
	}

	public function testLoginKeyAuthMethod() {
		$this->truncateTables();
		$user = new UserVo();
		$user->set('email', 'aaron@babblespark.com');
		$user->set('is_email_verified', false);
		$user->set('is_account_disabled', false);
		$user->save();
		$user->registerAuthOption(
			\Orion\StandardUserVo::AUTH_TYPE_KEY,
			array('key' => md5($user->get('email')), 'set_cookie' => false)
		);

		$result = $user->logIn(
			\Orion\StandardUserVo::AUTH_TYPE_KEY,
			array('key' => md5($user->get('email')))
		);
		$this->assertEquals(true, $result->valid);
	}

	/**
	 * @expectedException 			Orion\AuthModelException
	 * @expectedExceptionMessage 	Invalid auth params.
	 */
	public function testLoginKeyAuthMethodInvalidParams() {
		$this->truncateTables();
		$user = new UserVo();
		$user->set('email', 'aaron@babblespark.com');
		$user->set('is_email_verified', false);
		$user->set('is_account_disabled', false);
		$user->save();
		$user->registerAuthOption(
			\Orion\StandardUserVo::AUTH_TYPE_KEY,
			array('key' => md5($user->get('email')), 'set_cookie' => false)
		);

		$result = $user->logIn(
			\Orion\StandardUserVo::AUTH_TYPE_KEY,
			array('cookie' => md5($user->get('email')))
		);
	}

	public function testLoginKeyAuthMethodUserNotFound() {
		$this->truncateTables();
		$user = new UserVo();
		$user->set('email', 'aaron@babblespark.com');
		$user->set('is_email_verified', false);
		$user->set('is_account_disabled', false);
		$user->save();
		$user->registerAuthOption(
			\Orion\StandardUserVo::AUTH_TYPE_KEY,
			array('key' => md5($user->get('email')), 'set_cookie' => false)
		);

		$result = $user->logIn(
			\Orion\StandardUserVo::AUTH_TYPE_KEY,
			array('key' => 'test')
		);
		$this->assertEquals(false, $result->valid);
		$this->assertEquals(
			\Orion\AuthLoginResult::AUTH_ERROR_USER_NOT_FOUND,
			$result->errorCode
		);
	}

	public function testLoginKeyAuthMethodUserAccountDisabled() {
		$this->truncateTables();
		$user = new UserVo();
		$user->set('email', 'aaron@babblespark.com');
		$user->set('is_email_verified', false);
		$user->set('is_account_disabled', true);
		$user->save();
		$user->registerAuthOption(
			\Orion\StandardUserVo::AUTH_TYPE_KEY,
			array('key' => md5($user->get('email')), 'set_cookie' => false)
		);

		$result = $user->logIn(
			\Orion\StandardUserVo::AUTH_TYPE_KEY,
			array('key' => md5($user->get('email')))
		);
		$this->assertEquals(false, $result->valid);
		$this->assertEquals(
			\Orion\AuthLoginResult::AUTH_ERROR_ACCOUNT_DISABLED,
			$result->errorCode
		);
	}

	public function testLoginKeyAuthMethodUserEmailNotVerified() {
		\Orion\StandardVoConfig::$loginRequiresEmailVerification = true;
		$this->truncateTables();
		$user = new UserVo();
		$user->set('email', 'aaron@babblespark.com');
		$user->set('is_email_verified', false);
		$user->set('is_account_disabled', false);
		$user->save();
		$user->registerAuthOption(
			\Orion\StandardUserVo::AUTH_TYPE_KEY,
			array('key' => md5($user->get('email')), 'set_cookie' => false)
		);

		$result = $user->logIn(
			\Orion\StandardUserVo::AUTH_TYPE_KEY,
			array('key' => md5($user->get('email')))
		);
		$this->assertEquals(false, $result->valid);
		$this->assertEquals(
			\Orion\AuthLoginResult::AUTH_ERROR_EMAIL_NOT_VERIFIED,
			$result->errorCode
		);
		\Orion\StandardVoConfig::$loginRequiresEmailVerification = false;
	}

	private function truncateTables() {
		$db = \Orion\MySQLPDODatabase::instance();
		$db->query('TRUNCATE `user_auth_attempts`');
		$db->query('TRUNCATE `user_auth_options`');
		$db->query('TRUNCATE `user_keys`');
		$db->query('TRUNCATE `users`');
	}
}
?>