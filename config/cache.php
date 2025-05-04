<?php

use Illuminate\Support\Str;

return [

    /*
    |--------------------------------------------------------------------------
    | Almacén de caché predeterminado
    |--------------------------------------------------------------------------
    |
    | Esta opción controla la conexión de caché predeterminada que se usa al
    | utilizar esta biblioteca de almacenamiento en caché. Esta conexión se
    | utiliza cuando no se especifica explícitamente otra al ejecutar una
    | determinada función de almacenamiento en caché.
    |
    | Soportado: "apc", "array", "database", "file",
    |            "memcached", "redis", "dynamodb"
    |
    */

    'default' => env('CACHE_DRIVER', 'file'),

    /*
    |--------------------------------------------------------------------------
    | Almacenes de caché
    |--------------------------------------------------------------------------
    |
    | Aquí puede definir todos los "almacenes" de caché para su aplicación,
    | así como sus controladores. Incluso puede definir varios almacenes para
    | el mismo controlador de caché para agrupar tipos de elementos almacenados
    | en sus cachés.
    |
    */

    'stores' => [

        'apc' => [
            'driver' => 'apc',
        ],

        'array' => [
            'driver' => 'array',
        ],

        'database' => [
            'driver' => 'database',
            'table' => 'cache',
            'connection' => null,
        ],

        'file' => [
            'driver' => 'file',
            'path' => storage_path('framework/cache/data'),
        ],

        'memcached' => [
            'driver' => 'memcached',
            'persistent_id' => env('MEMCACHED_PERSISTENT_ID'),
            'sasl' => [
                env('MEMCACHED_USERNAME'),
                env('MEMCACHED_PASSWORD'),
            ],
            'options' => [
                // Memcached::OPT_CONNECT_TIMEOUT => 2000,
            ],
            'servers' => [
                [
                    'host' => env('MEMCACHED_HOST', '127.0.0.1'),
                    'port' => env('MEMCACHED_PORT', 11211),
                    'weight' => 100,
                ],
            ],
        ],

        'redis' => [
            'driver' => 'redis',
            'connection' => 'cache',
        ],

        'dynamodb' => [
            'driver' => 'dynamodb',
            'key' => env('AWS_ACCESS_KEY_ID'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),
            'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
            'table' => env('DYNAMODB_CACHE_TABLE', 'cache'),
            'endpoint' => env('DYNAMODB_ENDPOINT'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Prefijo de clave de caché
    |--------------------------------------------------------------------------
    |
    | Al utilizar un almacen de datos basado en RAM como APC o Memcached,
    | puede haber otras aplicaciones que utilicen el mismo caché. Entonces,
    | especificaremos un valor para que se prefije a todas nuestras claves
    | para que podamos evitar colisiones.
    |
    */

    'prefix' => env('CACHE_PREFIX', Str::slug(env('APP_NAME', 'laravel'), '_') . '_cache'),

];
