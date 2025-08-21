<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // تذكير بعد 3 أيام من الإرسال بدون تأكيد الاستلام
        $schedule->command('workflow:remind-receipts')
            ->dailyAt('09:00')
            ->withoutOverlapping()
            ->onOneServer();

        // (اختياري) مهام صيانة أخرى:
        // $schedule->command('queue:work --stop-when-empty')->everyMinute()->withoutOverlapping();
        // $schedule->command('backup:run')->dailyAt('02:30');
        // $schedule->command('horizon:snapshot')->everyFiveMinutes();
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
