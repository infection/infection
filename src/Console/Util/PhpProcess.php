<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Console\Util;

use Composer\XdebugHandler\XdebugHandler;
use Symfony\Component\Process\Process;

/**
 * Vanilla PHP process and utility functions
 *
 * @internal
 */
final class PhpProcess extends Process
{
    /**
     * @var string|bool
     */
    private static $phprc;

    /**
     * Setups a default Xdebug-free environment for all subprocesses:
     *
     * - PHPRC should point to our temporary php.ini (we need to save a previous value)
     *
     * - PHP_INI_SCAN_DIR should be made blank because our php.ini has all we need
     *   (a previous value can be found in XdebugHandler::getRestartSettings()["scanDir"])
     */
    public static function setupXdebugFreeEnvironment()
    {
        if (!isset(self::$phprc)) {
            self::$phprc = getenv('PHPRC');
        }

        self::putenv('PHPRC', XdebugHandler::getRestartSettings()['tmpIni']);
        self::putenv('PHP_INI_SCAN_DIR', '');
    }

    public function start(callable $callback = null, array $env = null)
    {
        // Xdebug wasn't skipped, running as is
        if ('' == XdebugHandler::getSkippedVersion()) {
            parent::start($callback, $env);

            return;
        }

        self::restoreVanillaEnvironment();

        parent::start($callback, $env);

        self::setupXdebugFreeEnvironment();
    }

    private static function restoreVanillaEnvironment()
    {
        self::putenv('PHPRC', self::$phprc);
        self::putenv('PHP_INI_SCAN_DIR', XdebugHandler::getRestartSettings()['scanDir']);
    }

    /**
     * @param string $name
     * @param string|bool $value either string or false
     */
    private static function putenv(string $name, $value)
    {
        // getenv returns false if there was no variable => we must delete it
        putenv(false === $value ? $name : $name . '=' . $value);

        // Our parent will read vars from $_SERVER
        $_SERVER[$name] = $value;

        // $_ENV is typically empty, but update it if not
        if (!empty($_ENV)) {
            $_ENV[$name] = $value;
        }
    }
}
