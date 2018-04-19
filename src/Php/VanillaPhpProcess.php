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
        // Save previous state
        $before = [
            ConfigBuilder::ENV_PHP_INI_SCAN_DIR => $_ENV[ConfigBuilder::ENV_PHP_INI_SCAN_DIR] ?? $_SERVER[ConfigBuilder::ENV_PHP_INI_SCAN_DIR],
            ConfigBuilder::ENV_PHPRC => $_ENV[ConfigBuilder::ENV_PHPRC] ?? $_SERVER[ConfigBuilder::ENV_PHPRC],
        ];

        // Remove directions to use our custom php.ini without xdebug
        unset($_ENV[ConfigBuilder::ENV_PHP_INI_SCAN_DIR]);
        unset($_ENV[ConfigBuilder::ENV_PHPRC]);
        unset($_SERVER[ConfigBuilder::ENV_PHP_INI_SCAN_DIR]);
        unset($_SERVER[ConfigBuilder::ENV_PHPRC]);
        putenv(ConfigBuilder::ENV_PHP_INI_SCAN_DIR);
        putenv(ConfigBuilder::ENV_PHPRC);

        // Process will always append $_ENV and $_SERVER (mostly) to the environment of new processes
        parent::start($callback, $env);

        // Restore previous state
        foreach ($before as $key => $value) {
            $_ENV[$key] = $value;
            $_SERVER[$key] = $value;
            putenv($key . '=' . $value);
        }
    }
}
