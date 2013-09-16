<?php
require_once(dirname(__FILE__).'/../../../config.php');
set_include_path(get_include_path() . PATH_SEPARATOR . OrionConfig::$basePath);

require_once('Validator/ValidatorOptions.php');
require_once('Validator/SimpleValidator/SimpleValidator.php');
class ValidatorTest extends PHPUnit_Framework_TestCase {
	public function testOptionsInvalidParameters() {
		$validator = new \Orion\SimpleValidator();

		$options = new \Orion\ValidatorOptions('options', array());
		$result = $validator->validate('test', $options);
		$this->assertEquals(false, $result->valid);
		$this->assertEquals(0, $result->errorCode);
		$this->assertEquals('Invalid parameters.', $result->errorMessage);
	}

	public function testOptionsInvalidOptions() {
		$validator = new \Orion\SimpleValidator();

		$options = new \Orion\ValidatorOptions('options', array('options'=>'not array'));
		$result = $validator->validate('test', $options);
		$this->assertEquals(false, $result->valid);
		$this->assertEquals(1, $result->errorCode);
		$this->assertEquals('No list of valid options specified in the parameters.', $result->errorMessage);

	}
	
	public function testOptionsInvalidDataNoOptions() {
		$validator = new \Orion\SimpleValidator();

		$options = new \Orion\ValidatorOptions('options', array('options'=>array()));
		$result = $validator->validate('test', $options);
		$this->assertEquals(false, $result->valid);
		$this->assertEquals(2, $result->errorCode);
		$this->assertEquals('Invalid data.', $result->errorMessage);
	}

	public function testOptionsInvalidDataNotInList() {
		$validator = new \Orion\SimpleValidator();

		$options = new \Orion\ValidatorOptions('options', array('options'=>array('aaa','bbb','ccc')));
		$result = $validator->validate('test', $options);
		$this->assertEquals(false, $result->valid);
		$this->assertEquals(2, $result->errorCode);
		$this->assertEquals('Invalid data.', $result->errorMessage);
	}

	public function testOptionsInvalidDataNotInListWithCustomMessage() {
		$validator = new \Orion\SimpleValidator();

		$options = new \Orion\ValidatorOptions(
			'options',
			array('options'=>array('aaa','bbb','ccc')),
			true,
			array(2 => 'This is a test message.')
		);
		$result = $validator->validate('test', $options);
		$this->assertEquals(false, $result->valid);
		$this->assertEquals(2, $result->errorCode);
		$this->assertEquals('This is a test message.', $result->errorMessage);
	}

	public function testOptionsValidDataOneOption() {
		$validator = new \Orion\SimpleValidator();

		$options = new \Orion\ValidatorOptions('options', array('options'=>array('test')));
		$result = $validator->validate('test', $options);
		$this->assertEquals(true, $result->valid);
	}

	public function testOptionsValidDataFourOptions() {
		$validator = new \Orion\SimpleValidator();

		$options = new \Orion\ValidatorOptions('options', array('options'=>array('aaa','bbb','ccc','test')));
		$result = $validator->validate('test', $options);
		$this->assertEquals(true, $result->valid);
	}

	public function testLengthValidDataNoRequirements() {
		$validator = new \Orion\SimpleValidator();

		$options = new \Orion\ValidatorOptions('length', array());
		$result = $validator->validate('this is a test', $options);
		$this->assertEquals(true, $result->valid);
	}

	public function testLengthValidDataMinOnly1() {
		$validator = new \Orion\SimpleValidator();

		$options = new \Orion\ValidatorOptions('length', array('min'=>1));
		$result = $validator->validate('this is a test', $options);
		$this->assertEquals(true, $result->valid);
	}

	public function testLengthValidDataMinAndMax1() {
		$validator = new \Orion\SimpleValidator();

		$options = new \Orion\ValidatorOptions('length', array('min'=>1, 'max'=>20));
		$result = $validator->validate('this is a test', $options);
		$this->assertEquals(true, $result->valid);
	}

	public function testLengthValidDataMinAndMax2() {
		$validator = new \Orion\SimpleValidator();

		$options = new \Orion\ValidatorOptions('length', array('min'=>1, 'max'=>14));
		$result = $validator->validate('this is a test', $options);
		$this->assertEquals(true, $result->valid);
	}

	public function testLengthInvalidDataTooLong1() {
		$validator = new \Orion\SimpleValidator();

		$options = new \Orion\ValidatorOptions('length', array('min'=>1, 'max'=>13));
		$result = $validator->validate('this is a test', $options);
		$this->assertEquals(false, $result->valid);
		$this->assertEquals(0, $result->errorCode);
		$this->assertEquals('The string is too long.', $result->errorMessage);
	}

