<?php

namespace _HumbugBoxb47773b41c19\App\Providers;

use _HumbugBoxb47773b41c19\Illuminate\Support\ServiceProvider;
use _HumbugBoxb47773b41c19\Illuminate\Support\Facades\Broadcast;
class BroadcastServiceProvider extends ServiceProvider
{
    public function boot()
    {
        Broadcast::routes();
        require base_path('routes/channels.php');
    }
}
