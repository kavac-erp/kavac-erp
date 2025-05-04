<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Valores predeterminados de autenticación
    |--------------------------------------------------------------------------
    |
    | Esta opción controla las opciones predeterminadas de "protección" de
    | autenticación y restablecimiento de contraseña para su aplicación.
    | Puede cambiar estos valores predeterminados según sea necesario,
    | pero son un comienzo perfecto para la mayoría de las aplicaciones.
    |
    */
    'active_directory' => [
        'enabled' => env('ACTIVE_DIRECTORY', false),
        'url' => env('ACTIVE_DIRECTORY_URL', null),
        'dn' => env('ACTIVE_DIRECTORY_DN', null),
        'base_dn' => env('ACTIVE_DIRECTORY_BASE_DN', null),
    ],

    'defaults' => [
        'guard' => 'web',
        'passwords' => 'users',
    ],

    /*
    |--------------------------------------------------------------------------
    | Protección de autenticación
    |--------------------------------------------------------------------------
    |
    | A continuación, puede definir cada guardia de autenticación para su
    | aplicación. Por supuesto, aquí se ha definido una excelente configuración
    | predeterminada que utiliza el almacenamiento de sesiones y el proveedor
    | de usuarios de Eloquent.
    |
    | Todos los controladores de autenticación tienen un proveedor de usuarios.
    | Esto define cómo los usuarios se recuperan de su base de datos u otros
    | mecanismos de almacenamiento utilizados por esta aplicación para conservar
    | los datos de su usuario.
    |
    | Soportado: "session", "token"
    |
    */

    'guards' => [
        'web' => [
            'driver' => 'session',
            'provider' => 'users',
        ],

        'api' => [
            'driver' => 'token',
            'provider' => 'users',
            'hash' => false,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Proveedores de usuarios
    |--------------------------------------------------------------------------
    |
    | Todos los controladores de autenticación tienen un proveedor de usuarios.
    | Esto define cómo los usuarios se recuperan de la base de datos u otros
    | mecanismos de almacenamiento utilizados por esta aplicación para
    | conservar los datos de su usuario.
    |
    | Si tiene varias tablas o modelos de usuario, puede configurar varias
    | fuentes que representen cada modelo/tabla. Estas fuentes se pueden asignar
    | a cualquier "guard" de autenticación adicional que haya definido.
    |
    | Soportado: "database", "eloquent"
    |
    */

    'providers' => [
        'users' => [
            'driver' => 'eloquent',
            'model' => App\Models\User::class,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Restablecimiento de contraseñas
    |--------------------------------------------------------------------------
    |
    | Puede especificar varias configuraciones de restablecimiento de contraseña
    | si tiene más de una tabla o modelo de usuario en la aplicación y desea
    | tener configuraciones de restablecimiento de contraseña independientes
    | según los tipos de usuario específicos.
    |
    | El tiempo de caducidad es la cantidad de minutos que el token de
    | restablecimiento debe considerarse válido. Esta función de seguridad hace
    | que los tokens duren poco, por lo que tienen menos tiempo para ser adivinados.
    | Puede cambiar esto según sea necesario.
    |
    */

    'passwords' => [
        'users' => [
            'provider' => 'users',
            'table' => 'password_resets',
            'expire' => 60,
        ],
    ],

];