	public function testLengthInvalidDataTooLong2() {
		$validator = new \Orion\SimpleValidator();

		$options = new \Orion\ValidatorOptions('length', array('min'=>1, 'max'=>10));
		$result = $validator->validate('this is a test', $options);
		$this->assertEquals(false, $result->valid);
		$this->assertEquals(0, $result->errorCode);
		$this->assertEquals('The string is too long.', $result->errorMessage);
	}

	public function testLengthValidDataMinOnly2() {
		$validator = new \Orion\SimpleValidator();

		$options = new \Orion\ValidatorOptions('length', array('min'=>15));
		$result = $validator->validate('this is a test', $options);
		$this->assertEquals(false, $result->valid);
		$this->assertEquals(1, $result->errorCode);
		$this->assertEquals('The string is too short.', $result->errorMessage);
	}

	public function testLengthValidDataMaxOnly1() {
		$validator = new \Orion\SimpleValidator();

		$options = new \Orion\ValidatorOptions('length', array('max'=>10));
		$result = $validator->validate('this is a test', $options);
		$this->assertEquals(false, $result->valid);
		$this->assertEquals(0, $result->errorCode);
		$this->assertEquals('The string is too long.', $result->errorMessage);
	}

	public function testLengthValidDataMaxOnly2() {
		$validator = new \Orion\SimpleValidator();

		$options = new \Orion\ValidatorOptions('length', array('max'=>100));
		$result = $validator->validate('this is a test', $options);
		$this->assertEquals(true, $result->valid);
	}

	public function testEmailNoAtSymbol() {
		$validator = new \Orion\SimpleValidator();

		$options = new \Orion\ValidatorOptions('email');
		$result = $validator->validate('test', $options);
		$this->assertEquals(false, $result->valid);
		$this->assertEquals(0, $result->errorCode);
		$this->assertEquals('The email address must contain an @ symbol.', $result->errorMessage);
	}

	public function testEmailLocalTooShort() {
		$validator = new \Orion\SimpleValidator();

		$options = new \Orion\ValidatorOptions('email');
		$result = $validator->validate('@', $options);
		$this->assertEquals(false, $result->valid);
		$this->assertEquals(2, $result->errorCode);
		$this->assertEquals('The local piece is too short.', $result->errorMessage);
	}

	public function testEmailLocalTooLong() {
		$validator = new \Orion\SimpleValidator();

		$options = new \Orion\ValidatorOptions('email');
		$result = $validator->validate('1234567890123456789012345678901234567890123456789012345678901234567890@', $options);
		$this->assertEquals(false, $result->valid);
		$this->assertEquals(1, $result->errorCode);
		$this->assertEquals('The local piece is too long.', $result->errorMessage);
	}

	public function testEmailDomainTooShort() {
		$validator = new \Orion\SimpleValidator();

		$options = new \Orion\ValidatorOptions('email');
		$result = $validator->validate('a@', $options);
		$this->assertEquals(false, $result->valid);
		$this->assertEquals(4, $result->errorCode);
		$this->assertEquals('The domain is too short.', $result->errorMessage);
	}

	public function testEmailDomainTooLong() {
		$validator = new \Orion\SimpleValidator();

		$options = new \Orion\ValidatorOptions('email');
		$result = $validator->validate('a@1234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890', $options);
		$this->assertEquals(false, $result->valid);
		$this->assertEquals(3, $result->errorCode);
		$this->assertEquals('The domain is too long.', $result->errorMessage);
	}

	public function testEmailLocalStartInvalid() {
		$validator = new \Orion\SimpleValidator();

		$options = new \Orion\ValidatorOptions('email');
		$result = $validator->validate('.a@test.com', $options);
		$this->assertEquals(false, $result->valid);
		$this->assertEquals(5, $result->errorCode);
		$this->assertEquals('The local piece cannot start with a dot.', $result->errorMessage);
	}

	public function testEmailLocalEndInvalid() {
		$validator = new \Orion\SimpleValidator();

		$options = new \Orion\ValidatorOptions('email');
		$result = $validator->validate('a.@test.com', $options);
		$this->assertEquals(false, $result->valid);
		$this->assertEquals(6, $result->errorCode);
		$this->assertEquals('The local piece cannot end with a dot.', $result->errorMessage);
	}

