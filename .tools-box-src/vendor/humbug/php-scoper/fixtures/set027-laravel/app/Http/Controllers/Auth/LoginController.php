<?php

namespace _HumbugBoxb47773b41c19\App\Http\Controllers\Auth;

use _HumbugBoxb47773b41c19\App\Http\Controllers\Controller;
use _HumbugBoxb47773b41c19\Illuminate\Foundation\Auth\AuthenticatesUsers;
class LoginController extends Controller
{
    use AuthenticatesUsers;
    protected $redirectTo = '/home';
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }
}
