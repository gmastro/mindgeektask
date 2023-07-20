<?php

namespace App\Console;

use App\Jobs\ProcessRemoteFeeds;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Stringable;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        $schedule
            ->exec("truncate -s 0 ./storage/logs/laravel.log")
            ->name("scheduler.truncate.log.hourly")
            ->hourlyAt(11);
        $schedule
            ->exec("./vendor/bin/sail artisan queue:prune-failed --hours=0")
            ->name("scheduler.prune.failed.jobs.hourly")
            ->hourly();
        $schedule
            ->exec("./vendor/bin/sail artisan queue:prune-batches --hours=0")
            ->name("scheduler.prune.failed.batch.jobs.hourly")
            ->hourly();
        $schedule
            ->job(ProcessRemoteFeeds::class, "scheduler", "redis")
            ->name('process.remote.feeds.job.daily.at.21.00')
            ->withoutOverlapping(180)
            ->before(fn () => info("Starting scheduler for all ACTIVE remote feed jobs"))
            ->after(fn (Stringable $output) => info("Started scheduler for ACTIVE remote feed jobs", ['output' => $output]))
            ->onSuccess(fn (Stringable $output) => info("Completed scheduler for ACTIVE remote feed jobs", ['output' => $output]))
            ->onFailure(fn (Stringable $output) => info("Remote feed jobs scheduler failed", ['output' => $output]))
            ->dailyAt("21:18:51");
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