	public function testEmailConsecutiveDots() {
		$validator = new \Orion\SimpleValidator();

		$options = new \Orion\ValidatorOptions('email');
		$result = $validator->validate('a..b@test.com', $options);
		$this->assertEquals(false, $result->valid);
		$this->assertEquals(7, $result->errorCode);
		$this->assertEquals('The local piece cannot have two or more consecutive dots.', $result->errorMessage);
	}

	public function testEmailDomainInvalidChars() {
		$validator = new \Orion\SimpleValidator();

		$options = new \Orion\ValidatorOptions('email');
		$result = $validator->validate('a@test\b.com', $options);
		$this->assertEquals(false, $result->valid);
		$this->assertEquals(8, $result->errorCode);
		$this->assertEquals('The domain has invalid characters.', $result->errorMessage);
	}

	public function testEmailDomainConsecutiveDots() {
		$validator = new \Orion\SimpleValidator();

		$options = new \Orion\ValidatorOptions('email');
		$result = $validator->validate('a@test..b.com', $options);
		$this->assertEquals(false, $result->valid);
		$this->assertEquals(9, $result->errorCode);
		$this->assertEquals('The domain cannot have two or more consecutive dots.', $result->errorMessage);
	}

	public function testEmailLocalInvalidChars() {
		$validator = new \Orion\SimpleValidator();

		$options = new \Orion\ValidatorOptions('email');
		$result = $validator->validate('あい@tesb.com', $options);
		$this->assertEquals(false, $result->valid);
		$this->assertEquals(10, $result->errorCode);
		$this->assertEquals('The local piece has invalid characters.', $result->errorMessage);
	}

	public function testEmailDomainInvalid() {
		$validator = new \Orion\SimpleValidator();

		$options = new \Orion\ValidatorOptions('email');
		$result = $validator->validate('email@123.123.123.123', $options);
		$this->assertEquals(false, $result->valid);
		$this->assertEquals(11, $result->errorCode);
		$this->assertEquals('The email domain is invalid.', $result->errorMessage);
	}

	public function testEmailValid() {
		$validator = new \Orion\SimpleValidator();

		$options = new \Orion\ValidatorOptions('email');
		$result = $validator->validate('"あい"@tesb.com', $options);
		$this->assertEquals(true, $result->valid);

		$options = new \Orion\ValidatorOptions('email');
		$result = $validator->validate('email@domain.com', $options);
		$this->assertEquals(true, $result->valid);

		$options = new \Orion\ValidatorOptions('email');
		$result = $validator->validate('firstname.lastname@domain.com', $options);
		$this->assertEquals(true, $result->valid);

		$options = new \Orion\ValidatorOptions('email');
		$result = $validator->validate('email@subdomain.domain.com', $options);
		$this->assertEquals(true, $result->valid);

		$options = new \Orion\ValidatorOptions('email');
		$result = $validator->validate('firstname+lastname@domain.com', $options);
		$this->assertEquals(true, $result->valid);

		$options = new \Orion\ValidatorOptions('email');
		$result = $validator->validate('email@[123.123.123.123]', $options);
		$this->assertEquals(true, $result->valid);

		$options = new \Orion\ValidatorOptions('email');
		$result = $validator->validate('"email"@domain.com', $options);
		$this->assertEquals(true, $result->valid);

		$options = new \Orion\ValidatorOptions('email');
		$result = $validator->validate('1234567890@domain.com', $options);
		$this->assertEquals(true, $result->valid);

		$options = new \Orion\ValidatorOptions('email');
		$result = $validator->validate('email@domain-one.com', $options);
		$this->assertEquals(true, $result->valid);

		$options = new \Orion\ValidatorOptions('email');
		$result = $validator->validate('_______@domain.com', $options);
		$this->assertEquals(true, $result->valid);

		$options = new \Orion\ValidatorOptions('email');
		$result = $validator->validate('email@domain.name', $options);
		$this->assertEquals(true, $result->valid);

		$options = new \Orion\ValidatorOptions('email');
		$result = $validator->validate('email@domain.co.jp', $options);
		$this->assertEquals(true, $result->valid);

		$options = new \Orion\ValidatorOptions('email');
		$result = $validator->validate('firstname-lastname@domain.com', $options);
		$this->assertEquals(true, $result->valid);

		$options = new \Orion\ValidatorOptions('email');
		$result = $validator->validate('email@domain.web', $options);
		$this->assertEquals(true, $result->valid);
	}

