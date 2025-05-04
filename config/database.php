<?php

use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Str;

return [

    /*
    |--------------------------------------------------------------------------
    | Nombre de conexión de base de datos predeterminado
    |--------------------------------------------------------------------------
    |
    | Aquí puede especificar cuál de las siguientes conexiones de base de datos
    | desea usar como su conexión predeterminada para todo el trabajo de la
    | base de datos. Por supuesto, puede usar muchas conexiones a la vez usando
    | la biblioteca de la base de datos.
    |
    */

    'default' => env('DB_CONNECTION', 'pgsql'),

    /*
    |--------------------------------------------------------------------------
    | Conexiones de base de datos
    |--------------------------------------------------------------------------
    |
    | Aquí está cada una de las conexiones de base de datos configuradas para
    | su aplicación. Por supuesto, a continuación se muestran ejemplos de
    | configuración de cada plataforma de base de datos compatible con Laravel
    | para simplificar el desarrollo.
    |
    |
    | Todo el trabajo de la base de datos en Laravel se realiza a través de las
    | instalaciones de PHP PDO, así que asegúrese de tener el controlador para
    | su base de datos particular instalada en su máquina antes de comenzar
    | el desarrollo.
    |
    */

    'connections' => [

        'sqlite' => [
            'driver' => 'sqlite',
            'url' => env('DATABASE_URL'),
            'database' => env('DB_DATABASE', database_path('database.sqlite')),
            'prefix' => '',
            'foreign_key_constraints' => env('DB_FOREIGN_KEYS', true),
        ],

        'mysql' => [
            'driver' => 'mysql',
            'url' => env('DATABASE_URL'),
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '3306'),
            'database' => env('DB_DATABASE', 'forge'),
            'username' => env('DB_USERNAME', 'forge'),
            'password' => env('DB_PASSWORD', ''),
            'unix_socket' => env('DB_SOCKET', ''),
            'charset' => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => true,
            'engine' => 'InnoDB',
            'options' => extension_loaded('pdo_mysql') ? array_filter([
                PDO::MYSQL_ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
            ]) : [],
            'dump' => [
                'dump_binary_path' => '/usr/bin',
                'use_single_transaction',
                'timeout' => 60 * 20, // tiempo de espera de 20 minutos
            ]
        ],

        'pgsql' => [
            'driver' => 'pgsql',
            'url' => env('DATABASE_URL'),
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '5432'),
            'database' => env('DB_DATABASE', 'forge'),
            'username' => env('DB_USERNAME', 'forge'),
            'password' => env('DB_PASSWORD', ''),
            'charset' => 'utf8',
            'prefix' => '',
            'prefix_indexes' => true,
            'schema' => 'public',
            'sslmode' => 'prefer',
            'dump' => [
                'dump_binary_path' => '/usr/bin',
                'use_single_transaction',
                'timeout' => 60 * 20, // tiempo de espera de 20 minutos
                'add_extra_option' => '-b -O --inserts --exclude-table-data=sessions',
            ]
        ],

        'sqlsrv' => [
            'driver' => 'sqlsrv',
            'url' => env('DATABASE_URL'),
            'host' => env('DB_HOST', 'localhost'),
            'port' => env('DB_PORT', '1433'),
            'database' => env('DB_DATABASE', 'forge'),
            'username' => env('DB_USERNAME', 'forge'),
            'password' => env('DB_PASSWORD', ''),
            'charset' => 'utf8',
            'prefix' => '',
            'prefix_indexes' => true,
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Tabla del repositorio de migración
    |--------------------------------------------------------------------------
    |
    | Esta tabla realiza un seguimiento de todas las migraciones que ya se han
    | ejecutado para su aplicación. Con esta información, podemos determinar
    | cuáles de las migraciones en el disco no se han ejecutado realmente en
    | la base de datos.
    |
    */

    'migrations' => 'migrations',

    /*
    |--------------------------------------------------------------------------
    | Bases de datos Redis
    |--------------------------------------------------------------------------
    |
    | Redis es un almacén de clave-valor de código abierto, rápido y avanzado
    | que también proporciona un cuerpo de comandos más rico que los sistemas
    | típicos de clave-valor como APC o Memcached. Laravel hace que sea fácil
    | profundizar.
    |
    */

    'redis' => [

        'client' => env('REDIS_CLIENT', 'phpredis'),

        'options' => [
            'cluster' => env('REDIS_CLUSTER', 'redis'),
            'prefix' => env('REDIS_PREFIX', Str::slug(env('APP_NAME', 'laravel'), '_') . '_database_'),
        ],

        'default' => [
            'host' => env('REDIS_HOST', '127.0.0.1'),
            'password' => env('REDIS_PASSWORD', null),
            'port' => env('REDIS_PORT', 6379),
            'database' => env('REDIS_DB', 0),
            //'prefix' => env('REDIS_PREFIX', env('APP_NAME', 'laravel') . '_' . 'session')
        ],

        'cache' => [
            'host' => env('REDIS_HOST', '127.0.0.1'),
            'password' => env('REDIS_PASSWORD', null),
            'port' => env('REDIS_PORT', 6379),
            'database' => env('REDIS_CACHE_DB', 1),
            'prefix' => env('REDIS_PREFIX', env('APP_NAME', 'laravel') . '_' . 'cache')
        ],

        'queue' => [
            'host' => env('REDIS_HOST', '127.0.0.1'),
            'password' => env('REDIS_PASSWORD', null),
            'port' => env('REDIS_PORT', 6379),
            'database' => env('REDIS_QUEUE_DB', 2),
            'prefix' =>  env('REDIS_QUEUE_PREFIX', 'q:'),
        ],
    ],

];
