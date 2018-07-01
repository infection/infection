<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Tests\Process\Coverage;

use Infection\Process\Coverage\CoverageRequirementChecker;
use PHPUnit\Framework\TestCase;

/**
 * All these tests should be ran in separate processes, as otherwise they may rely
 * on the internal state of XdebugHandler.
 *
 * @internal
 *
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
final class CoverageRequirementCheckerTest extends TestCase
{
    public function test_it_has_debugger_or_coverage_option_on_phpdbg()
    {
        $this->requirePhpDbg();
        $this->requireNoXdebug();

        $coverageChecker = new CoverageRequirementChecker(false, '');

        $this->assertTrue($coverageChecker->hasDebuggerOrCoverageOption());
    }

    public function test_it_has_debugger_or_coverage_option_with_xdebug()
    {
        $this->requireNoPhpDbg();
        $this->requireXdebug();

        $coverageChecker = new CoverageRequirementChecker(false, '');

        $this->assertTrue($coverageChecker->hasDebuggerOrCoverageOption());
    }

    public function test_it_has_debug_or_coverage_option_when_provided_with_coverage()
    {
        $this->requireNoPhpDbg();
        $this->requireNoXdebug();

        $coverageChecker = new CoverageRequirementChecker(true, '');

        $this->assertTrue($coverageChecker->hasDebuggerOrCoverageOption());
    }

    public function test_it_has_debug_or_coverage_option_when_provided_with_correct_initial_php_settings()
    {
        $this->requireNoPhpDbg();
        $this->requireNoXdebug();

        $coverageChecker = new CoverageRequirementChecker(false, '-d zend_extension=xdebug.so');

        $this->assertTrue($coverageChecker->hasDebuggerOrCoverageOption());
    }

    public function test_it_has_no_debug_or_coverage_option_when_provided_with_incorrect_initial_php_settings()
    {
        $this->requireNoPhpDbg();
        $this->requireNoXdebug();

        $coverageChecker = new CoverageRequirementChecker(false, '--help');

        $this->assertFalse($coverageChecker->hasDebuggerOrCoverageOption());
    }

    private function requirePhpDbg()
    {
        if (\PHP_SAPI !== 'phpdbg') {
            $this->markTestSkipped('Test requires phpdbg to run.');
        }
    }

    private function requireNoPhpDbg()
    {
        if (\PHP_SAPI === 'phpdbg') {
            $this->markTestSkipped('Test requires phpdbg to be disabled to run.');
        }
    }

    private function requireXdebug()
    {
        if (!\extension_loaded('xdebug')) {
            $this->markTestSkipped('Test requires xdebug to run.');
        }
    }

    private function requireNoXdebug()
    {
        if (\extension_loaded('xdebug')) {
            $this->markTestSkipped('Test requires xdebug to be disabled to run.');
        }
    }
}