	public function testEmailInvalid1() {
		$validator = new \Orion\SimpleValidator();

		$options = new \Orion\ValidatorOptions('email');
		$result = $validator->validate('plainaddress', $options);
		$this->assertEquals(false, $result->valid);
		$this->assertEquals(0, $result->errorCode);
		$this->assertEquals('The email address must contain an @ symbol.', $result->errorMessage);
	}

	public function testEmailInvalid2() {
		$validator = new \Orion\SimpleValidator();

		$options = new \Orion\ValidatorOptions('email');
		$result = $validator->validate('#@%^%#$@#$@#.com', $options);
		$this->assertEquals(false, $result->valid);
		$this->assertEquals(8, $result->errorCode);
		$this->assertEquals('The domain has invalid characters.', $result->errorMessage);
	}

	public function testEmailInvalid3() {
		$validator = new \Orion\SimpleValidator();

		$options = new \Orion\ValidatorOptions('email');
		$result = $validator->validate('@domain.com', $options);
		$this->assertEquals(false, $result->valid);
		$this->assertEquals(2, $result->errorCode);
		$this->assertEquals('The local piece is too short.', $result->errorMessage);
	}

	public function testEmailInvalid4() {
		$validator = new \Orion\SimpleValidator();

		$options = new \Orion\ValidatorOptions('email');
		$result = $validator->validate('Joe Smith <email@domain.com>', $options);
		$this->assertEquals(false, $result->valid);
		$this->assertEquals(8, $result->errorCode);
		$this->assertEquals('The domain has invalid characters.', $result->errorMessage);
	}

	public function testEmailInvalid5() {
		$validator = new \Orion\SimpleValidator();

		$options = new \Orion\ValidatorOptions('email');
		$result = $validator->validate('email.domain.com', $options);
		$this->assertEquals(false, $result->valid);
		$this->assertEquals(0, $result->errorCode);
		$this->assertEquals('The email address must contain an @ symbol.', $result->errorMessage);
	}

	public function testEmailInvalid6() {
		$validator = new \Orion\SimpleValidator();

		$options = new \Orion\ValidatorOptions('email');
		$result = $validator->validate('email@domain@domain.com', $options);
		$this->assertEquals(false, $result->valid);
		$this->assertEquals(10, $result->errorCode);
		$this->assertEquals('The local piece has invalid characters.', $result->errorMessage);
	}

	public function testEmailInvalid7() {
		$validator = new \Orion\SimpleValidator();

		$options = new \Orion\ValidatorOptions('email');
		$result = $validator->validate('.email@domain.com', $options);
		$this->assertEquals(false, $result->valid);
		$this->assertEquals(5, $result->errorCode);
		$this->assertEquals('The local piece cannot start with a dot.', $result->errorMessage);
	}

	public function testEmailInvalid8() {
		$validator = new \Orion\SimpleValidator();

		$options = new \Orion\ValidatorOptions('email');
		$result = $validator->validate('email.@domain.com', $options);
		$this->assertEquals(false, $result->valid);
		$this->assertEquals(6, $result->errorCode);
		$this->assertEquals('The local piece cannot end with a dot.', $result->errorMessage);
	}

	public function testEmailInvalid9() {
		$validator = new \Orion\SimpleValidator();

		$options = new \Orion\ValidatorOptions('email');
		$result = $validator->validate('email..email@domain.com', $options);
		$this->assertEquals(false, $result->valid);
		$this->assertEquals(7, $result->errorCode);
		$this->assertEquals('The local piece cannot have two or more consecutive dots.', $result->errorMessage);
	}

	public function testEmailInvalid10() {
		$validator = new \Orion\SimpleValidator();

		$options = new \Orion\ValidatorOptions('email');
		$result = $validator->validate('あいうえお@domain.com', $options);
		$this->assertEquals(false, $result->valid);
		$this->assertEquals(10, $result->errorCode);
		$this->assertEquals('The local piece has invalid characters.', $result->errorMessage);
	}

	public function testEmailInvalid11() {
		$validator = new \Orion\SimpleValidator();

		$options = new \Orion\ValidatorOptions('email');
		$result = $validator->validate('email@domain.com (Joe Smith)', $options);
		$this->assertEquals(false, $result->valid);
		$this->assertEquals(8, $result->errorCode);
		$this->assertEquals('The domain has invalid characters.', $result->errorMessage);
	}

	public function testEmailInvalid12() {
		$validator = new \Orion\SimpleValidator();

		$options = new \Orion\ValidatorOptions('email');
		$result = $validator->validate('email@domain', $options);
		$this->assertEquals(false, $result->valid);
		$this->assertEquals(11, $result->errorCode);
		$this->assertEquals('The email domain is invalid.', $result->errorMessage);
	}

