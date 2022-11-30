<?php

namespace _HumbugBoxb47773b41c19\App\Http\Middleware;

use _HumbugBoxb47773b41c19\Illuminate\Cookie\Middleware\EncryptCookies as Middleware;
class EncryptCookies extends Middleware
{
    protected $except = [];
}
