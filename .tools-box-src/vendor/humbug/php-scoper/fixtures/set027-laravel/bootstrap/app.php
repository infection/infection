<?php

namespace _HumbugBoxb47773b41c19;

$app = new Illuminate\Foundation\Application(\realpath(__DIR__ . '/../'));
$app->singleton(Illuminate\Contracts\Http\Kernel::class, App\Http\Kernel::class);
$app->singleton(Illuminate\Contracts\Console\Kernel::class, App\Console\Kernel::class);
$app->singleton(Illuminate\Contracts\Debug\ExceptionHandler::class, App\Exceptions\Handler::class);
return $app;
