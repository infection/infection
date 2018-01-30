<?php
/**
 * Copyright © 2018 Tobias Stadler
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\TestFramework\Codeception;

use Codeception\Events;
use Codeception\Extension;

class CustomAutoloadFilePath extends Extension
{
    public static $events = [
        Events::MODULE_INIT => 'autoloadFilePath',
    ];

    public function autoloadFilePath()
    {
        $customAutoloadFilePath = getenv('CUSTOM_AUTOLOAD_FILE_PATH');
        if ($customAutoloadFilePath !== null) {
            require_once $customAutoloadFilePath;
        }
    }
}
