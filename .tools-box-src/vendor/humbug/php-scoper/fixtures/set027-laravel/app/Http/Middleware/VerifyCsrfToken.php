<?php

namespace _HumbugBoxb47773b41c19\App\Http\Middleware;

use _HumbugBoxb47773b41c19\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;
class VerifyCsrfToken extends Middleware
{
    protected $except = [];
}
