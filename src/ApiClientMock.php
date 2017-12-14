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

	const MODEL_MAPPING = [];

	/**
	 * @var array|string The mappings that are available based on the URL we have entered so far
	 */
	private $subMapping = self::MODEL_MAPPING;

	/**
	 * @var \Faker\Generator
	 */
	private $faker;

	/**
	 * ApiClientMock constructor.
	 */
	public function __construct() {
		$this->faker = Factory::create();
	}

	/**
	 * @param $name
	 * @param $arguments
	 * @return $this|BaseModel|BaseModel[]
	 * @throws NotImplementedException
	 */
	public function __call($name, $arguments) {
		switch ($name) {
			case 'location':
				break;
			case 'get':
				$this->extendUrl($name);
				$subMapping = $this->subMapping;
				$this->subMapping = self::MODEL_MAPPING;
				return $this->generateModel($subMapping['default']);
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
	private function generateModel($subMapping) {
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
			$objects[] = new $className($this->generateAttributesForClass($className));
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
	private function generateAttributesForClass($className) {
		$className::reformatAttributeKeys();
		$attributesToGenerate = $className::$attributeKeys;

		$attributes = [];
		foreach ($attributesToGenerate as $attributeToGenerate) {
			$type = $attributeToGenerate['type'];
			$key = $attributeToGenerate['attribute'];
			$returnType = $attributeToGenerate['returnType'];
			$arrayOf = ($type === 'array' ? $attributeToGenerate['arrayOf'] : null);

			if (strpos($type, '|') !== false) {
				$type = explode('|', $type);
				$type = ($type[0] !== 'null' ? $type[0] : $type[1]);
			}

			switch ($type) {
				case 'array':
					$attributes[$key] = $this->generateArray($arrayOf);
					break;
				case 'callable':
					$attributes[$key] = $this->generateCallable($returnType);
					break;
				case 'string':
				case 'integer':
				case 'int':
				case 'boolean':
				case 'bool':
				case 'double':
				case 'float':
					$attributes[$key] = $this->generatePrimitive($type, $attributeToGenerate['helperType']);
					break;
				default:
					$attributes[$key] = $this->generateAttributesForClass($returnType);
			}
		}

		return $attributes;
	}

	/**
	 * @param $returnType
	 * @return bool|float|int|string|array
	 * @throws NotImplementedException
	 */
	private function generateCallable($returnType) {
		if (substr($returnType, -2) === '[]')
			return $this->generateArray(substr($returnType, 0, -2));
		switch ($returnType) {
			case 'string':
			case 'integer':
			case 'int':
			case 'boolean':
			case 'bool':
			case 'double':
			case 'float':
				return $this->generatePrimitive($returnType);
				break;
			default:
				throw new NotImplementedException();
		}
	}

	/**
	 * @param $arrayOf
	 * @return array
	 * @throws NotImplementedException
	 */
	private function generateArray($arrayOf) {
		$count = $this->faker->numberBetween(1, 50);

		$array = [];
		for ($i = 0; $i < $count; $i++) {
			switch ($arrayOf) {
				case 'string':
				case 'integer':
				case 'int':
				case 'boolean':
				case 'bool':
				case 'double':
				case 'float':
					$array[] = $this->generatePrimitive($arrayOf);
					break;
				default:
					$array[] = $this->generateAttributesForClass($arrayOf);
			}
		}

		return $array;
	}

	/**
	 * @param $type
	 * @param string $helperType
	 * @return bool|float|int|string
	 * @throws NotImplementedException
	 */
	private function generatePrimitive($type, $helperType = '') {
		switch ($type) {
			case 'string':
				switch ($helperType) {
					case 'ip':
						return $this->faker->ipv4;
					case '':
						return $this->faker->word;
					default:
						throw new NotImplementedException();
				}
			case 'integer':
			case 'int':
				return $this->faker->randomnumber();
			case 'boolean':
			case 'bool':
				return $this->faker->boolean;
			case 'double':
			case 'float':
				return $this->faker->randomFloat();
			default:
				throw new NotImplementedException();
		}
	}

	/**
	 * @param $name
	 */
	private function extendUrl($name) {
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
}
