<?php

namespace _HumbugBoxb47773b41c19;

use _HumbugBoxb47773b41c19\Monolog\Handler\StreamHandler;
return ['default' => env('LOG_CHANNEL', 'stack'), 'channels' => ['stack' => ['driver' => 'stack', 'channels' => ['single']], 'single' => ['driver' => 'single', 'path' => storage_path('logs/laravel.log'), 'level' => 'debug'], 'daily' => ['driver' => 'daily', 'path' => storage_path('logs/laravel.log'), 'level' => 'debug', 'days' => 7], 'slack' => ['driver' => 'slack', 'url' => env('LOG_SLACK_WEBHOOK_URL'), 'username' => 'Laravel Log', 'emoji' => ':boom:', 'level' => 'critical'], 'stderr' => ['driver' => 'monolog', 'handler' => StreamHandler::class, 'with' => ['stream' => 'php://stderr']], 'syslog' => ['driver' => 'syslog', 'level' => 'debug'], 'errorlog' => ['driver' => 'errorlog', 'level' => 'debug']]];
