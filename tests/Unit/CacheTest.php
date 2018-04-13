<?php
/**
 * Created by PhpStorm.
 * User: jfunk
 * Date: 4/12/18
 * Time: 8:38 AM
 */

namespace Piurafunk\PhpSdkFramework\Unit;

use Illuminate\Cache\ArrayStore;
use Piurafunk\PhpSdkFramework\ApiClientContract;
use Piurafunk\PhpSdkFramework\ApiClientMock;
use Piurafunk\PhpSdkFramework\BaseTest;
use Piurafunk\PhpSdkFramework\TestModel;

class CacheTest extends BaseTest {

	/**
	 * @var ApiClientContract|ApiClientMock
	 */
	private static $api;

	/**
	 * @inheritdoc
	 */
	public static function setUpBeforeClass() {
		parent::setUpBeforeClass();

		static::$api = new ApiClientMock(new ArrayStore());
	}

	/**
	 * @inheritdoc
	 */
	protected function setUp() {
		parent::setUp();

		static::$api->format('default');
	}

	/**
	 * Test if the faker API will correctly generate a model and store it in the cache, then retrieve it again
	 */
	public function testRetrieveSameModelTwice() {
		$generatedModel = static::$api->get();
		$cachedModel = static::$api->get();

		$this->assertEquals($generatedModel, $cachedModel);
	}

	/**
	 * Test that the path in the cache is used correctly
	 */
	public function testCachePathing() {
		// Add the post route to the mock client
		static::$api->addRoute('test.get', [
			'default' => TestModel::class,
			'chart' => 'string'
		]);

		$modelOne = static::$api->get();
		$modelTwo = static::$api->test()->get();

		$this->assertNotEquals($modelOne, $modelTwo);
	}
}