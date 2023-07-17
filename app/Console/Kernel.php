<?php

namespace App\Console;

use App\Mail\RecapEmail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // php artisan schedule:work --> Corre el comando
        // $schedule->command('inspire')->hourly();
        $schedule->call(function(){
            Mail::to('felipecabezas98@gmail.com')->send(new RecapEmail());
        })->everyMinute();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
