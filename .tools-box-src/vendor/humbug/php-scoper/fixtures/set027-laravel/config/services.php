<?php

namespace _HumbugBoxb47773b41c19;

return ['mailgun' => ['domain' => env('MAILGUN_DOMAIN'), 'secret' => env('MAILGUN_SECRET')], 'ses' => ['key' => env('SES_KEY'), 'secret' => env('SES_SECRET'), 'region' => env('SES_REGION', 'us-east-1')], 'sparkpost' => ['secret' => env('SPARKPOST_SECRET')], 'stripe' => ['model' => App\User::class, 'key' => env('STRIPE_KEY'), 'secret' => env('STRIPE_SECRET')]];
