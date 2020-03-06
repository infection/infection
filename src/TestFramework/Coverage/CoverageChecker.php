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

namespace Infection\TestFramework\Coverage;

use Composer\XdebugHandler\XdebugHandler;
use Infection\AbstractTestFramework\TestFrameworkAdapter;
use Infection\TestFramework\Codeception\CodeceptionAdapter;
use Infection\TestFramework\PhpUnit\Adapter\PhpUnitAdapter;
use function count;
use const DIRECTORY_SEPARATOR;
use function extension_loaded;
use function file_exists;
use function implode;
use Infection\TestFramework\Coverage\XmlReport\PhpUnitXmlCoverageFactory;
use const PHP_EOL;
use const PHP_SAPI;
use function Safe\preg_match;
use function Safe\sprintf;
use Webmozart\PathUtil\Path;

/**
 * @internal
 */
final class CoverageChecker
{
    private $skipCoverage;
    private $skipInitialTests;
    private $initialTestPhpOptions;
    private $coveragePath;
    private $jUnitPath;
    private $testFrameworkAdapter;

    public function __construct(
        bool $skipCoverage,
        bool $skipInitialTests,
        string $initialTestPhpOptions,
        string $coveragePath,
        string $jUnitPath,
        TestFrameworkAdapter $testFrameworkAdapter
    ) {
        $this->skipCoverage = $skipCoverage;
        $this->skipInitialTests = $skipInitialTests;
        $this->initialTestPhpOptions = $initialTestPhpOptions;
        $this->coveragePath = $coveragePath;
        $this->jUnitPath = $jUnitPath;
        $this->testFrameworkAdapter = $testFrameworkAdapter;
    }

    public function checkCoverageRequirements(): void
    {
        if ($this->skipInitialTests && !$this->skipCoverage) {
            throw new CoverageNotFound(sprintf(
                'The initial test suite run is being skipped. The XML %sreports need to be '
                .'provided with the "--coverage" option',
                $this->testFrameworkAdapter->hasJUnitReport() ?
                    'and JUnit coverage '
                    : ''
            ));
        }

        if (!$this->skipCoverage && !$this->hasCoverageGeneratorEnabled()) {
            throw new CoverageNotFound(<<<TXT
Coverage needs to be generated but no code coverage generator (pcov, phpdbg or xdebug) has been detected. Please either:
- Enable pcov and run infection again
- Use phpdbg, e.g. `phpdbg -qrr infection`
- Enable Xdebug and run infection again
- Use the "--coverage" option with path to the existing coverage report
- Enable the code generator tool for the initial test run only, e.g. with `--initial-tests-php-options -d zend_extension=xdebug.so`
TXT
            );
        }
    }

    public function checkCoverageExists(): void
    {
        $coverageIndexFilePath = Path::canonicalize(
            $this->coveragePath . DIRECTORY_SEPARATOR . PhpUnitXmlCoverageFactory::COVERAGE_INDEX_FILE_NAME
        );

        if (!file_exists($coverageIndexFilePath)) {
            throw new CoverageNotFound(sprintf(
                'Could not find the file "%s". Please ensure that the XML coverage report has been '
                . 'properly generated at the right place. If using PHPUnit for example, the option '
                . 'for the path given is "--coverage-xml=%s"',
                $coverageIndexFilePath,
                $this->coveragePath . '/coverage-xml'
            ));
        }

        if ($this->testFrameworkAdapter->hasJUnitReport() && !file_exists($this->jUnitPath)) {
            $message = sprintf(
                'Could not find the file "%s". Please ensure that the JUnit coverage report '
                .'has been properly generated at the right place.',
                $this->jUnitPath
            );

            if ($this->testFrameworkAdapter instanceof PhpUnitAdapter) {
                $message .= sprintf(
                    ' The PHPUnit option for the path given is "--log-junit=%s/junit.xml"',
                    $this->coveragePath
                );
            } elseif ($this->testFrameworkAdapter instanceof CodeceptionAdapter) {
                $message .= ' The Codeception option for the path given is "--xml"';
            }

            throw new CoverageNotFound($message);
        }
    }

    public function checkCoverageHasBeenGenerated(
        string $initialTestSuiteCommandLine,
        string $initialTestSuiteOutput
    ): void {
        $errors = [];

        $coverageIndexFilePath = Path::canonicalize(
            $this->coveragePath . DIRECTORY_SEPARATOR . PhpUnitXmlCoverageFactory::COVERAGE_INDEX_FILE_NAME
        );

        if (!file_exists($coverageIndexFilePath)) {
            $errors[] = sprintf('- The file "%s" could not be found', $coverageIndexFilePath);
        }

        if ($this->testFrameworkAdapter->hasJUnitReport() && !file_exists($this->jUnitPath)) {
            $errors[] = sprintf('- The file "%s" could not be found', $this->jUnitPath);
        }

        if (count($errors) === 0) {
            return;
        }

        $message = sprintf(<<<TXT
The code coverage generated by the initial test run is invalid. Please report the issue on the
infection repository "%s".

```
$ %s
%s
```

Issue(s):
%s
TXT
            ,
            'https://github.com/infection/infection',
            $initialTestSuiteCommandLine,
            $initialTestSuiteOutput,
            implode(PHP_EOL, $errors)
        );

        throw new CoverageNotFound($message);
    }

    private function hasCoverageGeneratorEnabled(): bool
    {
        return PHP_SAPI === 'phpdbg'
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
