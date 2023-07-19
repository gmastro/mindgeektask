<?php

namespace App\Console;

use App\Jobs\ProcessRemoteFeeds;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // $schedule->command('inspire')->hourly();
        $schedule
            ->job(new ProcessRemoteFeeds())
            ->name('process.remote.feeds.job.daily.at.21.00')
            ->withoutOverlapping(180)
            ->daily()
            ->at("21:00:00");
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
