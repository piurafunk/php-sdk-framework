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

	/**
	 * @inheritdoc
	 */
	public static function setUpBeforeClass() {
		parent::setUpBeforeClass();

		static::$api = new ApiClientMock();
	}

	/**
	 * @inheritdoc
	 */
	protected function setUp() {
		parent::setUp();

		static::$api->format('default');
	}

	/**
	 * Test the integer tweaks
	 */
	public function testIntegerTweaks() {
		TestModel::$attributeKeys['age'] = [
			'type' => 'integer',
			'tweaks' => [
				'min' => 18,
				'max' => 64
			]
		];

		/** @var TestModel $testModel */
		$testModel = static::$api->get();

		$this->assertTestModel($testModel);
		$this->assertGreaterThanOrEqual(18, $testModel->age);
		$this->assertLessThanOrEqual(64, $testModel->age);
	}

	/**
	 * Test a custom defined data type
	 */
	public function testCustomDefinedFakerValue() {
		TestModel::$attributeKeys['height'] = [
			'type' => 'height',
			'returnType' => 'string'
		];

		/** @var ApiClientMock $api */
		$api = static::$api;
		$api::addGenerator('height', function (\Faker\Generator $faker) {
			$feet = $faker->numberBetween(5, 6);
			$inches = $faker->numberBetween(0, 11);

			return "$feet' $inches\"";
		});

		$testModel = $api->get();

		$this->assertTestModel($testModel);
		$this->assertInternalType('string', $testModel->height);
		$this->assertRegExp('/\d\' \d{1,2}"/', $testModel->height);
	}

	/**
	 * Test 'chart' as return type
	 */
	public function testFormatType() {
		$response = static::$api->format('chart')->get();

		$this->assertInternalType('string', $response);
	}

	/**
	 * Test a basic model
	 */
	public function testGenerateTestModel() {
		/** @var TestModel $testModel */
		$testModel = static::$api->get();

		$this->assertTestModel($testModel);
	}

	/**
	 * Assert the attributes of a TestModel
	 *
	 * @param $testModel
	 */
	public function assertTestModel($testModel) {
		$this->assertInstanceOf(TestModel::class, $testModel);
		$this->assertInternalType('string', $testModel->firstName);
		$this->assertInternalType('string', $testModel->lastName);
		$this->assertInternalType('string', $testModel->address);
		$this->assertInternalType('string', $testModel->homeIpAddress);
		$this->assertIpv4($testModel->homeIpAddress);
		$this->assertInternalType('integer', $testModel->createdAt);
		$this->assertThat($testModel->phoneNumber, $this->logicalOr(
			$this->isType('string'),
			$this->isNull()
		));
	}
}