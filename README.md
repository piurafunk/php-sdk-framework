# php-sdk-framework
A framework for simple PHP SDK packages

# Installation
`composer require piurafunk/php-sdk-framework`

# Usage
## ApiClientContract
Implement the `Piurafunk\PhpSdkFramework\ApiClientContract` interface and define the functionality for the `makeRequest` method. This function should handle firing the HTTP request off to the API, and returning an appropriate data type to the calling method.

## Models
Extend the `Piurafunk\PhpSdkFramework\BaseModel` class. Override the `$attributeKeys` array. Each element should have the following structure:

```
'propertyName' => [
  'attribute' => '',
  'type' => '',
  'returnType' => '',
  'arrayOf' => '',
  'callable' => '',
  'description' => ''
]
```

Where each index means the following:

|Index|Description|Valid Input
|---|---|---|
|attribute|The attribute name as returned by the API|Any|
|type|The type used by the faker to generate appropriate data. This is only used in dev mode|string, integer, boolean, float, double, \namespace\model::class|
|returnType|The type of data this variable will be returned as|Any valid type (ie, primitives, models, primitive[], models[])|
|description|A description of this variable|Any|
|callable|A function that mutates the data every time it is retrieved from the model|A callback|

