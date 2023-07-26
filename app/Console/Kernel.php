<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
//    /**
//     * The Artisan commands provided by your application.
//     *
//     * @var array
//     */
//    protected array $commands = [
//        UpdatePromFileCommand::class,
//    ];

    /**
     * Define the application's command schedule.
     *
     * @param Schedule $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('app:download_royal')->cron('*/22 * * * *');
        $schedule->command('app:download_image_royal')->cron('1 1 * * *');
//        $schedule->command('app:export origami')->cron('*/5 * * * *');
//        $schedule->command('app:export royal')->cron('*/5 * * * *');
//        $schedule->command('app:auto-ask')->cron('* * * * *');
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
