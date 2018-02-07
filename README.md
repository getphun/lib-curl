# lib-curl

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