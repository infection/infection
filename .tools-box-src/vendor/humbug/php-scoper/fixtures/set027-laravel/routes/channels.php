<?php

namespace _HumbugBoxb47773b41c19;

use _HumbugBoxb47773b41c19\Illuminate\Support\Facades\Broadcast;
Broadcast::channel('App.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});
