<?php

namespace _HumbugBoxb47773b41c19;

return ['default' => env('BROADCAST_DRIVER', 'null'), 'connections' => ['pusher' => ['driver' => 'pusher', 'key' => env('PUSHER_APP_KEY'), 'secret' => env('PUSHER_APP_SECRET'), 'app_id' => env('PUSHER_APP_ID'), 'options' => ['cluster' => env('PUSHER_APP_CLUSTER'), 'encrypted' => \true]], 'redis' => ['driver' => 'redis', 'connection' => 'default'], 'log' => ['driver' => 'log'], 'null' => ['driver' => 'null']]];