	public function testEmailInvalid13() {
		$validator = new \Orion\SimpleValidator();

		$options = new \Orion\ValidatorOptions('email');
		$result = $validator->validate('email@-domain.com', $options);
		$this->assertEquals(false, $result->valid);
		$this->assertEquals(11, $result->errorCode);
		$this->assertEquals('The email domain is invalid.', $result->errorMessage);
	}

	public function testEmailInvalid14() {
		$validator = new \Orion\SimpleValidator();

		$options = new \Orion\ValidatorOptions('email');
		$result = $validator->validate('email@111.222.333.44444', $options);
		$this->assertEquals(false, $result->valid);
		$this->assertEquals(11, $result->errorCode);
		$this->assertEquals('The email domain is invalid.', $result->errorMessage);
	}

	public function testEmailInvalid15() {
		$validator = new \Orion\SimpleValidator();

		$options = new \Orion\ValidatorOptions('email');
		$result = $validator->validate('email@domain..com', $options);
		$this->assertEquals(false, $result->valid);
		$this->assertEquals(9, $result->errorCode);
		$this->assertEquals('The domain cannot have two or more consecutive dots.', $result->errorMessage);
	}

	public function testArrayNotArray1() {
		$validator = new \Orion\SimpleValidator();

		$options = new \Orion\ValidatorOptions('array');
		$result = $validator->validate(null, $options);
		$this->assertEquals(false, $result->valid);
		$this->assertEquals(0, $result->errorCode);
		$this->assertEquals('The data is not an array.', $result->errorMessage);
	}

	public function testArrayNotArray2() {
		$validator = new \Orion\SimpleValidator();

		$options = new \Orion\ValidatorOptions('array');
		$result = $validator->validate(0, $options);
		$this->assertEquals(false, $result->valid);
		$this->assertEquals(0, $result->errorCode);
		$this->assertEquals('The data is not an array.', $result->errorMessage);
	}

	public function testArrayNotArray3() {
		$validator = new \Orion\SimpleValidator();

		$options = new \Orion\ValidatorOptions('array');
		$result = $validator->validate('a', $options);
		$this->assertEquals(false, $result->valid);
		$this->assertEquals(0, $result->errorCode);
		$this->assertEquals('The data is not an array.', $result->errorMessage);
	}

	public function testArrayValid1() {
		$validator = new \Orion\SimpleValidator();

		$options = new \Orion\ValidatorOptions('array');
		$result = $validator->validate(array(), $options);
		$this->assertEquals(true, $result->valid);

		$options = new \Orion\ValidatorOptions('array');
		$result = $validator->validate(array('test'), $options);
		$this->assertEquals(true, $result->valid);
	}

	public function testArrayMissingKeys() {
		$validator = new \Orion\SimpleValidator();
		$data = array(
			'password' => 'adshnl8432rhlksd',
			'sex' => 'female'
		);
		$options = $this->getArrayValidationOptions();
		$result = $validator->validate($data, $options);
		$this->assertEquals(false, $result->valid);
		$this->assertEquals(3, $result->errorCode);
		$this->assertEquals('The key is required but missing [email].', $result->errorMessage);
	}

	public function testArrayExtraKeys() {
		$validator = new \Orion\SimpleValidator();
		$data = array(
			'email' => 'aero4x@gmail.com',
			'password' => 'adshnl8432rhlksd',
			'sex' => 'female',
			'extra' => 0
		);
		$options = $this->getArrayValidationOptions();
		$result = $validator->validate($data, $options);
		$this->assertEquals(false, $result->valid);
		$this->assertEquals(1, $result->errorCode);
		$this->assertEquals('The key was not expected [extra].', $result->errorMessage);
	}

	public function testArrayValid2() {
		$validator = new \Orion\SimpleValidator();

		$data = array(
			'email' => 'aero4x@gmail.com',
			'password' => 'adshnl8432rhlksd',
			'sex' => 'female'
		);

		$options = $this->getArrayValidationOptions();
		$result = $validator->validate($data, $options);

		$this->assertEquals(true, $result->valid);
	}

