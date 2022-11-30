<?php

namespace _HumbugBoxb47773b41c19\App\Console;

use _HumbugBoxb47773b41c19\Illuminate\Console\Scheduling\Schedule;
use _HumbugBoxb47773b41c19\Illuminate\Foundation\Console\Kernel as ConsoleKernel;
class Kernel extends ConsoleKernel
{
    protected $commands = [];
    protected function schedule(Schedule $schedule)
    {
    }
    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');
        require base_path('routes/console.php');
    }
}
