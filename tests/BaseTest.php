<?php
/**
 * Created by PhpStorm.
 * User: jfunk
 * Date: 12/15/17
 * Time: 12:06 PM
 */

namespace Tests;


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
			$this->assertGreaterThanOrEqual(1, $octet);
			$this->assertLessThanOrEqual(255, $octet);
		}
	}
}