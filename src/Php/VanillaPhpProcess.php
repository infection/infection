<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Php;

use Symfony\Component\Process\Process;

/**
 * @internal
 */
final class VanillaPhpProcess extends Process
{
    public function start(callable $callback = null, array $env = null)
    {
        // Save previous state (ConfigBuilder ensures these two will always be present)
        $previousState = [
            ConfigBuilder::ENV_PHP_INI_SCAN_DIR => getenv(ConfigBuilder::ENV_PHP_INI_SCAN_DIR),
            ConfigBuilder::ENV_PHPRC => getenv(ConfigBuilder::ENV_PHPRC),
        ];

        // No previous state - no problem; starting it as it is
        if (!array_filter($previousState)) {
            parent::start($callback, $env);

            return;
        }

        // Remove directions to use our custom php.ini without xdebug
        // parent::start() will always inherit everything from $_ENV, and checks for actual environment
        unset($_ENV[ConfigBuilder::ENV_PHP_INI_SCAN_DIR]);
        unset($_ENV[ConfigBuilder::ENV_PHPRC]);
        putenv(ConfigBuilder::ENV_PHP_INI_SCAN_DIR);
        putenv(ConfigBuilder::ENV_PHPRC);

        parent::start($callback, $env);

        // Restore previous state
        foreach ($previousState as $key => $value) {
            // We have to set $_SERVER because it is used as a fallback for $_ENV
            // E.g. by parent::start() because $_ENV can be non-initialized
            $_SERVER[$key] = $value;
            $_ENV[$key] = $value;
            putenv($key . '=' . $value);
        }
    }
}
