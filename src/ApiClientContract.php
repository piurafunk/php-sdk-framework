<?php
/**
 * Created by PhpStorm.
 * User: jfunk
 * Date: 11/16/17
 * Time: 8:35 AM
 */

namespace Piurafunk\PhpSdkFramework;

/**
 * Interface ApiClientContract
 * @package Piurafunk\PhpSdkFramework
 */
interface ApiClientContract {

	/**
	 * Make a request to the API
	 *
	 * @param string $method
	 * @param string $uri
	 * @param array $headers
	 * @param array $query
	 * @param array $body
	 *
	 * @return array|string
	 */
	function makeRequest($method = 'GET', $uri = '/', array $headers = [], array $query = [], array $body = []);

	/**
	 * Set the format of the return data
	 *
	 * @param $format
	 * @return static
	 */
	function format($format);
}