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

namespace Infection\TestFramework\PhpUnit\Coverage;

use Composer\XdebugHandler\XdebugHandler;
use function extension_loaded;
use Infection\TestFramework\Coverage\CoverageNotFound;
use function ini_get as ini_get_unsafe;
use const PHP_SAPI;
use function Safe\preg_match;

/** @internal */
final readonly class CoverageRequirementsChecker
{
    public function __construct(
        private bool $skipCoverage,
        private bool $skipInitialTests,
        private string $initialTestsPhpOptions,
    ) {
    }

    public function check(): void
    {
        if ($this->skipInitialTests && !$this->skipCoverage) {
            throw new CoverageNotFound(
                'The initial test suite run is being skipped. The XML and JUnit coverage reports need to be '
                . 'provided with the "--coverage" option',
            );
        }

        if ($this->skipCoverage || $this->hasCoverageGeneratorEnabled()) {
            return;
        }

        throw new CoverageNotFound(<<<TXT
            Coverage needs to be generated but no code coverage generator (pcov, phpdbg or xdebug) has been detected. Please either:
            - Enable pcov and run Infection again
            - Use phpdbg, e.g. `phpdbg -qrr infection`
            - Enable Xdebug (in case of using Xdebug 3 check that `xdebug.mode` or environment variable XDEBUG_MODE set to `coverage`) and run Infection again
            - Use the "--coverage" option with path to the existing coverage report
            - Enable the code generator tool for the initial test run only, e.g. with `--initial-tests-php-options -d zend_extension=xdebug.so`
            TXT
        );
    }

    private function hasCoverageGeneratorEnabled(): bool
    {
        return PHP_SAPI === 'phpdbg'
            || XdebugHandler::isXdebugActive()
            || extension_loaded('pcov')
            || XdebugHandler::getSkippedVersion() !== ''
            || ini_get_unsafe('xdebug.mode') !== false
            || preg_match('/(zend_extension\s*=.*xdebug.*)/mi', $this->initialTestsPhpOptions) === 1
            || preg_match('/(extension\s*=.*pcov.*)/mi', $this->initialTestsPhpOptions) === 1;
    }
}
