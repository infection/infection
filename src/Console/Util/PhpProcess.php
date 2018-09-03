<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Console\Util;

use Composer\XdebugHandler\PhpConfig;
use Symfony\Component\Process\Process;

/**
 * @internal
 */
final class PhpProcess extends Process
{
    /**
     * Runs a PHP process with xdebug loaded
     *
     * If xdebug was loaded in the main process, it will have been restarted
     * without xdebug and configured to keep xdebug out of PHP sub-processes.
     *
     * This method allows a sub-process to run with xdebug enabled (if it was
     * originally loaded), then restores the xdebug-free environment.
     *
     * {@inheritdoc}
     */
    public function start(callable $callback = null, array $env = null): void
    {
        $phpConfig = new PhpConfig();

        $phpConfig->useOriginal();
        parent::start($callback, $env ?? []);
        $phpConfig->usePersistent();
    }
}
