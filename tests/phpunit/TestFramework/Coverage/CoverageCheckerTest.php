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

namespace Infection\Tests\TestFramework\Coverage;

use function extension_loaded;
use Infection\AbstractTestFramework\TestFrameworkAdapter;
use Infection\TestFramework\Coverage\CoverageChecker;
use Infection\TestFramework\Coverage\CoverageNotFound;
use Infection\TestFramework\PhpUnit\Adapter\PhpUnitAdapter;
use const PHP_SAPI;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use function Safe\sprintf;
use Webmozart\PathUtil\Path;

/**
 * All these tests should be ran in separate processes, as otherwise they may rely
 * on the internal state of XdebugHandler.
 *
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 *
 * @group integration Requires some I/O operations
 */
final class CoverageCheckerTest extends TestCase
{
    /**
     * @var string
     */
    private static $coveragePath;

    /**
     * @var string
     */
    private static $jUnit;

    /**
     * @var TestFrameworkAdapter|MockObject
     */
    private $testFrameworkAdapterMock;

    public static function setUpBeforeClass(): void
    {
        self::$coveragePath = Path::canonicalize(__DIR__ . '/../../Fixtures/Files/phpunit/coverage/coverage-xml');
        self::$jUnit = Path::canonicalize(__DIR__ . '/../../Fixtures/Files/phpunit/junit.xml');
    }

    public function test_it_needs_coverage_to_be_provided_if_initial_tests_are_skipped_without_JUnit_report(): void
    {
        $checker = new CoverageChecker(
            false,
            true,
            '',
            '',
            null,
            'unknown'
        );

        $this->expectException(CoverageNotFound::class);
        $this->expectExceptionMessage(
            'The initial test suite run is being skipped. The XML reports need to be provided with '
            . 'the "--coverage" option'
        );

        $checker->checkCoverageRequirements();
    }

    public function test_it_needs_coverage_to_be_provided_if_initial_tests_are_skipped_with_JUnit_report(): void
    {
        $checker = new CoverageChecker(
            false,
            true,
            '',
            '',
            '/path/to/junit.xml',
            'unknown'
        );

        $this->expectException(CoverageNotFound::class);
        $this->expectExceptionMessage(
            'The initial test suite run is being skipped. The XML and JUnit coverage reports need '
            . 'to be provided with the "--coverage" option'
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
            '',
            'unknown'
        );

        $this->expectException(CoverageNotFound::class);
        $this->expectExceptionMessage(<<<TXT
Coverage needs to be generated but no code coverage generator (pcov, phpdbg or xdebug) has been detected. Please either:
- Enable pcov and run infection again
- Use phpdbg, e.g. `phpdbg -qrr infection`
- Enable Xdebug and run infection again
- Use the "--coverage" option with path to the existing coverage report
- Enable the code generator tool for the initial test run only, e.g. with `--initial-tests-php-options -d zend_extension=xdebug.so`
TXT
        );

        $checker->checkCoverageRequirements();
    }

    public function test_it_passes_existence_check_if_XML_index_and_JUnit_files_are_found_with_JUnit_report(): void
    {
        $checker = new CoverageChecker(
            false,
            false,
            '',
            self::$coveragePath,
            self::$jUnit,
            'unknown'
        );

        $checker->checkCoverageExists();

        $this->addToAssertionCount(1);
    }

    public function test_it_passes_existence_check_if_XML_index_and_JUnit_files_are_found_without_JUnit_report(): void
    {
        $checker = new CoverageChecker(
            false,
            false,
            '',
            self::$coveragePath,
            self::$jUnit,
            'unknown'
        );

        $checker->checkCoverageExists();

        $this->addToAssertionCount(1);
    }

    public function test_it_does_not_pass_existence_check_if_XML_index_is_missing_with_lambda_test_framework_adapter(): void
    {
        $checker = new CoverageChecker(
            false,
            false,
            '',
            '/nowhere',
            self::$jUnit,
            'unknown'
        );

        $this->expectException(CoverageNotFound::class);
        $this->expectExceptionMessage(
            'Could not find the file "/nowhere/index.xml". Please ensure that the XML coverage '
            . 'report has been properly generated at the right place.'
        );

        $checker->checkCoverageExists();
    }

    public function test_it_does_not_pass_existence_check_if_XML_index_is_missing_with_PHPUnit(): void
    {
        $checker = new CoverageChecker(
            false,
            false,
            '',
            '/nowhere',
            self::$jUnit,
            'phpunit'
        );

        $this->expectException(CoverageNotFound::class);
        $this->expectExceptionMessage(
            'Could not find the file "/nowhere/index.xml". Please ensure that the XML coverage '
            . 'report has been properly generated at the right place. The PHPUnit option for the '
            . 'path given is "--coverage-xml=/nowhere/coverage-xml"'
        );

        $checker->checkCoverageExists();
    }

    public function test_it_does_not_pass_existence_check_if_XML_index_is_missing_with_Codeception(): void
    {
        $checker = new CoverageChecker(
            false,
            false,
            '',
            '/nowhere',
            self::$jUnit,
            'codeception'
        );

        $this->expectException(CoverageNotFound::class);
        $this->expectExceptionMessage(
            'Could not find the file "/nowhere/index.xml". Please ensure that the XML coverage '
            . 'report has been properly generated at the right place. The Codeception option for the'
            . ' path given is "--coverage-phpunit=/nowhere/coverage-xml"'
        );

        $checker->checkCoverageExists();
    }

