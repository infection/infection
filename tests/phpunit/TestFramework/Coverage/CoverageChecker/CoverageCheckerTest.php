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

namespace Infection\Tests\TestFramework\Coverage\CoverageChecker;

use function extension_loaded;
use Infection\TestFramework\Coverage\CoverageChecker;
use Infection\TestFramework\Coverage\CoverageNotFound;
use Infection\TestFramework\Coverage\Locator\FakeLocator;
use Infection\TestFramework\Coverage\Locator\FixedLocator;
use Infection\TestFramework\Coverage\Locator\ReportLocator;
use Infection\TestFramework\PhpUnit\Adapter\PhpUnitAdapter;
use Infection\Tests\TestFramework\Coverage\Locator\Throwable\UnknownReportLocatorException;
use const PHP_SAPI;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use function sprintf;
use Symfony\Component\Filesystem\Path;

/**
 * All these tests should be run in separate processes, as otherwise they may rely
 * on the internal state of XdebugHandler.
 *
 * Requires some I/O operations
 */
#[PreserveGlobalState(false)]
#[Group('integration')]
#[RunTestsInSeparateProcesses]
#[CoversClass(CoverageChecker::class)]
final class CoverageCheckerTest extends TestCase
{
    private const COVERAGE_DIR_PATH = __DIR__ . '/Fixtures';

    private const JUNIT_PATH = __DIR__ . '/Fixtures/junit.xml';

    public function test_it_needs_coverage_to_be_provided_if_initial_tests_are_skipped_without_junit_report(): void
    {
        $checker = new CoverageChecker(
            false,
            true,
            '',
            '',
            false,
            new FakeLocator(),
            'unknown',
            new FakeLocator(),
        );

        $this->expectException(CoverageNotFound::class);
        $this->expectExceptionMessage(
            'The initial test suite run is being skipped. The XML reports need to be provided with '
            . 'the "--coverage" option',
        );

        $checker->checkCoverageRequirements();
    }

    public function test_it_needs_coverage_to_be_provided_if_initial_tests_are_skipped_with_junit_report(): void
    {
        $checker = new CoverageChecker(
            false,
            true,
            '',
            '',
            true,
            new FixedLocator(self::JUNIT_PATH),
            'unknown',
            new FakeLocator(),
        );

        $this->expectException(CoverageNotFound::class);
        $this->expectExceptionMessage(
            'The initial test suite run is being skipped. The XML and JUnit coverage reports need '
            . 'to be provided with the "--coverage" option',
        );

        $checker->checkCoverageRequirements();
    }

    public function test_it_needs_code_coverage_generator_enabled_if_coverage_is_not_provided(): void
    {
        $this->requireNoPcov();
        $this->requireNoPhpDbg();
        $this->requireNoXdebug();

        $checker = new CoverageChecker(
            false,
            false,
            '',
            '',
            false,
            new FakeLocator(),
            'unknown',
            new FakeLocator(),
        );

        $this->expectException(CoverageNotFound::class);
        $this->expectExceptionMessage(<<<TXT
            Coverage needs to be generated but no code coverage generator (pcov, phpdbg or xdebug) has been detected. Please either:
            - Enable pcov and run Infection again
            - Use phpdbg, e.g. `phpdbg -qrr infection`
            - Enable Xdebug (in case of using Xdebug 3 check that `xdebug.mode` or environment variable XDEBUG_MODE set to `coverage`) and run Infection again
            - Use the "--coverage" option with path to the existing coverage report
            - Enable the code generator tool for the initial test run only, e.g. with `--initial-tests-php-options -d zend_extension=xdebug.so`
            TXT
        );

        $checker->checkCoverageRequirements();
    }

    public function test_it_passes_existence_check_if_xml_index_and_junit_files_are_found_with_junit_report(): void
    {
        $checker = new CoverageChecker(
            false,
            false,
            '',
            '/path/to/coverage-xml',
            true,
            new FixedLocator(self::JUNIT_PATH),
            'unknown',
            new FixedLocator(self::COVERAGE_DIR_PATH . '/index.xml'),
        );

        $checker->checkCoverageExists();

        $this->addToAssertionCount(1);
    }

