<?php

namespace _HumbugBoxb47773b41c19\App\Providers;

use _HumbugBoxb47773b41c19\Illuminate\Support\Facades\Gate;
use _HumbugBoxb47773b41c19\Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
class AuthServiceProvider extends ServiceProvider
{
    protected $policies = ['_HumbugBoxb47773b41c19\\App\\Model' => '_HumbugBoxb47773b41c19\\App\\Policies\\ModelPolicy'];
    public function boot()
    {
        $this->registerPolicies();
    }
}
