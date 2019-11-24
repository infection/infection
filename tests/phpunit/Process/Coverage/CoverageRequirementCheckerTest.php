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

namespace Infection\Tests\Process\Coverage;

use Infection\Process\Coverage\CoverageRequirementChecker;
use PHPUnit\Framework\TestCase;

/**
 * All these tests should be ran in separate processes, as otherwise they may rely
 * on the internal state of XdebugHandler.
 *
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class CoverageRequirementCheckerTest extends TestCase
{
    public function test_it_has_debugger_or_coverage_option_on_phpdbg(): void
    {
        $this->requirePhpDbg();
        $this->requireNoXdebug();
        $this->requireNoPcov();

        $coverageChecker = new CoverageRequirementChecker(false, '');

        $this->assertTrue($coverageChecker->hasDebuggerOrCoverageOption());
    }

    public function test_it_has_debugger_or_coverage_option_on_pcov(): void
    {
        $this->requireNoPhpDbg();
        $this->requireNoXdebug();
        $this->requirePcov();

        $coverageChecker = new CoverageRequirementChecker(false, '');

        $this->assertTrue($coverageChecker->hasDebuggerOrCoverageOption());
    }

    public function test_it_has_debugger_or_coverage_option_with_xdebug(): void
    {
        $this->requireNoPhpDbg();
        $this->requireNoPcov();
        $this->requireXdebug();

        $coverageChecker = new CoverageRequirementChecker(false, '');

        $this->assertTrue($coverageChecker->hasDebuggerOrCoverageOption());
    }

    public function test_it_has_debug_or_coverage_option_when_provided_with_coverage(): void
    {
        $this->requireNoPhpDbg();
        $this->requireNoXdebug();
        $this->requireNoPcov();

        $coverageChecker = new CoverageRequirementChecker(true, '');

        $this->assertTrue($coverageChecker->hasDebuggerOrCoverageOption());
    }

    public function test_it_has_debug_or_coverage_option_when_provided_with_correct_xdebug_initial_php_settings(): void
    {
        $this->requireNoPhpDbg();
        $this->requireNoXdebug();
        $this->requireNoPcov();

        $coverageChecker = new CoverageRequirementChecker(false, '-d zend_extension=xdebug.so');

        $this->assertTrue($coverageChecker->hasDebuggerOrCoverageOption());
    }

    public function test_it_has_debug_or_coverage_option_when_provided_with_correct_pcov_initial_php_settings(): void
    {
        $this->requireNoPhpDbg();
        $this->requireNoXdebug();
        $this->requireNoPcov();

        $coverageChecker = new CoverageRequirementChecker(false, '-d extension=pcov.so');

        $this->assertTrue($coverageChecker->hasDebuggerOrCoverageOption());
    }

    public function test_it_has_no_debug_or_coverage_option_when_provided_with_incorrect_initial_php_settings(): void
    {
        $this->requireNoPhpDbg();
        $this->requireNoXdebug();
        $this->requireNoPcov();

        $coverageChecker = new CoverageRequirementChecker(false, '--help');

        $this->assertFalse($coverageChecker->hasDebuggerOrCoverageOption());
    }

    private function requirePhpDbg(): void
    {
        if (\PHP_SAPI !== 'phpdbg') {
            $this->markTestSkipped('Test requires phpdbg to run.');
        }
    }

    private function requireNoPhpDbg(): void
    {
        if (\PHP_SAPI === 'phpdbg') {
            $this->markTestSkipped('Test requires phpdbg to be disabled to run.');
        }
    }

    private function requireXdebug(): void
    {
        if (!\extension_loaded('xdebug')) {
            $this->markTestSkipped('Test requires xdebug to run.');
        }
    }

    private function requireNoXdebug(): void
    {
        if (\extension_loaded('xdebug')) {
            $this->markTestSkipped('Test requires xdebug to be disabled to run.');
        }
    }

    private function requirePcov(): void
    {
        if (!\extension_loaded('pcov')) {
            $this->markTestSkipped('Test requires pcov to run.');
        }
    }

    private function requireNoPcov(): void
    {
        if (\extension_loaded('pcov')) {
            $this->markTestSkipped('Test requires pcov to be disabled to run.');
        }
    }
}