	public function testArrayValidKeyedNestedArray() {
		$validator = new \Orion\SimpleValidator();
		$data = array(
			'email' => 'aero4x@gmail.com',
			'password' => 'adshnl8432rhlksd',
			'sex' => 'female',
			'pet' => array(
				'name' => 'Sammy',
				'type' => 'cat'
			)
		);
		$options = $this->getArrayValidationOptions();
		$options->params['keyedValidationOptions']['pet'] = new \Orion\ValidatorOptions(
			'array',
			array(
				'keyedValidationOptions' => array(
					'name' => new \Orion\ValidatorOptions(),
					'type' => new \Orion\ValidatorOptions('options', array('options' => array('cat', 'dog')))
				)
			)
		);

		$result = $validator->validate($data, $options);
		$this->assertEquals(true, $result->valid);
	}

	public function testArrayInvalidKeyedNestedArray() {
		$validator = new \Orion\SimpleValidator();
		$data = array(
			'email' => 'aero4x@gmail.com',
			'password' => 'adshnl8432rhlksd',
			'sex' => 'female',
			'pet' => array(
				'name' => 'Gerald',
				'type' => 'fish'
			)
		);
		$options = $this->getArrayValidationOptions();
		$options->params['keyedValidationOptions']['pet'] = new \Orion\ValidatorOptions(
			'array',
			array(
				'keyedValidationOptions' => array(
					'name' => new \Orion\ValidatorOptions(null, array(), false),
					'type' => new \Orion\ValidatorOptions('options', array('options' => array('cat', 'dog')))
				)
			)
		);

		$result = $validator->validate($data, $options);
		$this->assertEquals(false, $result->valid);
		$this->assertEquals(2, $result->errorCode);
		$this->assertEquals('The key is invalid [pet].', $result->errorMessage);

	}

	public function testNonRequiredKey() {
		$validator = new \Orion\SimpleValidator();
		$data = array(
			'email' => 'aero4x@gmail.com',
			'password' => 'adshnl8432rhlksd',
			'sex' => 'female',
			'pet' => array(
				'type' => 'cat'
			)
		);
		$options = $this->getArrayValidationOptions();
		$options->params['keyedValidationOptions']['pet'] = new \Orion\ValidatorOptions(
			'array',
			array(
				'keyedValidationOptions' => array(
					'name' => new \Orion\ValidatorOptions(null, array(), false),
					'type' => new \Orion\ValidatorOptions('options', array('options' => array('cat', 'dog')))
				)
			)
		);

		$result = $validator->validate($data, $options);
		$this->assertEquals(true, $result->valid);
	}

	public function testIndexedValidation() {
		$validator = new \Orion\SimpleValidator();
		$data = array(
			'email' => 'aero4x@gmail.com',
			'password' => 'adshnl8432rhlksd',
			'sex' => 'female',
			'pet' => array(
				array('name' => 'Spanky', 'type' => 'cat'),
				array('name' => 'Domino', 'type' => 'dog'),
				array('name' => 'Monty', 'type' => 'dog')
			)
		);
		$options = $this->getArrayValidationOptions();
		$options->params['keyedValidationOptions']['pet'] = new \Orion\ValidatorOptions(
			'array',
			array(
				'indexedValidationOptions' => new \Orion\ValidatorOptions(
					'array',
					array(
						'keyedValidationOptions' => array(
							'name' => new \Orion\ValidatorOptions(null, array(), true),
							'type' => new \Orion\ValidatorOptions('options', array('options' => array('cat', 'dog')))
						)
					)
				)
			)
		);
		$result = $validator->validate($data, $options);
		$this->assertEquals(true, $result->valid);
	}

