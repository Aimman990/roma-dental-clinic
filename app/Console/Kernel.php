<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Console\Commands\SendReminders;

class Kernel extends ConsoleKernel
{
    protected function schedule(Schedule $schedule): void
    {
        // run reminders every minute in demo (adjust to daily/hourly in production)
        $schedule->command('reminders:send')->everyMinute();
    }

    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');
    }
}
