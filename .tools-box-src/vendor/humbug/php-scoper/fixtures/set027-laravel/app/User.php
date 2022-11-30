<?php

namespace _HumbugBoxb47773b41c19\App;

use _HumbugBoxb47773b41c19\Illuminate\Notifications\Notifiable;
use _HumbugBoxb47773b41c19\Illuminate\Foundation\Auth\User as Authenticatable;
class User extends Authenticatable
{
    use Notifiable;
    protected $fillable = ['name', 'email', 'password'];
    protected $hidden = ['password', 'remember_token'];
}
