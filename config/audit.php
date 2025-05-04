<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Implementación de auditoría
    |--------------------------------------------------------------------------
    |
    | Definir qué implementación del modelo de auditoría se debe utilizar.
    |
    */

    'implementation' => OwenIt\Auditing\Models\Audit::class,

    /*
    |--------------------------------------------------------------------------
    | Prefijo Morph de usuario y guards
    |--------------------------------------------------------------------------
    |
    | Defina el prefijo morph y las protecciones de autenticación para el resolutor de usuarios.
    |
    */

    'user' => [
        'morph_prefix' => 'user',
        'guards'       => [
            'web',
            'api',
        ],
        'resolver'     => OwenIt\Auditing\Resolvers\UserResolver::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Resolutores de auditoría
    |--------------------------------------------------------------------------
    |
    | Definir las implementaciones de usuario, dirección IP, agente de usuario y resolución de URL.
    |
    */
    'resolver' => [
        'ip_address' => OwenIt\Auditing\Resolvers\IpAddressResolver::class,
        'user_agent' => OwenIt\Auditing\Resolvers\UserAgentResolver::class,
        'url'        => OwenIt\Auditing\Resolvers\UrlResolver::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Eventos de auditoría
    |--------------------------------------------------------------------------
    |
    | Los eventos Eloquent que desencadenan una Auditoría.
    |
    */

    'events' => [
        'created',
        'updated',
        'deleted',
        'restored',
    ],

    /*
    |--------------------------------------------------------------------------
    | Modo estricto
    |--------------------------------------------------------------------------
    |
    | ¿Habilitar el modo estricto al auditar?
    |
    */

    'strict' => true,

    /*
    |--------------------------------------------------------------------------
    | Marcas de tiempo de auditoría
    |--------------------------------------------------------------------------
    |
    | ¿Deberían auditarse las marcas de tiempo created_at, updated_at y delete_at?
    |
    */

    'timestamps' => true,

    /*
    |--------------------------------------------------------------------------
    | Umbral de auditoría
    |--------------------------------------------------------------------------
    |
    | Especifique un umbral para la cantidad de registros de auditoría que puede tener un modelo.
    | Cero significa sin límite.
    |
    */

    'threshold' => env('AUDIT_LIMIT', 0),

    /*
    |--------------------------------------------------------------------------
    | Controlador de Auditoría
    |--------------------------------------------------------------------------
    |
    | El controlador de auditoría predeterminado utilizado para realizar un seguimiento de los cambios.
    |
    */

    'driver' => 'database',

    /*
    |--------------------------------------------------------------------------
    | Configuraciones del controlador de auditoría
    |--------------------------------------------------------------------------
    |
    | Controladores de auditoría disponibles y configuraciones respectivas.
    |
    */

    'drivers' => [
        'database' => [
            'table'      => 'audits',
            'connection' => env('DB_CONNECTION', 'pgsql'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Consola de auditoría
    |--------------------------------------------------------------------------
    |
    | Si se deben auditar los eventos de la consola (p. ej., php artisan db:seed).
    |
    */

    'console' => false, //false para no auditar cuando se hace una migración por consola
];
