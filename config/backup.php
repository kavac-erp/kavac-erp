<?php

/**
 * Establece si se envian notificaciones por correo electrónico cuando se respalda la aplicación.
 *
 * @var array
 */
$mail = (config('mail.username') !== null) ? ['mail'] : [];

return [

    'backup' => [

        'enabled' => env('BACKUP_ENABLED', false),

        /**
         * El nombre de esta aplicación. Puede usar este nombre para monitorear las copias de seguridad.
         */
        'name' => config('app.name'),

        'source' => [

            'files' => [

                /**
                 * La lista de directorios y archivos que se incluirán en la copia de seguridad.
                 */
                'include' => [
                    //base_path(),
                ],

                /**
                 * Estos directorios y archivos se excluirán de la copia de seguridad.
                 *
                 * Los directorios utilizados por el proceso de copia de seguridad se excluirán automáticamente.
                 */
                'exclude' => [
                    base_path('vendor'),
                    base_path('node_modules'),
                ],

                /**
                 * Determina si se deben seguir los enlaces simbólicos.
                 */
                'followLinks' => false,
            ],

            /**
             * Los nombres de las conexiones a las bases de datos que se deben respaldar.
             * Son soportadas las base de datos MySQL, PostgreSQL, SQLite y Mongo.
             */
            'databases' => [
                env('DB_CONNECTION'),
            ],
        ],

        /**
         * El volcado de la base de datos se puede comprimir para disminuir el uso del espacio en disco.
         */
        'gzip_database_dump' => false,

        'destination' => [

            /**
             * El prefijo del nombre de archivo utilizado para el archivo zip de copia de seguridad.
             */
            'filename_prefix' => '',

            /**
             * Los nombres de los discos en los que se almacenarán las copias de seguridad.
             */
            'disks' => [
                'snapshots',
            ],
        ],
    ],

    /**
     * Puede recibir notificaciones cuando ocurran eventos específicos. Fuera de la caja, puede usar 'correo' y 'slack'.
     * Para Slack necesitas instalar guzzlehttp/guzzle.
     *
     * También puede usar sus propias clases de notificación, solo asegúrese de que la clase tenga el nombre de una de las clases
     * `Spatie\Backup\Events`.
     */
    'notifications' => [

        'notifications' => [
            \Spatie\Backup\Notifications\Notifications\BackupHasFailed::class         => $mail,
            \Spatie\Backup\Notifications\Notifications\UnhealthyBackupWasFound::class => $mail,
            \Spatie\Backup\Notifications\Notifications\CleanupHasFailed::class        => $mail,
            \Spatie\Backup\Notifications\Notifications\BackupWasSuccessful::class     => $mail,
            \Spatie\Backup\Notifications\Notifications\HealthyBackupWasFound::class   => $mail,
            \Spatie\Backup\Notifications\Notifications\CleanupWasSuccessful::class    => $mail,
        ],

        /**
         * Aquí puede especificar el notificable al que se deben enviar las notificaciones.
         * El notificable predeterminado utilizará las variables especificadas en este archivo de configuración.
         */
        'notifiable' => \Spatie\Backup\Notifications\Notifiable::class,

        'mail' => [
            'to' => config('mail.from.address'),
        ],

        'slack' => [
            'webhook_url' => '',

            /**
             * Si se establece en nulo, se utilizará el canal predeterminado del webhook.
             */
            'channel' => null,

            'username' => null,

            'icon' => null,

        ],
    ],

    /**
     * Aquí puede especificar qué copias de seguridad deben monitorearse.
     * Si una copia de seguridad no cumple con los requisitos especificados,
     * se activará el evento UnHealthyBackupWasFound.
     */
    'monitorBackups' => [
        [
            'name' => config('app.name'),
            'disks' => ['local'],
            'newestBackupsShouldNotBeOlderThanDays' => 1,
            'storageUsedMayNotBeHigherThanMegabytes' => 5000,
        ],
    ],

    'cleanup' => [
        /**
         * La estrategia que se utilizará para limpiar las copias de seguridad antiguas.
         * La estrategia predeterminada mantendrá todas las copias de seguridad durante una cierta cantidad de días.
         * Después de ese período solo se mantendrá una copia de seguridad diaria.
         * Después de ese período solo se mantendrán copias de seguridad semanales y así sucesivamente.
         *
         * No importa cómo lo configure, la estrategia predeterminada nunca eliminará la copia de seguridad más reciente.
         */
        'strategy' => \Spatie\Backup\Tasks\Cleanup\Strategies\DefaultStrategy::class,

        'defaultStrategy' => [

            /**
             * El número de días durante los cuales se deben conservar las copias de seguridad.
             */
            'keepAllBackupsForDays' => 7,

            /**
             * El número de días durante los cuales se deben conservar las copias de seguridad diarias.
             */
            'keepDailyBackupsForDays' => 16,

            /**
             * El número de semanas durante las cuales se debe conservar una copia de seguridad semanal.
             */
            'keepWeeklyBackupsForWeeks' => 8,

            /**
             * El número de meses durante los cuales se debe mantener una copia de seguridad mensual.
             */
            'keepMonthlyBackupsForMonths' => 4,

            /**
             * El número de años durante los cuales se debe mantener una copia de seguridad anual.
             */
            'keepYearlyBackupsForYears' => 2,

            /**
             * Después de limpiar las copias de seguridad, elimine la copia de seguridad más antigua hasta alcanzar
             * esta cantidad de megabytes.
             */
            'deleteOldestBackupsWhenUsingMoreMegabytesThan' => 5000,
        ],
    ],
];
