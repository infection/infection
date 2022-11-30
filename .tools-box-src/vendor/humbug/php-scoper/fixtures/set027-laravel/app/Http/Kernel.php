<?php

namespace _HumbugBoxb47773b41c19\App\Http;

use _HumbugBoxb47773b41c19\Illuminate\Foundation\Http\Kernel as HttpKernel;
class Kernel extends HttpKernel
{
    protected $middleware = [\_HumbugBoxb47773b41c19\Illuminate\Foundation\Http\Middleware\CheckForMaintenanceMode::class, \_HumbugBoxb47773b41c19\Illuminate\Foundation\Http\Middleware\ValidatePostSize::class, \_HumbugBoxb47773b41c19\App\Http\Middleware\TrimStrings::class, \_HumbugBoxb47773b41c19\Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull::class, \_HumbugBoxb47773b41c19\App\Http\Middleware\TrustProxies::class];
    protected $middlewareGroups = ['web' => [\_HumbugBoxb47773b41c19\App\Http\Middleware\EncryptCookies::class, \_HumbugBoxb47773b41c19\Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class, \_HumbugBoxb47773b41c19\Illuminate\Session\Middleware\StartSession::class, \_HumbugBoxb47773b41c19\Illuminate\View\Middleware\ShareErrorsFromSession::class, \_HumbugBoxb47773b41c19\App\Http\Middleware\VerifyCsrfToken::class, \_HumbugBoxb47773b41c19\Illuminate\Routing\Middleware\SubstituteBindings::class], 'api' => ['throttle:60,1', 'bindings']];
    protected $routeMiddleware = ['auth' => \_HumbugBoxb47773b41c19\Illuminate\Auth\Middleware\Authenticate::class, 'auth.basic' => \_HumbugBoxb47773b41c19\Illuminate\Auth\Middleware\AuthenticateWithBasicAuth::class, 'bindings' => \_HumbugBoxb47773b41c19\Illuminate\Routing\Middleware\SubstituteBindings::class, 'cache.headers' => \_HumbugBoxb47773b41c19\Illuminate\Http\Middleware\SetCacheHeaders::class, 'can' => \_HumbugBoxb47773b41c19\Illuminate\Auth\Middleware\Authorize::class, 'guest' => \_HumbugBoxb47773b41c19\App\Http\Middleware\RedirectIfAuthenticated::class, 'signed' => \_HumbugBoxb47773b41c19\Illuminate\Routing\Middleware\ValidateSignature::class, 'throttle' => \_HumbugBoxb47773b41c19\Illuminate\Routing\Middleware\ThrottleRequests::class];
}
