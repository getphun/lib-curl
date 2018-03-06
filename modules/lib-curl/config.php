<?php
/**
 * lib-curl config file
 * @package lib-curl
 * @version 0.0.1
 * @upgrade true
 */

return [
    '__name' => 'lib-curl',
    '__version' => '0.0.1',
    '__git' => 'https://github.com/getphun/lib-curl',
    '__files' => [
        'modules/lib-curl' => ['install','remove','update'],
        'etc/log/lib-curl/.gitkeep' => ['install', 'remove']
    ],
    '__dependencies' => [],
    '_services' => [],
    '_autoload' => [
        'classes' => [
            'LibCurl\\Library\\Curl' => 'modules/lib-curl/library/Curl.php'
        ],
        'files' => []
    ]
];