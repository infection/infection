<?php

namespace _HumbugBoxb47773b41c19\App\Http\Middleware;

use _HumbugBoxb47773b41c19\Illuminate\Foundation\Http\Middleware\TrimStrings as Middleware;
class TrimStrings extends Middleware
{
    protected $except = ['password', 'password_confirmation'];
}
