<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        Commands\OcrPerformanceReport::class,
    ];

    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // OCR Performance Report - Daily at 8 AM
        $schedule->command('ocr:report')
            ->dailyAt('08:00')
            ->timezone('Europe/Paris')
            ->emailOutputOnFailure(User::where('is_admin', true)->pluck('email')->toArray());

        // Invoice Reminders
        $schedule->command('send:invoice-reminders')
            ->dailyAt('09:00')
            ->timezone('Europe/Paris');
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