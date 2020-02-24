<?php

declare(strict_types=1);

return [
    'whitelist' => [
        \Composer\Autoload\ClassLoader::class,
        'Safe\*',
    ],
    'whitelist-global-constants' => false,
    'whitelist-global-classes' => false,
    'whitelist-global-functions' => false,
];