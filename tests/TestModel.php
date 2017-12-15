<?php

namespace Tests;

use Piurafunk\PhpSdkFramework\BaseModel;

/**
 * Class TestModel
 * @property-read string $firstName
 * @property-read string $lastName
 * @property-read string $address
 * @property-read string $homeIpAddress
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
			''
		]
	];
}