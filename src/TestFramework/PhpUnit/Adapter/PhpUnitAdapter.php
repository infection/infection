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

namespace Infection\TestFramework\PhpUnit\Adapter;

use function escapeshellarg;
use Infection\AbstractTestFramework\MemoryUsageAware;
use Infection\AbstractTestFramework\SyntaxErrorAware;
use Infection\Config\ValueProvider\PCOVDirectoryProvider;
use Infection\TestFramework\AbstractTestFrameworkAdapter;
use Infection\TestFramework\CommandLineArgumentsAndOptionsBuilder;
use Infection\TestFramework\CommandLineBuilder;
use Infection\TestFramework\Config\InitialConfigBuilder;
use Infection\TestFramework\Config\MutationConfigBuilder;
use Infection\TestFramework\ProvidesInitialRunOnlyOptions;
use Infection\TestFramework\VersionParser;
use function Safe\preg_match;
use function sprintf;
use function trim;
use function version_compare;

/**
 * @internal
 * @final
 */
class PhpUnitAdapter extends AbstractTestFrameworkAdapter implements MemoryUsageAware, ProvidesInitialRunOnlyOptions, SyntaxErrorAware
{
    final public const COVERAGE_DIR = 'coverage-xml';

    public function __construct(
        string $testFrameworkExecutable,
        private readonly string $tmpDir,
        private readonly string $jUnitFilePath,
        private readonly PCOVDirectoryProvider $pcovDirectoryProvider,
        InitialConfigBuilder $initialConfigBuilder,
        MutationConfigBuilder $mutationConfigBuilder,
        CommandLineArgumentsAndOptionsBuilder $argumentsAndOptionsBuilder,
        VersionParser $versionParser,
        CommandLineBuilder $commandLineBuilder,
        ?string $version = null,
    ) {
        parent::__construct($testFrameworkExecutable, $initialConfigBuilder, $mutationConfigBuilder, $argumentsAndOptionsBuilder, $versionParser, $commandLineBuilder, $version);
    }

    public function hasJUnitReport(): bool
    {
        return true;
    }

    /**
     * Returns array of arguments to pass them into the Initial Run Process
     *
     * @param string[] $phpExtraArgs
     *
     * @return string[]
     */
    public function getInitialTestRunCommandLine(
        string $extraOptions,
        array $phpExtraArgs,
        bool $skipCoverage,
    ): array {
        if ($skipCoverage === false) {
            $extraOptions = trim(sprintf(
                '%s --coverage-xml=%s --log-junit=%s',
                $extraOptions,
                $this->tmpDir . '/' . self::COVERAGE_DIR,
                $this->jUnitFilePath, // escapeshellarg() is done up the stack in ArgumentsAndOptionsBuilder
            ));

            if ($this->pcovDirectoryProvider->shallProvide()) {
                $phpExtraArgs[] = '-d';
                $phpExtraArgs[] = sprintf('pcov.directory=%s', escapeshellarg($this->pcovDirectoryProvider->getDirectory()));
            }
        }

        return parent::getInitialTestRunCommandLine($extraOptions, $phpExtraArgs, $skipCoverage);
    }

    public function testsPass(string $output): bool
    {
        if (preg_match('/failures!/i', $output) === 1) {
            return false;
        }

        if (preg_match('/errors!/i', $output) === 1) {
            return false;
        }

        // OK (XX tests, YY assertions)
        $isOk = preg_match('/OK\s\(/', $output) === 1;

        // "OK, but incomplete, skipped, or risky tests!"
        $isOkWithInfo = preg_match('/OK\s?,/', $output) === 1;

        // "Warnings!" - e.g. when deprecated functions are used, but tests pass
        $isWarning = preg_match('/warnings!/i', $output) === 1;

        // "No tests executed!" - e.g. when --filter option contains too large regular expression
        $isNoTestsExecuted = preg_match('/No tests executed!/i', $output) === 1;

        return $isOk || $isOkWithInfo || $isWarning || $isNoTestsExecuted;
    }

    public function isSyntaxError(string $output): bool
    {
        return preg_match('/ParseError: syntax error/i', $output) === 1;
    }

    public function getMemoryUsed(string $output): float
    {
        if (preg_match('/Memory: (\d+(?:\.\d+))\s*MB/', $output, $match) === 1) {
            return (float) $match[1];
        }

        return -1.;
    }

    public function getName(): string
    {
        return 'PHPUnit';
    }

    public function getInitialTestsFailRecommendations(string $commandLine): string
    {
        $recommendations = parent::getInitialTestsFailRecommendations($commandLine);

        if (version_compare($this->getVersion(), '7.2', '>=')) {
            $recommendations = sprintf(
                "%s\n\n%s\n\n%s",
                "Infection runs the test suite in a RANDOM order. Make sure your tests do not have hidden dependencies.\n\n"
                . 'You can add these attributes to `phpunit.xml` to check it: <phpunit executionOrder="random" resolveDependencies="true" ...',
                'If you don\'t want to let Infection run tests in a random order, set the `executionOrder` to some value, for example <phpunit executionOrder="default"',
                parent::getInitialTestsFailRecommendations($commandLine),
            );
        }

        return $recommendations;
    }

    /**
     * @return string[]
     */
    public function getInitialRunOnlyOptions(): array
    {
        return ['--configuration', '--filter', '--testsuite'];
    }
}
