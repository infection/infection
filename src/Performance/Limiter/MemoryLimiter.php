<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Performance\Limiter;

use Composer\XdebugHandler\XdebugHandler;
use Infection\TestFramework\AbstractTestFrameworkAdapter;
use Infection\TestFramework\MemoryUsageAware;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Process;

/**
 * @internal
 */
final class MemoryLimiter
{
    /**
     * @var Filesystem
     */
    private $fs;

    /**
     * @var string|false
     */
    private $iniLocation;

    public function __construct(Filesystem $fs, $iniLocation)
    {
        $this->fs = $fs;
        $this->iniLocation = $iniLocation;
    }

    public function applyMemoryLimitFromProcess(Process $process, AbstractTestFrameworkAdapter $adapter): void
    {
        if (!$adapter instanceof MemoryUsageAware || $this->hasMemoryLimitSet() || $this->isUsingSystemIni()) {
            return;
        }

        $tempConfigPath = $this->iniLocation;

        if (empty($tempConfigPath) || !file_exists($tempConfigPath) || !is_writable($tempConfigPath)) {
            // Cannot add a memory limit: there is no php.ini file or it is not writable
            return;
        }

        $memoryLimit = $adapter->getMemoryUsed($process->getOutput());

        if ($memoryLimit < 0) {
            // Cannot detect memory used, not setting any limits
            return;
        }

        /*
         * Since we know how much memory the initial test suite used,
         * and only if we know, we can enforce a memory limit upon all
         * mutation processes. Limit is set to be twice the known amount,
         * because if we know that a normal test suite used X megabytes,
         * if a mutants uses a lot more, this is a definite error.
         *
         * By default we let a mutant process use twice as much more
         * memory as an initial test suite consumed.
         */
        $memoryLimit *= 2;
        $this->fs->appendToFile($tempConfigPath, PHP_EOL . sprintf('memory_limit = %dM', $memoryLimit));
    }

    private function hasMemoryLimitSet(): bool
    {
        // -1 means no memory limit. Anything else means the user has set their own limits, which we don't want to mess with
        return ini_get('memory_limit') !== '-1';
    }

    private function isUsingSystemIni(): bool
    {
        // Under phpdbg we're using a system php.ini, can't add a memory limit there
        // If there is no skipped version of xdebug handler we are also using the system php ini
        return \PHP_SAPI === 'phpdbg' || XdebugHandler::getSkippedVersion() === '';
    }
}
