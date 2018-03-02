<?php
/**
 * Created by PhpStorm.
 * User: jfunk
 * Date: 11/16/17
 * Time: 9:33 AM
 */

namespace Piurafunk\PhpSdkFramework;

/**
 * Class BaseModel
 * @package Onapp\Models
 */
class BaseModel {

	/**
	 * @var array An array of properties returned from the API
	 */
	protected $attributes;

	/**
	 * @var array Attributes that the user sets at run time
	 */
	protected $customAttributes = [];

	/**
	 * @var array
	 */
	public static $attributeKeys = [];

	/**
	 * BaseModel constructor.
	 * @param array $attributes
	 * @throws NotImplementedException
	 */
	public function __construct(array $attributes) {
		static::reformatAttributeKeys();
		$this->attributes = $attributes;

		foreach (static::$attributeKeys as $property => $attributeKey) {
			$hasAttribute = $this->has($attributeKey['attribute']);
			if (!$hasAttribute)
				continue;
			$value = $this->attributes[$attributeKey['attribute']];
			$isObject = static::isObject($attributeKey['type']);
			$isArrayOfObjects = static::isArrayOfObjects($attributeKey);
			if ($isObject && $hasAttribute) {
				$this->attributes[$attributeKey['attribute']] = new $attributeKey['type']($value);
				continue;
			} elseif ($isArrayOfObjects && $hasAttribute) {
				$this->attributes[$attributeKey['attribute']] = [];
				foreach ($value as $objectData) {
					$this->attributes[$attributeKey['attribute']][] = new $attributeKey['arrayOf']($objectData);
				}
			}
		}
	}

	/**
	 * Check if an attribute is an array of objects
	 *
	 * @param array $attributeKey
	 * @return bool
	 */
	protected static function isArrayOfObjects(array $attributeKey) {

		$type = $attributeKey['type'];

		if ($type !== 'array') return false;

		$returnType = $attributeKey['returnType'];

		$returnType = str_replace('[]', '', $returnType);

		return static::isObject($returnType);
	}

	/**
	 * Generate the docblock text for the model
	 *
	 * @return array
	 * @throws NotImplementedException
	 */
	public static function generateDocBlock() {
		static::reformatAttributeKeys();
		$return = [];
		foreach (static::$attributeKeys as $property => $attributeKey) {
			$type = $attributeKey['type'];
			$description = $attributeKey['description'];
			$return[] = " * @property-read $type $$property" . ($description ? " $description" : "") . PHP_EOL;
		}
		return $return;
	}

	/**
	 * Retrieve the attribute keys array
	 *
	 * @return array
	 * @throws NotImplementedException
	 */
	public static function getAttributeKeys() {
		static::reformatAttributeKeys();
		return static::$attributeKeys;
	}

