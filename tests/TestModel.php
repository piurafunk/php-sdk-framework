<?php

namespace Piurafunk\PhpSdkFramework;

/**
 * Class TestModel
 * @property-read string $firstName
 * @property-read string $lastName
 * @property-read string $address
 * @property-read string $homeIpAddress
 * @property-read integer $createdAt
 * @property-read string|null $phoneNumber
 */
class TestModel extends BaseModel {

	public static $attributeKeys = [
		'firstName' => [
			'type' => 'firstName',
			'returnType' => 'string',
			'attribute' => 'first_name'
		],
		'lastName' => [
			'type' => 'lastName',
			'returnType' => 'string',
			'attribute' => 'last_name'
		],
		'address' => [
			'type' => 'address',
			'returnType' => 'string',
		],
		'homeIpAddress' => [
			'type' => 'ip',
			'returnType' => 'string',
			'attribute' => 'home_ip_address'
		],
		'phoneNumber' => [
			'type' => 'phone',
			'attribute' => 'phone_number',
			'nullable' => true,
			'returnType' => 'string'
		]
	];

	/**
	 * @throws NotImplementedException
	 */
	public static function reformatAttributeKeys() {
		static::$attributeKeys['createdAt'] = [
			'type' => 'date',
			'attribute' => 'created_at',
			'returnType' => 'integer',
			'callable' => function ($value) {
				return strtotime($value);
			}
		];

		parent::reformatAttributeKeys();
	}
}