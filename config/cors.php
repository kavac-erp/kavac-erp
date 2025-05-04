<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Configuración de uso compartido de recursos de origen cruzado (CORS)
    |--------------------------------------------------------------------------
    |
    | Aquí puede configurar sus ajustes para compartir recursos de
    | origen cruzado o "CORS". Esto determina qué operaciones de origen cruzado
    | pueden ejecutarse en los navegadores web. Usted es libre de ajustar esta
    | configuración según sea necesario.
    |
    | Para aprender más: https://developer.mozilla.org/en-US/docs/Web/HTTP/CORS
    |
    */

    'paths' => ['api/*'],

    'allowed_methods' => ['*'],

    'allowed_origins' => ['*'],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => false,

];
