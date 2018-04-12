<?php
/**
 * Created by PhpStorm.
 * User: jfunk
 * Date: 12/15/17
 * Time: 12:06 PM
 */

namespace Piurafunk\PhpSdkFramework;

use PHPUnit\Framework\TestCase;

class BaseTest extends TestCase {

	/**
	 * @param $actual
	 * @param string $message
	 */
	protected function assertIpv4($actual, $message = '') {
		$this->assertRegExp("/(\d{1,3}\.){3}(\d{1,3})/",$actual,$message);
		$ipArray = explode('.', $actual);
		foreach ($ipArray as $octet) {
			$this->assertGreaterThanOrEqual(0, $octet);
			$this->assertLessThanOrEqual(255, $octet);
		}
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