    public function test_it_does_not_pass_existence_check_if_xml_index_is_missing_with_lambda_test_framework_adapter(): void
    {
        $checker = new CoverageChecker(
            false,
            false,
            '',
            '/nowhere',
            true,
            new FixedLocator(self::JUNIT_PATH),
            'unknown',
            $this->createInvalidIndexLocatorMock(),
        );

        try {
            $checker->checkCoverageExists();

            $this->fail();
        } catch (CoverageNotFound $exception) {
            $this->assertSame(
                'Could not find the "index.xml" file. Please ensure that the XML coverage '
                . 'report has been properly generated at the right place.',
                $exception->getMessage(),
            );
            $this->assertSame(0, $exception->getCode());
            $this->assertInstanceOf(UnknownReportLocatorException::class, $exception->getPrevious());
        }
    }

    public function test_it_does_not_pass_existence_check_if_xml_index_is_missing_with_phpunit(): void
    {
        $checker = new CoverageChecker(
            false,
            false,
            '',
            '/nowhere',
            true,
            new FixedLocator(self::JUNIT_PATH),
            'phpunit',
            $this->createInvalidIndexLocatorMock(),
        );

        try {
            $checker->checkCoverageExists();

            $this->fail();
        } catch (CoverageNotFound $exception) {
            $this->assertSame(
                'Could not find the "index.xml" file. Please ensure that the XML coverage '
                . 'report has been properly generated at the right place. The PHPUnit option for the '
                . 'path given is "--coverage-xml=/nowhere/coverage-xml"',
                $exception->getMessage(),
            );
            $this->assertSame(0, $exception->getCode());
            $this->assertInstanceOf(UnknownReportLocatorException::class, $exception->getPrevious());
        }
    }

    public function test_it_does_not_pass_existence_check_if_xml_index_is_missing_with_codeception(): void
    {
        $checker = new CoverageChecker(
            false,
            false,
            '',
            '/nowhere',
            true,
            new FixedLocator(self::JUNIT_PATH),
            'codeception',
            $this->createInvalidIndexLocatorMock(),
        );

        try {
            $checker->checkCoverageExists();

            $this->fail();
        } catch (CoverageNotFound $exception) {
            $this->assertSame(
                'Could not find the "index.xml" file. Please ensure that the XML coverage '
                . 'report has been properly generated at the right place. The Codeception option for the'
                . ' path given is "--coverage-phpunit=/nowhere/coverage-xml"',
                $exception->getMessage(),
            );
            $this->assertSame(0, $exception->getCode());
            $this->assertInstanceOf(UnknownReportLocatorException::class, $exception->getPrevious());
        }
    }

    public function test_it_passes_existence_check_if_xml_index_is_present_and_junit_file_is_missing_without_junit_report(): void
    {
        $checker = new CoverageChecker(
            false,
            false,
            '',
            self::COVERAGE_DIR_PATH,
            false,
            new FakeLocator(),
            'unknown',
            new FixedLocator(self::COVERAGE_DIR_PATH . '/index.xml'),
        );

        $checker->checkCoverageExists();

        $this->addToAssertionCount(1);
    }

    public function test_it_does_not_pass_existence_check_if_junit_file_is_missing_with_junit_report_with_lambda_test_framework_adapter(): void
    {
        $checker = new CoverageChecker(
            false,
            false,
            '',
            self::COVERAGE_DIR_PATH,
            true,
            $this->createInvalidJUnitReportLocatorMock(),
            'unknown',
            new FixedLocator(self::COVERAGE_DIR_PATH . '/index.xml'),
        );

        try {
            $checker->checkCoverageExists();

            $this->fail();
        } catch (CoverageNotFound $exception) {
            $this->assertSame(
                'Could not find the JUnit file report. Please ensure that the JUnit coverage '
                . 'report has been properly generated at the right place.',
                $exception->getMessage(),
            );
            $this->assertSame(0, $exception->getCode());
            $this->assertInstanceOf(UnknownReportLocatorException::class, $exception->getPrevious());
        }
    }