    public function test_it_passes_existence_check_if_XML_index_is_present_and_JUnit_file_is_missing_without_JUnit_report(): void
    {
        $checker = new CoverageChecker(
            false,
            false,
            '',
            self::$coveragePath,
            null,
            'unknown'
        );

        $checker->checkCoverageExists();

        $this->addToAssertionCount(1);
    }

    public function test_it_does_not_pass_existence_check_if_JUnit_file_is_missing_with_JUnit_report_with_lambda_test_framework_adapter(): void
    {
        $checker = new CoverageChecker(
            false,
            false,
            '',
            self::$coveragePath,
            '/invalid/path/to/junit.xml',
            'unknown'
        );

        $this->expectException(CoverageNotFound::class);
        $this->expectExceptionMessage(sprintf(
            'Could not find the file "/invalid/path/to/junit.xml". Please ensure that the '
            . 'JUnit coverage report has been properly generated at the right place.',
            self::$coveragePath
        ));

        $checker->checkCoverageExists();
    }

    public function test_it_does_not_pass_existence_check_if_JUnit_file_is_missing_with_JUnit_report_with_PHPUnit_test_framework_adapter(): void
    {
        $phpUnitAdapterMock = $this->createMock(PhpUnitAdapter::class);
        $phpUnitAdapterMock->method('hasJUnitReport')->willReturn(true);

        $checker = new CoverageChecker(
            false,
            false,
            '',
            self::$coveragePath,
            '/invalid/path/to/junit.xml',
            'phpunit'
        );

        $this->expectException(CoverageNotFound::class);
        $this->expectExceptionMessage(sprintf(
            'Could not find the file "/invalid/path/to/junit.xml". Please ensure that the '
            . 'JUnit coverage report has been properly generated at the right place. The PHPUnit '
            . 'option for the path given is "--log-junit=%s/junit.xml"',
            self::$coveragePath
        ));

        $checker->checkCoverageExists();
    }

    public function test_it_does_not_pass_existence_check_if_JUnit_file_is_missing_with_JUnit_report_with_Codeception_test_framework_adapter(): void
    {
        $checker = new CoverageChecker(
            false,
            false,
            '',
            self::$coveragePath,
            '/invalid/path/to/junit.xml',
            'codeception'
        );

        $this->expectException(CoverageNotFound::class);
        $this->expectExceptionMessage('Could not find the file "/invalid/path/to/junit.xml". Please'
            . ' ensure that the JUnit coverage report has been properly generated at the right '
            . 'place. The Codeception option for the path given is "--xml"'
        );

        $checker->checkCoverageExists();
    }

    public function test_it_passes_existence_check_if_XML_index_and_JUnit_files_are_found_after_tests_run_with_JUnit_report(): void
    {
        $checker = new CoverageChecker(
            false,
            false,
            '',
            self::$coveragePath,
            self::$jUnit,
            'unknown'
        );

        $checker->checkCoverageHasBeenGenerated(
            'bin/phpunit --coverage-xml=coverage/coverage-xml --log-junit=coverage=junit.xml',
            'Ok!'
        );

        $this->addToAssertionCount(1);
    }

    public function test_it_passes_existence_check_if_XML_index_and_JUnit_files_are_found_after_tests_run_without_JUnit_report(): void
    {
        $checker = new CoverageChecker(
            false,
            false,
            '',
            self::$coveragePath,
            self::$jUnit,
            'unknown'
        );

        $checker->checkCoverageHasBeenGenerated(
            'bin/phpunit --coverage-xml=coverage/coverage-xml --log-junit=coverage=junit.xml',
            'Ok!'
        );

        $this->addToAssertionCount(1);
    }

    public function test_it_does_not_pass_existence_check_if_XML_index_is_missing_after_tests_run(): void
    {
        $checker = new CoverageChecker(
            false,
            false,
            '',
            '/nowhere',
            self::$jUnit,
            'unknown'
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
- The file "/nowhere/index.xml" could not be found
TXT
        );

        $checker->checkCoverageHasBeenGenerated(
            'bin/phpunit --coverage-xml=coverage/coverage-xml --log-junit=coverage=junit.xml',
            'Ok!'
        );
    }

    public function test_it_does_not_pass_existence_check_if_JUnit_file_is_missing_after_tests_run_with_JUnit_report(): void
    {
        $coveragePath = Path::canonicalize(self::$coveragePath);

        $checker = new CoverageChecker(
            false,
            false,
            '',
            $coveragePath,
            '/invalid/path/to/junit.xml',
            'unknown'
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
- The file "/invalid/path/to/junit.xml" could not be found
TXT
        );

        $checker->checkCoverageHasBeenGenerated(
            'bin/phpunit --coverage-xml=coverage/coverage-xml --log-junit=coverage=junit.xml',
            'Ok!'
        );
    }

    public function test_it_passes_existence_check_if_XML_index_is_found_and_JUnit_file_is_missing_after_tests_run_without_JUnit_report(): void
    {
        $checker = new CoverageChecker(
            false,
            false,
            '',
            self::$coveragePath,
            null,
            'unknown'
        );

        $checker->checkCoverageHasBeenGenerated(
            'bin/phpunit --coverage-xml=coverage/coverage-xml --log-junit=coverage=junit.xml',
            'Ok!'
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
}
