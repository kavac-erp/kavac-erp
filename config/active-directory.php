<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Configuración para el uso de Directorio Activo
    |--------------------------------------------------------------------------
    |
    | Aquí puede configurar sus ajustes para establecer la autenticación en la aplicación
    | a través de Directorio Activo
    |
    */
    'enabled' => env('ACTIVE_DIRECTORY', false),

    'url' => env('ACTIVE_DIRECTORY_URL', ''),

    'dn' => env('ACTIVE_DIRECTORY_DN', ''),

    'base_dn' => env('ACTIVE_DIRECTORY_BASE_DN', ''),
];