	/**
	 * Format the static::$attributeKeys property to follow the proper format
	 * @throws NotImplementedException
	 */
	public static function reformatAttributeKeys() {
		$newAttributeKeys = [];
		foreach (static::$attributeKeys as $property => $attributeKey) {
			if (is_int($property)) {
				$newAttributeKeys[$attributeKey] = [
					'attribute' => $attributeKey,
					'type' => 'string',
					'returnType' => 'string',
					'description' => '',
					'helperType' => '',
					'tweaks' => []
				];
			} elseif (is_array($attributeKey)) {
				$propertyData = [];

				// Determine attribute name
				$attribute = array_key_exists('attribute', $attributeKey) ? $attributeKey['attribute'] : $property;
				$propertyData['attribute'] = $attribute;

				// Determine type
				$type = array_key_exists('type', $attributeKey) ? $attributeKey['type'] : 'string';
				$returnType = array_key_exists('returnType', $attributeKey) ? $attributeKey['returnType'] : $type;
				if (static::isObject($type)) {
					if (substr($type, 0, 1) !== '\\')
						$returnType = '\\' . $type;
				} elseif ($type === 'array') {
					// Determine array of
					$propertyData['arrayOf'] = $attributeKey['arrayOf'];

					if (static::isObject($attributeKey['arrayOf']))
						$returnType = "\\${attributeKey['arrayOf']}[]";
					else
						$returnType = "${attributeKey['arrayOf']}[]";
				}

				$propertyData['type'] = $type;
				$propertyData['returnType'] = $returnType;

				// Determine callable
				if (array_key_exists('callable', $attributeKey)) {
					if ($attributeKey['callable'] instanceof \Closure) {
						$propertyData['callable'] = $attributeKey['callable'];
					}
				}

				// Gather tweaks
				$tweaks = array_key_exists('tweaks', $attributeKey) ? $attributeKey['tweaks'] : [];
				$propertyData['tweaks'] = $tweaks;

				// Determine nullable
				$nullable = array_key_exists('nullable', $attributeKey) ? (bool)$attributeKey['nullable'] : false;
				$propertyData['nullable'] = $nullable;

				// Determine helper type
				$helperType = array_key_exists('helperType', $attributeKey) ? $attributeKey['helperType'] : '';
				$propertyData['helperType'] = $helperType;

				// Determine description
				$description = array_key_exists('description', $attributeKey) ? $attributeKey['description'] : '';
				$propertyData['description'] = $description;

				// Add to new array
				$newAttributeKeys[$property] = $propertyData;
			} else {
				throw new NotImplementedException('The format for this model is incorrect: ' . static::class . '; Property: ' . print_r($property, true));
			}
		}
		static::$attributeKeys = $newAttributeKeys;
	}

	/**
	 * Check if the provided string is an object type
	 *
	 * @param $type
	 * @return bool
	 */
	protected static function isObject($type) {
		return class_exists($type);
	}

	/**
	 * Checks if our attributes contain the key specified
	 *
	 * @param $key
	 * @return bool
	 */
	protected function has($key) {
		return array_key_exists($key, $this->attributes);
	}

	/**
	 * Retrieve the specified attribute
	 *
	 * @param $property
	 * @return bool|float|int|mixed|null
	 */
	public function __get($property) {

		// If it is a custom property, return that first and foremost
		if (array_key_exists($property, $this->customAttributes))
			return $this->customAttributes[$property];

		// If it's not a property that the SDK is concerned about, return null
		if (!array_key_exists($property, static::$attributeKeys))
			return null;

		// If we didn't get the property back from the API, return null
		if (!$this->has(static::$attributeKeys[$property]['attribute']))
			return null;


		// Get they property information and the concrete attribute value
		$attributeKey = static::$attributeKeys[$property];
		$value = $this->attributes[$attributeKey['attribute']];

		// If the callable is set, let's call and return it
		if (array_key_exists('callable', $attributeKey) && $attributeKey['callable'] instanceof \Closure) {
			return $attributeKey['callable']($value);
		}

		switch ($attributeKey['type']) {
			case 'int':
			case 'integer':
				return intval(str_replace(',', '', $value));
			case 'float':
			case 'single':
				return floatval(str_replace(',', '', $value));
			case 'bool':
			case 'boolean':
				return boolval($value);
			case 'double':
				return doubleval(str_replace(',', '', $value));
			default:
				return $value;
		}
	}

	/**
	 * Set a custom attribute on this model
	 *
	 * @param $name
	 * @param $value
	 */
	public function __set($name, $value) {
		$this->customAttributes[$name] = $value;
	}

	/**
	 * Return an array of all the attributes
	 *
	 * @return array
	 */
	public function toArray() {
		$returnArray = [];
		foreach (static::$attributeKeys as $propertyName => $attributeKey) {

			$property = $this->$propertyName;

			if (is_array($property)) {
				$propertyAsArray = [];
				foreach ($property as $value) {
					if ($value instanceof self)
						$propertyAsArray[] = $value->toArray();
					else
						$propertyAsArray[] = $value;
				}
				$returnArray[$propertyName] = $propertyAsArray;
			} elseif ($property instanceof self) {
				$returnArray[$propertyName] = $property->toArray();
			} else
				$returnArray[$propertyName] = $property;
		}

		return array_merge($returnArray, $this->customAttributes);
	}
}