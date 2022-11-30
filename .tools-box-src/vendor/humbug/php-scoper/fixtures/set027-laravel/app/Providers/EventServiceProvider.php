<?php

namespace _HumbugBoxb47773b41c19\App\Providers;

use _HumbugBoxb47773b41c19\Illuminate\Support\Facades\Event;
use _HumbugBoxb47773b41c19\Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
class EventServiceProvider extends ServiceProvider
{
    protected $listen = ['_HumbugBoxb47773b41c19\\App\\Events\\Event' => ['_HumbugBoxb47773b41c19\\App\\Listeners\\EventListener']];
    public function boot()
    {
        parent::boot();
    }
}
