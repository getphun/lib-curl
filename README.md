# lib-curl

## Konfigurasi

Library ini memiliki satu konfigurasi untuk me-log semua aktifitas curl seperti di bawah:

```php
return [
	'_name' => 'Phun',
	...,
	'libCurl' => [
		'log' => false
	]
];
```

## Usage

```php
<?php

use LibCurl\Library\Curl;

$res = Curl::fetch([
	'url' => 'String url',
	'query' => 'Array [key=>value]',
	'body' => 'Array [key=>value]',
	'headers' => 'Array [key=value]',
	'handler' => 'String handler[json]'
]);
```