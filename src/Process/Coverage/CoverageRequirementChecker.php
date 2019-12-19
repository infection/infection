<?php
/**
 * This code is licensed under the BSD 3-Clause License.
 *
 * Copyright (c) 2017, Maks Rafalko
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 * * Redistributions of source code must retain the above copyright notice, this
 *   list of conditions and the following disclaimer.
 *
 * * Redistributions in binary form must reproduce the above copyright notice,
 *   this list of conditions and the following disclaimer in the documentation
 *   and/or other materials provided with the distribution.
 *
 * * Neither the name of the copyright holder nor the names of its
 *   contributors may be used to endorse or promote products derived from
 *   this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE
 * FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL
 * DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR
 * SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY,
 * OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 */

declare(strict_types=1);

namespace Infection\Process\Coverage;

use Safe\preg_match;
use Composer\XdebugHandler\XdebugHandler;
use function extension_loaded;
use const PHP_SAPI;

/**
 * @internal
 */
final class CoverageRequirementChecker
{
    private $skipCoverage;
    private $initialTestPhpOptions;

    public function __construct(bool $skipCoverage, string $initialTestPhpOptions)
    {
        $this->skipCoverage = $skipCoverage;
        $this->initialTestPhpOptions = $initialTestPhpOptions;
    }

    public function hasDebuggerOrCoverageOption(): bool
    {
        return $this->skipCoverage
            || PHP_SAPI === 'phpdbg'
            || extension_loaded('xdebug')
            || extension_loaded('pcov')
            || XdebugHandler::getSkippedVersion()
            || $this->isXdebugIncludedInInitialTestPhpOptions()
            || $this->isPcovIncludedInInitialTestPhpOptions();
    }

    private function isXdebugIncludedInInitialTestPhpOptions(): bool
    {
        return (bool) preg_match(
            '/(zend_extension\s*=.*xdebug.*)/mi',
            $this->initialTestPhpOptions
        );
    }

    private function isPcovIncludedInInitialTestPhpOptions(): bool
    {
        return (bool) preg_match(
            '/(extension\s*=.*pcov.*)/mi',
            $this->initialTestPhpOptions
        );
    }
}
