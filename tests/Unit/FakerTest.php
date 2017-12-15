<?php

namespace Piurafunk\PhpSdkFramework\Unit;

use Piurafunk\PhpSdkFramework\ApiClientContract;
use Piurafunk\PhpSdkFramework\ApiClientMock;
use Piurafunk\PhpSdkFramework\BaseTest;
use Piurafunk\PhpSdkFramework\TestModel;

class FakerTest extends BaseTest {

	/**
	 * @var ApiClientContract
	 */
	private static $api;

	public static function setUpBeforeClass() {
		parent::setUpBeforeClass();
		
		static::$api = new ApiClientMock();
	}

	public function testGenerateTestModel() {
		/** @var TestModel $testModel */
		$testModel = static::$api->get();

		$this->assertInternalType('string', $testModel->firstName);
		$this->assertInternalType('string', $testModel->lastName);
		$this->assertInternalType('string', $testModel->address);
		$this->assertInternalType('string', $testModel->homeIpAddress);
		$this->assertIpv4($testModel->homeIpAddress);
		$this->assertInternalType('integer', $testModel->createdAt);
	}
}