	public function testIntValid() {
		$validator = new \Orion\SimpleValidator();
		
		$options = new \Orion\ValidatorOptions('int');
		$result = $validator->validate(1, $options);
		$this->assertEquals(true, $result->valid);

		$options = new \Orion\ValidatorOptions('int');
		$result = $validator->validate(3287, $options);
		$this->assertEquals(true, $result->valid);

		$options = new \Orion\ValidatorOptions('int');
		$result = $validator->validate('12', $options);
		$this->assertEquals(true, $result->valid);

		$options = new \Orion\ValidatorOptions('int');
		$result = $validator->validate('12238', $options);
		$this->assertEquals(true, $result->valid);

		$options = new \Orion\ValidatorOptions('int');
		$result = $validator->validate('-12', $options);
		$this->assertEquals(true, $result->valid);

		$options = new \Orion\ValidatorOptions('int');
		$result = $validator->validate(-12, $options);
		$this->assertEquals(true, $result->valid);

		$options = new \Orion\ValidatorOptions('int', array('min' => 3));
		$result = $validator->validate(3, $options);
		$this->assertEquals(true, $result->valid);

		$options = new \Orion\ValidatorOptions('int', array('min' => 3));
		$result = $validator->validate(4, $options);
		$this->assertEquals(true, $result->valid);

		$options = new \Orion\ValidatorOptions('int', array('min' => '3'));
		$result = $validator->validate(3, $options);
		$this->assertEquals(true, $result->valid);

		$options = new \Orion\ValidatorOptions('int', array('min' => '3'));
		$result = $validator->validate(4, $options);
		$this->assertEquals(true, $result->valid);

		$options = new \Orion\ValidatorOptions('int', array('min' => '03'));
		$result = $validator->validate(3, $options);
		$this->assertEquals(true, $result->valid);

		$options = new \Orion\ValidatorOptions('int', array('min' => '03'));
		$result = $validator->validate(4, $options);
		$this->assertEquals(true, $result->valid);

		$options = new \Orion\ValidatorOptions('int', array('max' => 3));
		$result = $validator->validate(3, $options);
		$this->assertEquals(true, $result->valid);

		$options = new \Orion\ValidatorOptions('int', array('max' => 3));
		$result = $validator->validate(2, $options);
		$this->assertEquals(true, $result->valid);

		$options = new \Orion\ValidatorOptions('int', array('max' => '3'));
		$result = $validator->validate(3, $options);
		$this->assertEquals(true, $result->valid);

		$options = new \Orion\ValidatorOptions('int', array('max' => '3'));
		$result = $validator->validate(2, $options);
		$this->assertEquals(true, $result->valid);

		$options = new \Orion\ValidatorOptions('int', array('max' => '03'));
		$result = $validator->validate(3, $options);
		$this->assertEquals(true, $result->valid);

		$options = new \Orion\ValidatorOptions('int', array('max' => '03'));
		$result = $validator->validate(2, $options);
		$this->assertEquals(true, $result->valid);

		$options = new \Orion\ValidatorOptions('int', array('min' => -1, 'max' => 100));
		$result = $validator->validate(-1, $options);
		$this->assertEquals(true, $result->valid);

		$options = new \Orion\ValidatorOptions('int', array('min' => -1, 'max' => 100));
		$result = $validator->validate(100, $options);
		$this->assertEquals(true, $result->valid);

		$options = new \Orion\ValidatorOptions('int', array('min' => -1, 'max' => 100));
		$result = $validator->validate(22, $options);
		$this->assertEquals(true, $result->valid);
	}

	public function testIntTooSmall1() {
		$validator = new \Orion\SimpleValidator();
		$options = new \Orion\ValidatorOptions('int', array('min' => 3));
		$result = $validator->validate(2, $options);
		$this->assertEquals(false, $result->valid);
		$this->assertEquals(1, $result->errorCode);
		$this->assertEquals('The integer is too small.', $result->errorMessage);
	}

	public function testIntTooSmall2() {
		$validator = new \Orion\SimpleValidator();
		$options = new \Orion\ValidatorOptions('int', array('min' => '3'));
		$result = $validator->validate(1, $options);
		$this->assertEquals(false, $result->valid);
		$this->assertEquals(1, $result->errorCode);
		$this->assertEquals('The integer is too small.', $result->errorMessage);
	}

	public function testIntTooSmall3() {
		$validator = new \Orion\SimpleValidator();
		$options = new \Orion\ValidatorOptions('int', array('min' => '03'));
		$result = $validator->validate(-4, $options);
		$this->assertEquals(false, $result->valid);
		$this->assertEquals(1, $result->errorCode);
		$this->assertEquals('The integer is too small.', $result->errorMessage);
	}

	public function testIntTooSmall4() {
		$validator = new \Orion\SimpleValidator();
		$options = new \Orion\ValidatorOptions('int', array('min' => '03'));
		$result = $validator->validate('-4', $options);
		$this->assertEquals(false, $result->valid);
		$this->assertEquals(1, $result->errorCode);
		$this->assertEquals('The integer is too small.', $result->errorMessage);
	}

	public function testIntTooLarge1() {
		$validator = new \Orion\SimpleValidator();
		$options = new \Orion\ValidatorOptions('int', array('max' => 3));
		$result = $validator->validate(4, $options);
		$this->assertEquals(false, $result->valid);
		$this->assertEquals(2, $result->errorCode);
		$this->assertEquals('The integer is too large.', $result->errorMessage);
	}

	public function testIntTooLarge2() {
		$validator = new \Orion\SimpleValidator();
		$options = new \Orion\ValidatorOptions('int', array('max' => '3'));
		$result = $validator->validate(5, $options);
		$this->assertEquals(false, $result->valid);
		$this->assertEquals(2, $result->errorCode);
		$this->assertEquals('The integer is too large.', $result->errorMessage);
	}

