<?php
/**
 * Created by PhpStorm.
 * User: jfunk
 * Date: 11/16/17
 * Time: 9:30 AM
 */

namespace Piurafunk\PhpSdkFramework;

/**
 * Class BaseRoute
 * @package Piurafunk\PhpSdkFramework
 */
abstract class BaseRoute {

	/**
	 * @var ApiClientContract
	 */
	protected $api;

	/**
	 * @var string The URI to make requests to
	 */
	protected $uri = '';

	/**
	 * BaseRoute constructor.
	 * @param ApiClientContract $api
	 * @param $uri
	 */
	final public function __construct(ApiClientContract &$api, $uri) {
		$this->api = $api;
		$this->uri .= $uri;
	}

	/**
	 * Set the format of the return data
	 *
	 * @param $format
	 */
	final public function format($format) {
		$this->api->format($format);
	}

	/**
	 * Make a GET request to the API
	 *
	 * @param array $headers
	 * @param array $query
	 */
	abstract protected function get(array $headers = [], array $query = []);

	/**
	 * Make a POST request to the API
	 *
	 * @param array $headers
	 * @param array $query
	 * @param array $body
	 */
	abstract protected function post(array $headers = [], array $query = [], $body = []);

	/**
	 * Make a PATCH request to the API
	 *
	 * @param array $headers
	 * @param array $query
	 * @param array $body
	 * @return mixed
	 */
	abstract protected function patch(array $headers = [], array $query = [], array $body = []);

	/**
	 * Make a PUT request to the API
	 *
	 * @param array $headers
	 * @param array $query
	 * @param array $body
	 * @return mixed
	 */
	abstract protected function put(array $headers = [], array $query = [], array $body = []);

	/**
	 * Make a DELETE request to the API
	 *
	 * @param array $headers
	 * @param array $query
	 * @param array $body
	 * @return mixed
	 */
	abstract protected function delete(array $headers = [], array $query = [], array $body = []);
}