    public function test_it_does_not_pass_existence_check_if_junit_file_is_missing_with_junit_report_with_phpunit_test_framework_adapter(): void
    {
        $phpUnitAdapterMock = $this->createMock(PhpUnitAdapter::class);
        $phpUnitAdapterMock->method('hasJUnitReport')->willReturn(true);

        $checker = new CoverageChecker(
            false,
            false,
            '',
            self::COVERAGE_DIR_PATH,
            true,
            $this->createInvalidJUnitReportLocatorMock(),
            'phpunit',
            new FixedLocator(self::COVERAGE_DIR_PATH . '/index.xml'),
        );

        try {
            $checker->checkCoverageExists();

            $this->fail();
        } catch (CoverageNotFound $exception) {
            $this->assertSame(
                sprintf(
                    'Could not find the JUnit file report. Please ensure that the JUnit coverage '
                    . 'report has been properly generated at the right place. The PHPUnit option for the '
                    . 'path given is "--log-junit=%s/junit.xml"',
                    self::COVERAGE_DIR_PATH,
                ),
                $exception->getMessage(),
            );
            $this->assertSame(0, $exception->getCode());
            $this->assertInstanceOf(UnknownReportLocatorException::class, $exception->getPrevious());
        }
    }

    public function test_it_does_not_pass_existence_check_if_junit_file_is_missing_with_junit_report_with_codeception_test_framework_adapter(): void
    {
        $checker = new CoverageChecker(
            false,
            false,
            '',
            self::COVERAGE_DIR_PATH,
            true,
            $this->createInvalidJUnitReportLocatorMock(),
            'codeception',
            new FixedLocator(self::COVERAGE_DIR_PATH . '/index.xml'),
        );

        try {
            $checker->checkCoverageExists();

            $this->fail();
        } catch (CoverageNotFound $exception) {
            $this->assertSame(
                'Could not find the JUnit file report. Please ensure that the JUnit coverage report has'
                . ' been properly generated at the right place. The Codeception option for the path '
                . 'given is "--xml"',
                $exception->getMessage(),
            );
            $this->assertSame(0, $exception->getCode());
            $this->assertInstanceOf(UnknownReportLocatorException::class, $exception->getPrevious());
        }
    }

    public function test_it_passes_existence_check_if_xml_index_and_junit_files_are_found_after_tests_run_with_junit_report(): void
    {
        $checker = new CoverageChecker(
            false,
            false,
            '',
            self::COVERAGE_DIR_PATH,
            true,
            new FixedLocator(self::JUNIT_PATH),
            'unknown',
            new FixedLocator(self::COVERAGE_DIR_PATH . '/index.xml'),
        );

        $checker->checkCoverageHasBeenGenerated(
            'bin/phpunit --coverage-xml=coverage/coverage-xml --log-junit=coverage=junit.xml',
            'Ok!',
        );

        $this->addToAssertionCount(1);
    }

    public function test_it_passes_existence_check_if_xml_index_and_junit_files_are_found_after_tests_run_without_junit_report(): void
    {
        $checker = new CoverageChecker(
            false,
            false,
            '',
            self::COVERAGE_DIR_PATH,
            true,
            new FixedLocator(self::JUNIT_PATH),
            'unknown',
            new FixedLocator(self::COVERAGE_DIR_PATH . '/index.xml'),
        );

        $checker->checkCoverageHasBeenGenerated(
            'bin/phpunit --coverage-xml=coverage/coverage-xml --log-junit=coverage=junit.xml',
            'Ok!',
        );

        $this->addToAssertionCount(1);
    }

