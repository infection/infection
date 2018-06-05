<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Process\Coverage;

use Composer\XdebugHandler\XdebugHandler;

/**
 * @internal
 */
final class CoverageChecker
{
    /**
     * @var bool
     */
    private $skipCoverage;

    /**
     * @var string
     */
    private $initialTestPhpOptions;

    public function __construct(bool $skipCoverage, string $initialTestPhpOptions)
    {
        $this->skipCoverage = $skipCoverage;
        $this->initialTestPhpOptions = $initialTestPhpOptions;
    }

    public function hasDebuggerOrCoverageOption(): bool
    {
        return $this->skipCoverage
            || \PHP_SAPI === 'phpdbg'
            || \extension_loaded('xdebug')
            || XdebugHandler::getSkippedVersion()
            || $this->isXdebugIncludedInInitialTestPhpOptions();
    }

    private function isXdebugIncludedInInitialTestPhpOptions(): bool
    {
        return (bool) preg_match(
            '/(zend_extension\s*=.*xdebug.*)/mi',
            $this->initialTestPhpOptions
        );
    }
}
