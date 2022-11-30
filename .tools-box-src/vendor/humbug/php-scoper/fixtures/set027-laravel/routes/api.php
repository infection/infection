<?php

namespace _HumbugBoxb47773b41c19;

use _HumbugBoxb47773b41c19\Illuminate\Http\Request;
use _HumbugBoxb47773b41c19\Illuminate\Support\Facades\Route;
Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});
