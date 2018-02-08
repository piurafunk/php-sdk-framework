<?php
/**
 * Created by PhpStorm.
 * User: jfunk
 * Date: 11/29/17
 * Time: 1:28 PM
 */

namespace Piurafunk\PhpSdkFramework;

use Faker\Factory;

/**
 * A mock class for the OnappApi
 *
 * Class OnappApiMock
 * @package App\Services
 */
class ApiClientMock implements ApiClientContract {

	const MODEL_MAPPING = [
		'get' => [
			'default' => TestModel::class,
			'chart' => 'string'
		]
	];

	/**
	 * @var \Closure[] An array of functions to generate fake data
	 */
	protected static $customGenerators = [];

	/**
	 * @var array|string The mappings that are available based on the URL we have entered so far
	 */
	private $subMapping;

	/**
	 * @var \Faker\Generator
	 */
	private $faker;

	/**
	 * @var string The format of the return data
	 */
	private $format = 'default';

	/**
	 * ApiClientMock constructor.
	 */
	final public function __construct() {
		$this->faker = Factory::create();
		$this->subMapping = static::MODEL_MAPPING;
	}

	/**
	 * @param string $format
	 * @return static
	 */
	final public function format($format = 'default') {
		$this->format = $format;
		return $this;
	}

	/**
	 * Add a function to the array of custom generators
	 *
	 * @param string $key
	 * @param \Closure $callable
	 */
	final public static function addGenerator($key, \Closure $callable) {
		static::$customGenerators[$key] = $callable;
	}

	/**
	 * Remove a function from the array of custom generators
	 *
	 * @param string $key
	 */
	final public static function removeGenerator($key) {
		unset(static::$customGenerators[$key]);
	}

	/**
	 * @param $name
	 * @param $arguments
	 * @return $this|BaseModel|BaseModel[]
	 * @throws NotImplementedException
	 */
	public function __call($name, $arguments) {
		switch ($name) {
			case 'get':
			case 'post':
			case 'put':
			case 'patch':
			case 'delete':
				$this->extendUrl($name);
				$subMapping = $this->subMapping;
				$this->reset();
				return $this->generateModel($subMapping[$this->format]);
			default:
				$this->extendUrl($name);
		}

		return $this;
	}

	/**
	 * @param $subMapping
	 * @return BaseModel|BaseModel[]
	 * @throws NotImplementedException
	 */
	final private function generateModel($subMapping) {
		$isArrayOfObjects = false;

		if (substr($subMapping, -2) == '[]') {
			$className = substr($subMapping, 0, -2);
			$isArrayOfObjects = true;
		} else {
			$className = $subMapping;
		}

		/** @var BaseModel $className */

		$numberOfObjects = ($isArrayOfObjects ? $this->faker->numberBetween(1, 50) : 1);

		$objects = [];
		for ($i = 0; $i < $numberOfObjects; $i++) {
			if (is_subclass_of($className, BaseModel::class)) {
				$objects[] = new $className($this->generateAttributesForClass($className));
			} else {
				$objects[] = $this->generateAttributeOfType($className);
			}
		}

		if ($isArrayOfObjects) {
			return $objects;
		} else {
			return $objects[0];
		}
	}

	/**
	 * @param BaseModel $className
	 * @return array
	 * @throws NotImplementedException
	 */
	final private function generateAttributesForClass($className) {
		$className::reformatAttributeKeys();
		$attributesToGenerate = $className::$attributeKeys;

		$attributes = [];
		foreach ($attributesToGenerate as $attributeToGenerate) {
			$type = $attributeToGenerate['type'];
			$key = $attributeToGenerate['attribute'];
			$returnType = $attributeToGenerate['returnType'];
			$arrayOf = ($type === 'array' ? $attributeToGenerate['arrayOf'] : null);
			$tweaks = $attributeToGenerate['tweaks'];

			if (strpos($type, '|') !== false) {
				$type = explode('|', $type);
				$type = ($type[0] !== 'null' ? $type[0] : $type[1]);
			}

			switch ($type) {
				case 'array':
					$attributes[$key] = $this->generateArray($arrayOf, $tweaks);
					break;
				case 'callable':
					$attributes[$key] = $this->generateAttributeOfType($returnType, $tweaks);
					break;
				default:
					$attributes[$key] = $this->generateAttributeOfType($type, $tweaks);
			}
		}

		return $attributes;
	}

	/**
	 * @param $arrayOf
	 * @param array $tweaks
	 * @return array
	 * @throws NotImplementedException
	 */
	final private function generateArray($arrayOf, array $tweaks = []) {
		$count = $this->faker->numberBetween(1, 50);

		$array = [];

		if (substr($arrayOf, -2) === '[]') {
			for ($i = 0; $i < $count; $i++) {
				$array[] = $this->generateArray(substr($arrayOf, 0, -2), $tweaks);
			}
		} else {
			for ($i = 0; $i < $count; $i++) {
				$array[] = $this->generateAttributeOfType($arrayOf, $tweaks);
			}

		}

		return $array;
	}

	/**
	 * @param $type
	 * @param array $tweaks
	 * @return bool|float|int|string|array
	 * @throws NotImplementedException
	 */
	final private function generateAttributeOfType($type, array $tweaks = []) {
		if (array_key_exists($type, static::$customGenerators)) {
			return (static::$customGenerators[$type])($this->faker);
		}

		switch ($type) {
			case 'ip':
			case 'ipv4':
				return $this->faker->ipv4;
			case 'ipv6':
				return $this->faker->ipv6;
			case 'firstName':
				return $this->faker->firstName;
			case 'lastName':
				return $this->faker->lastName;
			case 'address':
				return $this->faker->address;
			case 'date':
				return $this->faker->date('Y-m-d H:i:s');
			case 'phone':
				return $this->faker->phoneNumber;
			case 'string':
				return $this->faker->word;
			case 'integer':
			case 'int':
				return $this->generateInteger($tweaks);
			case 'boolean':
			case 'bool':
				return $this->faker->boolean;
			case 'double':
			case 'float':
				return $this->faker->randomFloat();
			default:
				return $this->generateAttributesForClass($type);
		}
	}

	/**
	 * Generate an integer, obeying the tweaks provided
	 *
	 * @param array $tweaks
	 * @return int
	 */
	final private function generateInteger(array $tweaks = []) {
		$min = array_key_exists('min', $tweaks) ? $tweaks['min'] : 0;
		$max = array_key_exists('max', $tweaks) ? $tweaks['max'] : 2147483647;

		return $this->faker->numberBetween($min, $max);
	}

	/**
	 * @param $name
	 */
	final private function extendUrl($name) {
		switch ($name) {
			default:
				$this->subMapping = $this->subMapping[$name];
		}
	}

	/**
	 * @param string $method
	 * @param string $uri
	 * @param array $headers
	 * @param array $query
	 * @param array $body
	 * @return array|ApiClientMock|BaseModel|BaseModel[]|string
	 * @throws NotImplementedException
	 */
	final public function makeRequest($method = 'GET', $uri = '/', array $headers = [], array $query = [], array $body = []) {
		return $this->__call(strtolower($method), []);
	}

	/**
	 * Reset the faker API to a fresh configuration
	 */
	public function reset() {
		$this->subMapping = static::MODEL_MAPPING;
	}
}
