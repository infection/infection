<?php

namespace _HumbugBoxb47773b41c19\App\Http\Controllers\Auth;

use _HumbugBoxb47773b41c19\App\Http\Controllers\Controller;
use _HumbugBoxb47773b41c19\Illuminate\Foundation\Auth\ResetsPasswords;
class ResetPasswordController extends Controller
{
    use ResetsPasswords;
    protected $redirectTo = '/home';
    public function __construct()
    {
        $this->middleware('guest');
    }
}
