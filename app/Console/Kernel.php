<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

/**
 * @class Kernel
 * @brief Gestiona los comandos de consola a ser ejecutados por la aplicación
 *
 * Gestiona los comandos de consola a ser ejecutados por la aplicación
 */
class Kernel extends ConsoleKernel
{
    /**
     * Los comandos de artisan proporcionados por la aplicación.
     *
     * @var array $commands
     */
    protected $commands = [
        //
    ];

    /**
     * Define la planificación en la ejecución de comandos de la aplicación.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     *
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        //
    }

    /**
     * Registra los comandos para la aplicación.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
