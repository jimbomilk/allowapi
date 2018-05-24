<?php


return [
    'standardsTree' => env('API_STANDARDS_TREE', 'x'),
    'subtype' => env('API_SUBTYPE', ''),
    'version' => env('API_VERSION', 'v1'),
    'prefix' => env('API_PREFIX', null),
    'domain' => env('API_DOMAIN', null),
    'name' => env('API_NAME', null),
    'conditionalRequest' => env('API_CONDITIONAL_REQUEST', true),
    'strict' => env('API_STRICT', false),
    'debug' => env('API_DEBUG', false),
    'errorFormat' => [
        'error' => [
            'message' => ':message',
            'errors' => ':errors',
            'code' => ':code',
            'status_code' => ':status_code',
            'debug' => ':debug'
        ]
    ],
    'middleware' => [],
    'auth' => [
        'jwt' => 'Dingo\Api\Auth\Provider\JWT',
    ],
    'throttling' => [],
    'transformer' => env('API_TRANSFORMER', Dingo\Api\Transformer\Adapter\Fractal::class),
    'defaultFormat' => env('API_DEFAULT_FORMAT', 'json'),
    'formats' => [

        'json' => Dingo\Api\Http\Response\Format\Json::class,

    ],
];
