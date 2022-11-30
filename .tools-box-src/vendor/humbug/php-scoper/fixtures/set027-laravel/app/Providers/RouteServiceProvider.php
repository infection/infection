<?php

namespace _HumbugBoxb47773b41c19\App\Providers;

use _HumbugBoxb47773b41c19\Illuminate\Support\Facades\Route;
use _HumbugBoxb47773b41c19\Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
class RouteServiceProvider extends ServiceProvider
{
    protected $namespace = '_HumbugBoxb47773b41c19\\App\\Http\\Controllers';
    public function boot()
    {
        parent::boot();
    }
    public function map()
    {
        $this->mapApiRoutes();
        $this->mapWebRoutes();
    }
    protected function mapWebRoutes()
    {
        Route::middleware('web')->namespace($this->namespace)->group(base_path('routes/web.php'));
    }
    protected function mapApiRoutes()
    {
        Route::prefix('api')->middleware('api')->namespace($this->namespace)->group(base_path('routes/api.php'));
    }
}
