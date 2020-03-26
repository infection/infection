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
use Infection\FileSystem\Locator\FileNotFound;
use Infection\TestFramework\Coverage\CoverageChecker;
use Infection\TestFramework\Coverage\CoverageNotFound;
use Infection\TestFramework\Coverage\JUnit\JUnitReportLocator;
use Infection\TestFramework\Coverage\XmlReport\IndexXmlCoverageLocator;
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

    public static function setUpBeforeClass(): void
    {
        self::$coveragePath = Path::canonicalize(__DIR__ . '/../../Fixtures/Files/phpunit/coverage');
        self::$jUnit = Path::canonicalize(__DIR__ . '/../../Fixtures/Files/phpunit/junit.xml');
    }

    public function test_it_needs_coverage_to_be_provided_if_initial_tests_are_skipped_without_JUnit_report(): void
    {
        $checker = new CoverageChecker(
            false,
            true,
            '',
            '',
            false,
            $this->createFakeJUnitReportLocatorMock(),
            'unknown',
            $this->createFakeIndexLocatorMock()
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
            true,
            $this->createJUnitReportLocatorMock(),
            'unknown',
            $this->createFakeIndexLocatorMock()
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
            false,
            $this->createFakeJUnitReportLocatorMock(),
            'unknown',
            $this->createFakeIndexLocatorMock()
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
            true,
            $this->createJUnitReportLocatorMock(),
            'unknown',
            $this->createIndexLocatorMock(self::$coveragePath . '/index.xml')
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
            true,
            $this->createJUnitReportLocatorMock(),
            'unknown',
            $this->createInvalidIndexLocatorMock()
        );

        try {
            $checker->checkCoverageExists();

            $this->fail();
        } catch (CoverageNotFound $exception) {
            $this->assertSame(
                'Could not find the "index.xml" file. Please ensure that the XML coverage '
                . 'report has been properly generated at the right place.',
                $exception->getMessage()
            );
            $this->assertSame(0, $exception->getCode());
            $this->assertInstanceOf(FileNotFound::class, $exception->getPrevious());
        }
    }

    public function test_it_does_not_pass_existence_check_if_XML_index_is_missing_with_PHPUnit(): void
    {
        $checker = new CoverageChecker(
            false,
            false,
            '',
            '/nowhere',
            true,
            $this->createJUnitReportLocatorMock(),
            'phpunit',
            $this->createInvalidIndexLocatorMock()
        );

        try {
            $checker->checkCoverageExists();

            $this->fail();
        } catch (CoverageNotFound $exception) {
            $this->assertSame(
                'Could not find the "index.xml" file. Please ensure that the XML coverage '
                . 'report has been properly generated at the right place. The PHPUnit option for the '
                . 'path given is "--coverage-xml=/nowhere/coverage-xml"',
                $exception->getMessage()
            );
            $this->assertSame(0, $exception->getCode());
            $this->assertInstanceOf(FileNotFound::class, $exception->getPrevious());
        }
    }

    public function test_it_does_not_pass_existence_check_if_XML_index_is_missing_with_Codeception(): void
    {
        $checker = new CoverageChecker(
            false,
            false,
            '',
            '/nowhere',
            true,
            $this->createJUnitReportLocatorMock(),
            'codeception',
            $this->createInvalidIndexLocatorMock()
        );

        try {
            $checker->checkCoverageExists();

            $this->fail();
        } catch (CoverageNotFound $exception) {
            $this->assertSame(
                'Could not find the "index.xml" file. Please ensure that the XML coverage '
                . 'report has been properly generated at the right place. The Codeception option for the'
                . ' path given is "--coverage-phpunit=/nowhere/coverage-xml"',
                $exception->getMessage()
            );
            $this->assertSame(0, $exception->getCode());
            $this->assertInstanceOf(FileNotFound::class, $exception->getPrevious());
        }
    }

    public function test_it_passes_existence_check_if_XML_index_is_present_and_JUnit_file_is_missing_without_JUnit_report(): void
    {
        $checker = new CoverageChecker(
            false,
            false,
            '',
            self::$coveragePath,
            false,
            $this->createFakeJUnitReportLocatorMock(),
            'unknown',
            $this->createIndexLocatorMock(self::$coveragePath . '/index.xml')
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
            true,
            $this->createInvalidJUnitReportLocatorMock(),
            'unknown',
            $this->createIndexLocatorMock(self::$coveragePath . '/index.xml')
        );

        try {
            $checker->checkCoverageExists();

            $this->fail();
        } catch (CoverageNotFound $exception) {
            $this->assertSame(
                sprintf(
                    'Could not find the JUnit file report. Please ensure that the JUnit coverage '
                    . 'report has been properly generated at the right place.',
                    self::$coveragePath
                ),
                $exception->getMessage()
            );
            $this->assertSame(0, $exception->getCode());
            $this->assertInstanceOf(FileNotFound::class, $exception->getPrevious());
        }
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
            true,
            $this->createInvalidJUnitReportLocatorMock(),
            'phpunit',
            $this->createIndexLocatorMock(self::$coveragePath . '/index.xml')
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
                    self::$coveragePath
                ),
                $exception->getMessage()
            );
            $this->assertSame(0, $exception->getCode());
            $this->assertInstanceOf(FileNotFound::class, $exception->getPrevious());
        }
    }

    public function test_it_does_not_pass_existence_check_if_JUnit_file_is_missing_with_JUnit_report_with_Codeception_test_framework_adapter(): void
    {
        $checker = new CoverageChecker(
            false,
            false,
            '',
            self::$coveragePath,
            true,
            $this->createInvalidJUnitReportLocatorMock(),
            'codeception',
            $this->createIndexLocatorMock(self::$coveragePath . '/index.xml')
        );

        try {
            $checker->checkCoverageExists();

            $this->fail();
        } catch (CoverageNotFound $exception) {
            $this->assertSame(
                sprintf(
                    'Could not find the JUnit file report. Please ensure that the JUnit coverage report has'
                    . ' been properly generated at the right place. The Codeception option for the path '
                    . 'given is "--xml"',
                    self::$coveragePath
                ),
                $exception->getMessage()
            );
            $this->assertSame(0, $exception->getCode());
            $this->assertInstanceOf(FileNotFound::class, $exception->getPrevious());
        }
    }

    public function test_it_passes_existence_check_if_XML_index_and_JUnit_files_are_found_after_tests_run_with_JUnit_report(): void
    {
        $checker = new CoverageChecker(
            false,
            false,
            '',
            self::$coveragePath,
            true,
            $this->createJUnitReportLocatorMock(),
            'unknown',
            $this->createIndexLocatorMock(self::$coveragePath . '/index.xml')
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
            true,
            $this->createJUnitReportLocatorMock(),
            'unknown',
            $this->createIndexLocatorMock(self::$coveragePath . '/index.xml')
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
            true,
            $this->createJUnitReportLocatorMock(),
            'unknown',
            $this->createInvalidIndexLocatorMock()
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
- The file "index.xml" could not be found: No index file found
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
            true,
            $this->createInvalidJUnitReportLocatorMock(),
            'unknown',
            $this->createIndexLocatorMock(self::$coveragePath . '/index.xml')
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
- The JUnit file could not be found: No JUnit file found
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
            false,
            $this->createFakeJUnitReportLocatorMock(),
            'unknown',
            $this->createIndexLocatorMock(self::$coveragePath . '/index.xml')
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

    /**
     * @return IndexXmlCoverageLocator|MockObject
     */
    private function createFakeIndexLocatorMock(): IndexXmlCoverageLocator
    {
        $indexLocatorMock = $this->createMock(IndexXmlCoverageLocator::class);
        $indexLocatorMock
            ->expects($this->never())
            ->method($this->anything())
        ;

        return $indexLocatorMock;
    }

    /**
     * @return JUnitReportLocator|MockObject
     */
    private function createFakeJUnitReportLocatorMock(): JUnitReportLocator
    {
        $jUnitLocatorMock = $this->createMock(JUnitReportLocator::class);
        $jUnitLocatorMock
            ->expects($this->never())
            ->method($this->anything())
        ;

        return $jUnitLocatorMock;
    }

    /**
     * @return IndexXmlCoverageLocator|MockObject
     */
    private function createInvalidIndexLocatorMock(): IndexXmlCoverageLocator
    {
        $indexLocatorMock = $this->createMock(IndexXmlCoverageLocator::class);
        $indexLocatorMock
            ->method('locate')
            ->willThrowException(new FileNotFound('No index file found'))
        ;

        return $indexLocatorMock;
    }

    /**
     * @return IndexXmlCoverageLocator|MockObject
     */
    private function createIndexLocatorMock(string $indexPath): IndexXmlCoverageLocator
    {
        $indexLocatorMock = $this->createMock(IndexXmlCoverageLocator::class);
        $indexLocatorMock
            ->method('locate')
            ->willReturn($indexPath)
        ;

        return $indexLocatorMock;
    }

    /**
     * @return JUnitReportLocator|MockObject
     */
    private function createInvalidJUnitReportLocatorMock(): JUnitReportLocator
    {
        $jUnitLocatorMock = $this->createMock(JUnitReportLocator::class);
        $jUnitLocatorMock
            ->method('locate')
            ->willThrowException(new FileNotFound('No JUnit file found'))
        ;

        return $jUnitLocatorMock;
    }

    /**
     * @return JUnitReportLocator|MockObject
     */
    private function createJUnitReportLocatorMock(): JUnitReportLocator
    {
        $jUnitLocatorMock = $this->createMock(JUnitReportLocator::class);
        $jUnitLocatorMock
            ->method('locate')
            ->willReturn(self::$jUnit)
        ;

        return $jUnitLocatorMock;
    }
}
