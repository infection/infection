<?php

namespace _HumbugBoxb47773b41c19;

use _HumbugBoxb47773b41c19\Illuminate\Foundation\Inspiring;
use _HumbugBoxb47773b41c19\Illuminate\Support\Facades\Artisan;
Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->describe('Display an inspiring quote');
