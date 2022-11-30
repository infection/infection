<?php

namespace _HumbugBoxb47773b41c19\App\Http\Middleware;

use _HumbugBoxb47773b41c19\Illuminate\Http\Request;
use _HumbugBoxb47773b41c19\Fideloper\Proxy\TrustProxies as Middleware;
class TrustProxies extends Middleware
{
    protected $proxies;
    protected $headers = Request::HEADER_X_FORWARDED_ALL;
}
