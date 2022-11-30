<?php

namespace _HumbugBoxb47773b41c19\App\Http\Controllers\Auth;

use _HumbugBoxb47773b41c19\App\User;
use _HumbugBoxb47773b41c19\App\Http\Controllers\Controller;
use _HumbugBoxb47773b41c19\Illuminate\Support\Facades\Hash;
use _HumbugBoxb47773b41c19\Illuminate\Support\Facades\Validator;
use _HumbugBoxb47773b41c19\Illuminate\Foundation\Auth\RegistersUsers;
class RegisterController extends Controller
{
    use RegistersUsers;
    protected $redirectTo = '/home';
    public function __construct()
    {
        $this->middleware('guest');
    }
    protected function validator(array $data)
    {
        return Validator::make($data, ['name' => 'required|string|max:255', 'email' => 'required|string|email|max:255|unique:users', 'password' => 'required|string|min:6|confirmed']);
    }
    protected function create(array $data)
    {
        return User::create(['name' => $data['name'], 'email' => $data['email'], 'password' => Hash::make($data['password'])]);
    }
}
