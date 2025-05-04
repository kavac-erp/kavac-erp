<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Configuración de la barra de depuración
    |--------------------------------------------------------------------------
    |
    | La barra de depuración está habilitada de forma predeterminada, cuando la
    | depuración se establece en verdadero en app.php. Puede anular el valor
    | configurando enable en verdadero o falso en lugar de nulo.
    |
    | Puede proporcionar una matriz de URI que deben ignorarse
    | (por ejemplo, 'api/*')
    |
    */

    'enabled' => env('DEBUGBAR_ENABLED', null),
    'except' => [
        'telescope*'
    ],

    /*
    |--------------------------------------------------------------------------
    | Configuración de almacenamiento
    |--------------------------------------------------------------------------
    |
    | DebugBar almacena datos para solicitudes de sesión/ajax.
    | Puede deshabilitar esto, para que la barra de depuración almacene datos
    | en encabezados/sesión, pero esto puede causar problemas con grandes
    | recopiladores de datos. De forma predeterminada, se utiliza el
    | almacenamiento de archivos (en la carpeta de almacenamiento).
    | También se pueden utilizar Redis y PDO. Para PDO, ejecute primero las
    | migraciones de paquetes.
    |
    */
    'storage' => [
        'enabled'    => true,
        'driver'     => 'file', // redis, file, pdo, custom
        'path'       => storage_path('debugbar'), // Para controlador file
        'connection' => null,   // Deje nulo para la conexión predeterminada (Redis/PDO)
        'provider'   => '' // Instancia de StorageInterface para controlador personalizado
    ],

    /*
    |--------------------------------------------------------------------------
    | Vendors
    |--------------------------------------------------------------------------
    |
    | Los archivos vendors se incluyen de forma predeterminada, pero se pueden
    | configurar como falsos. Esto también se puede configurar en 'js' o 'css',
    | para incluir solo archivos vendors javascript o css. Los archivos vendors
    | para css son: font-awesome (incluidas las fuentes) y
    | resaltar.js (archivos css) y para js son: jquery y resaltar.js Entonces,
    | si desea resaltar la sintaxis, configúrelo en verdadero. jQuery está
    | configurado para no entrar en conflicto con los scripts jQuery existentes.
    |
    */

    'include_vendors' => true,

    /*
    |--------------------------------------------------------------------------
    | Capturar solicitudes Ajax
    |--------------------------------------------------------------------------
    |
    | La barra de depuración puede capturar solicitudes de Ajax y mostrarlas.
    | Si no desea esto (es decir, debido a errores), puede usar esta opción para
    | deshabilitar el envío de datos a través de los encabezados.
    |
    | Opcionalmente, también puede enviar encabezados ServerTiming en solicitudes
    | ajax para Chrome DevTools.
    */

    'capture_ajax' => true,
    'add_ajax_timing' => true,

    /*
    |--------------------------------------------------------------------------
    | Controlador de errores personalizado para advertencias obsoletas
    |--------------------------------------------------------------------------
    |
    | Cuando está habilitada, la barra de depuración muestra advertencias obsoletas
    | para los componentes de Symfony en la pestaña Mensajes.
    |
    */
    'error_handler' => false,

    /*
    |--------------------------------------------------------------------------
    | Integración de Clockwork
    |--------------------------------------------------------------------------
    |
    | La barra de depuración puede emular los encabezados de Clockwork, por lo que
    | puede usar la extensión de Chrome, sin el código del lado del servidor.
    | En su lugar, utiliza recopiladores Debugbar.
    |
    */
    'clockwork' => false,

    /*
    |--------------------------------------------------------------------------
    | Colectores de datos
    |--------------------------------------------------------------------------
    |
    | Habilitar/deshabilitar recopiladores de datos
    |
    */

    'collectors' => [
        'phpinfo'         => true,  // versión php
        'messages'        => true,  // Mensajes
        'time'            => true,  // Registrador de datos de tiempo
        'memory'          => true,  // Uso de memoria
        'exceptions'      => true,  // visualizador de excepciones
        'log'             => true,  // Registros de eventos (fusionados en mensajes si están habilitados)
        'db'              => true,  // Mostrar consultas y enlaces de base de datos (PDO)
        'views'           => true,  // Vistas con sus datos
        'route'           => true,  // Información de la ruta actual
        'auth'            => true, // Mostrar el estado de autenticación de Laravel
        'gate'            => true, // Mostrar comprobaciones de Laravel Gate
        'session'         => true,  // Mostrar datos de la sesión
        'symfony_request' => true,  // Solo se puede habilitar uno.
        'mail'            => true,  // Capturar mensajes de correo
        'laravel'         => true, // Versión y entorno de Laravel
        'events'          => true, // Todos los eventos disparados
        'default_request' => false, // Registrador de solicitudes de Symfony normal o especial
        'logs'            => true, // Agregar los últimos mensajes de registro
        'files'           => false, // Mostrar los archivos incluidos
        'config'          => true, // Ajustes de configuración de pantalla
        'cache'           => false, // Mostrar eventos de caché
    ],

    /*
    |--------------------------------------------------------------------------
    | Opciones adicionales
    |--------------------------------------------------------------------------
    |
    | Configurar algunos recopiladores de datos
    |
    */

    'options' => [
        'auth' => [
            'show_name' => true,   // También muestra el nombre/correo electrónico de los usuarios en la barra de depuración
        ],
        'db' => [
            'with_params'       => true,   // Renderizar SQL con los parámetros sustituidos
            'backtrace'         => true,   // Utilice un backtrace para encontrar el origen de la consulta en sus archivos.
            'timeline'          => true,  // Agregar las consultas a la línea de tiempo
            'explain' => [                 // Mostrar salida EXPLAIN en consultas
                'enabled' => false,
                'types' => ['SELECT'],     // solución alternativa ['SELECT'] solamente. https://github.com/barryvdh/laravel-debugbar/issues/888 ['SELECT', 'INSERT', 'UPDATE', 'DELETE']; para MySQL 5.6.3+
            ],
            'hints'             => true,    // Mostrar sugerencias para errores comunes
        ],
        'mail' => [
            'full_log' => false
        ],
        'views' => [
            'data' => false,    //Nota: Puede ralentizar la aplicación porque los datos pueden ser bastante grandes.
        ],
        'route' => [
            'label' => true  // mostrar ruta completa en la barra
        ],
        'logs' => [
            'file' => null
        ],
        'cache' => [
            'values' => true // recopilar valores de caché
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Inyectar barra de depuración en respuesta
    |--------------------------------------------------------------------------
    |
    | Por lo general, la barra de depuración se agrega justo antes de </body>,
    | al escuchar la respuesta después de que finaliza la aplicación.
    | Si deshabilita esto, debe agregarlos en la plantilla usted mismo.
    | Ver http://phpdebugbar.com/docs/rendering.html
    |
    */

    'inject' => true,

    /*
    |--------------------------------------------------------------------------
    | Prefijo de ruta DebugBar
    |--------------------------------------------------------------------------
    |
    | A veces, desea configurar el prefijo de ruta para que DebugBar lo use para
    | cargar sus recursos. Por lo general, la necesidad proviene de un servidor
    | web mal configurado o de tratar de superar errores como este:
    | http://trac.nginx.org/nginx/ticket/97
    |
    */
    'route_prefix' => '_debugbar',

    /*
    |--------------------------------------------------------------------------
    | Dominio de ruta de DebugBar
    |--------------------------------------------------------------------------
    |
    | De forma predeterminada, la ruta DebugBar se sirve desde el mismo dominio
    | que atendió la solicitud. Para anular el dominio predeterminado,
    | especifíquelo como un valor no vacío.
    */
    'route_domain' => null,
];
