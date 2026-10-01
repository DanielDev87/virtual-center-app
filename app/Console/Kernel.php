<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Definir el horario de comandos de la aplicación.
     */
    protected function schedule(Schedule $schedule): void
    {
        $schedule->command('tickets:notify-overdue')->hourly()->withoutOverlapping();
    }

    /**
     * Registrar los comandos de la aplicación.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}