	public function testIntTooLarge3() {
		$validator = new \Orion\SimpleValidator();
		$options = new \Orion\ValidatorOptions('int', array('max' => '-3'));
		$result = $validator->validate(2, $options);
		$this->assertEquals(false, $result->valid);
		$this->assertEquals(2, $result->errorCode);
		$this->assertEquals('The integer is too large.', $result->errorMessage);
	}

	public function testIntTooLarge4() {
		$validator = new \Orion\SimpleValidator();
		$options = new \Orion\ValidatorOptions('int', array('max' => '-3'));
		$result = $validator->validate('-1', $options);
		$this->assertEquals(false, $result->valid);
		$this->assertEquals(2, $result->errorCode);
		$this->assertEquals('The integer is too large.', $result->errorMessage);
	}


	public function testIntRangeInvalid1() {
		$validator = new \Orion\SimpleValidator();
		$options = new \Orion\ValidatorOptions('int', array('min' => -1, 'max' => 100));
		$result = $validator->validate(-2, $options);
		$this->assertEquals(false, $result->valid);
		$this->assertEquals(1, $result->errorCode);
		$this->assertEquals('The integer is too small.', $result->errorMessage);
	}

	public function testIntRangeInvalid2() {
		$validator = new \Orion\SimpleValidator();
		$options = new \Orion\ValidatorOptions('int', array('min' => -1, 'max' => 100));
		$result = $validator->validate(-99, $options);
		$this->assertEquals(false, $result->valid);
		$this->assertEquals(1, $result->errorCode);
		$this->assertEquals('The integer is too small.', $result->errorMessage);
	}

	public function testIntRangeInvalid3() {
		$validator = new \Orion\SimpleValidator();
		$options = new \Orion\ValidatorOptions('int', array('min' => -1, 'max' => 100));
		$result = $validator->validate(101, $options);
		$this->assertEquals(false, $result->valid);
		$this->assertEquals(2, $result->errorCode);
		$this->assertEquals('The integer is too large.', $result->errorMessage);
	}

	public function testIntRangeInvalid4() {
		$validator = new \Orion\SimpleValidator();
		$options = new \Orion\ValidatorOptions('int', array('min' => -1, 'max' => 100));
		$result = $validator->validate(5000, $options);
		$this->assertEquals(false, $result->valid);
		$this->assertEquals(2, $result->errorCode);
		$this->assertEquals('The integer is too large.', $result->errorMessage);
	}

	public function testIntInvalid1() {
		$validator = new \Orion\SimpleValidator();
		$options = new \Orion\ValidatorOptions('int');
		$result = $validator->validate(12.75, $options);
		$this->assertEquals(false, $result->valid);
		$this->assertEquals(0, $result->errorCode);
		$this->assertEquals('The value is not an integer.', $result->errorMessage);
	}

	public function testIntInvalid2() {
		$validator = new \Orion\SimpleValidator();
		$options = new \Orion\ValidatorOptions('int');
		$result = $validator->validate('843.37', $options);
		$this->assertEquals(false, $result->valid);
		$this->assertEquals(0, $result->errorCode);
		$this->assertEquals('The value is not an integer.', $result->errorMessage);
	}

	public function testIntInvalid3() {
		$validator = new \Orion\SimpleValidator();
		$options = new \Orion\ValidatorOptions('int');
		$result = $validator->validate('438.', $options);
		$this->assertEquals(false, $result->valid);
		$this->assertEquals(0, $result->errorCode);
		$this->assertEquals('The value is not an integer.', $result->errorMessage);
	}

	public function testIntInvalid4() {
		$validator = new \Orion\SimpleValidator();
		$options = new \Orion\ValidatorOptions('int');
		$result = $validator->validate('asd', $options);
		$this->assertEquals(false, $result->valid);
		$this->assertEquals(0, $result->errorCode);
		$this->assertEquals('The value is not an integer.', $result->errorMessage);
	}

	private function getArrayValidationOptions() {
		return new \Orion\ValidatorOptions(
			'array',
			array(
				'keyedValidationOptions' => array(
					'email' => new \Orion\ValidatorOptions('email'),
					'password' => new \Orion\ValidatorOptions('length', array('min' => 7)),
					'sex' => new \Orion\ValidatorOptions('options', array('options' => array('male', 'female')))
				)
			)
		);
	}
}
?>