<?php

return [

    /**
     * El nombre del disco en el que se almacenan las instantáneas.
     */
    'disk' => 'snapshots',

    /**
     * La conexión que se utilizará para crear instantáneas.
     * Establézcalo en nulo para usar el valor predeterminado configurado en `config/databases.php`
     */
    'default_connection' => null,

    /**
     * El directorio donde se almacenarán los archivos temporales.
     */
    'temporary_directory_path' => storage_path('app/laravel-db-snapshots/temp'),

    /**
     * Crear archivos de volcado comprimidos con gzip
     */
    'compress' => false,
];
