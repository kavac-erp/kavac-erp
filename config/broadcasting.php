<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Emisor predeterminado
    |--------------------------------------------------------------------------
    |
    | Esta opción controla el emisor predeterminado que utilizará el framework
    | cuando sea necesario transmitir un evento. Puede establecer esto en
    | cualquiera de las conexiones definidas en la matriz de "conexiones"
    | a continuación.
    |
    | Soportado: "pusher", "redis", "log", "null"
    |
    */

    'default' => env('BROADCAST_DRIVER', 'null'),

    /*
    |--------------------------------------------------------------------------
    | Conexiones de difusión
    |--------------------------------------------------------------------------
    |
    | Aquí puede definir todas las conexiones de transmisión que se utilizarán
    | para transmitir eventos a otros sistemas o a través de websockets.
    | Dentro de esta matriz se proporcionan ejemplos de cada tipo de conexión
    | disponible.
    |
    */

    'connections' => [

        'pusher' => [
            'driver' => 'pusher',
            'key' => env('PUSHER_APP_KEY'),
            'secret' => env('PUSHER_APP_SECRET'),
            'app_id' => env('PUSHER_APP_ID'),
            'options' => [
                'cluster' => env('PUSHER_APP_CLUSTER'),
                'useTLS' => env('BROADCAST_TLS', false),
                'encrypted' => true,
                'host' => env('BROADCAST_HOST', '127.0.0.1'),
                'port' => env('BROADCAST_PORT', 6001),
                'path' => env('BROADCAST_PATH', ''),
                'scheme' => env('BROADCAST_SCHEME', 'http'),
                'curl_options' => [
                    CURLOPT_SSL_VERIFYHOST => 0,
                    CURLOPT_SSL_VERIFYPEER => 0,
                ]
            ],
        ],

        'redis' => [
            'driver' => 'redis',
            'connection' => 'default',
        ],

        'log' => [
            'driver' => 'log',
        ],

        'null' => [
            'driver' => 'null',
        ],

    ],

];
