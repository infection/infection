<?php

namespace _HumbugBoxb47773b41c19\App\Http\Controllers\Auth;

use _HumbugBoxb47773b41c19\App\Http\Controllers\Controller;
use _HumbugBoxb47773b41c19\Illuminate\Foundation\Auth\SendsPasswordResetEmails;
class ForgotPasswordController extends Controller
{
    use SendsPasswordResetEmails;
    public function __construct()
    {
        $this->middleware('guest');
    }
}