    public function test_it_does_not_pass_existence_check_if_xml_index_is_missing_after_tests_run(): void
    {
        $checker = new CoverageChecker(
            false,
            false,
            '',
            '/nowhere',
            true,
            new FixedLocator(self::JUNIT_PATH),
            'unknown',
            $this->createInvalidIndexLocatorMock(),
        );

        $this->expectException(CoverageNotFound::class);
        $this->expectExceptionMessage(<<<'TXT'
            The code coverage generated by the initial test run is invalid. Please report the issue on the
            infection repository "https://github.com/infection/infection".

            ```
            $ bin/phpunit --coverage-xml=coverage/coverage-xml --log-junit=coverage=junit.xml
            Ok!
            ```

            Issue(s):
            - The file "index.xml" could not be found: Could not locate the index.xml coverage report for some reasons!
            TXT
        );

        $checker->checkCoverageHasBeenGenerated(
            'bin/phpunit --coverage-xml=coverage/coverage-xml --log-junit=coverage=junit.xml',
            'Ok!',
        );
    }

    public function test_it_does_not_pass_existence_check_if_junit_file_is_missing_after_tests_run_with_junit_report(): void
    {
        $coveragePath = Path::canonicalize(self::COVERAGE_DIR_PATH);

        $checker = new CoverageChecker(
            false,
            false,
            '',
            $coveragePath,
            true,
            $this->createInvalidJUnitReportLocatorMock(),
            'unknown',
            new FixedLocator(self::COVERAGE_DIR_PATH . '/index.xml'),
        );

        $this->expectException(CoverageNotFound::class);
        $this->expectExceptionMessage(<<<'TXT'
            The code coverage generated by the initial test run is invalid. Please report the issue on the
            infection repository "https://github.com/infection/infection".

            ```
            $ bin/phpunit --coverage-xml=coverage/coverage-xml --log-junit=coverage=junit.xml
            Ok!
            ```

            Issue(s):
            - The JUnit file could not be found: Could not locate the JUnit coverage report for some reasons!
            TXT
        );

        $checker->checkCoverageHasBeenGenerated(
            'bin/phpunit --coverage-xml=coverage/coverage-xml --log-junit=coverage=junit.xml',
            'Ok!',
        );
    }

    public function test_it_passes_existence_check_if_xml_index_is_found_and_junit_file_is_missing_after_tests_run_without_junit_report(): void
    {
        $checker = new CoverageChecker(
            false,
            false,
            '',
            self::COVERAGE_DIR_PATH,
            false,
            new FakeLocator(),
            'unknown',
            new FixedLocator(self::COVERAGE_DIR_PATH . '/index.xml'),
        );

        $checker->checkCoverageHasBeenGenerated(
            'bin/phpunit --coverage-xml=coverage/coverage-xml --log-junit=coverage=junit.xml',
            'Ok!',
        );

        $this->addToAssertionCount(1);
    }

    private function requireNoPhpDbg(): void
    {
        if (PHP_SAPI === 'phpdbg') {
            $this->markTestSkipped('Test requires phpdbg to be disabled to run.');
        }
    }

    private function requireNoXdebug(): void
    {
        if (extension_loaded('xdebug')) {
            $this->markTestSkipped('Test requires xdebug to be disabled to run.');
        }
    }

    private function requireNoPcov(): void
    {
        if (extension_loaded('pcov')) {
            $this->markTestSkipped('Test requires pcov to be disabled to run.');
        }
    }

    private function createInvalidIndexLocatorMock(): ReportLocator&MockObject
    {
        $indexLocatorMock = $this->createMock(ReportLocator::class);
        $indexLocatorMock
            ->method('locate')
            ->willThrowException(UnknownReportLocatorException::create('index.xml'))
        ;

        return $indexLocatorMock;
    }

    private function createInvalidJUnitReportLocatorMock(): ReportLocator&MockObject
    {
        $jUnitLocatorMock = $this->createMock(ReportLocator::class);
        $jUnitLocatorMock
            ->method('locate')
            ->willThrowException(UnknownReportLocatorException::create('JUnit'))
        ;

        return $jUnitLocatorMock;
    }
}
