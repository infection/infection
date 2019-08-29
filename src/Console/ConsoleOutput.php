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

namespace Infection\Console;

use Infection\Mutant\Exception\MsiCalculationException;
use Infection\Mutant\MetricsCalculator;
use Infection\Process\Runner\TestRunConstraintChecker;
use Infection\TestFramework\AbstractTestFrameworkAdapter;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Process\Process;

/**
 * @internal
 */
final class ConsoleOutput
{
    public const INFECTION_USAGE_SUGGESTION = '- Enable xdebug and run infection again' . PHP_EOL .
        '- Use phpdbg: phpdbg -qrr infection' . PHP_EOL .
        '- Use --coverage option with path to the existing coverage report' . PHP_EOL .
        '- Use --initial-tests-php-options option with `-d zend_extension=xdebug.so` and/or any extra php parameters';
    private const CI_FLAG_ERROR = 'The minimum required %s percentage should be %s%%, but actual is %s%%. Improve your tests!';

    private const RUNNING_WITH_DEBUGGER_NOTE = 'You are running Infection with %s enabled.';

    /**
     * @var SymfonyStyle
     */
    private $io;

    public function __construct(SymfonyStyle $io)
    {
        $this->io = $io;
    }

    public function logVerbosityDeprecationNotice(string $valueToUse): void
    {
        $this->io->note('Numeric versions of log-verbosity have been deprecated, please use, ' . $valueToUse . ' to keep the same result');
    }

    public function logUnknownVerbosityOption(string $default): void
    {
        $this->io->note('Running infection with an unknown log-verbosity option, falling back to ' . $default . ' option');
    }

    public function logInitialTestsDoNotPass(Process $initialTestSuitProcess, AbstractTestFrameworkAdapter $testFrameworkAdapter): void
    {
        $testFrameworkKey = $testFrameworkAdapter->getName();

        $lines = [
            'Project tests must be in a passing state before running Infection.',
            $testFrameworkAdapter->getInitialTestsFailRecommendations($initialTestSuitProcess->getCommandLine()),
            sprintf(
                '%s reported an exit code of %d.',
                $testFrameworkKey,
                $initialTestSuitProcess->getExitCode()
            ),
            sprintf(
                'Refer to the %s\'s output below:',
                $testFrameworkKey
            ),
        ];

        if ($stdOut = $initialTestSuitProcess->getOutput()) {
            $lines[] = 'STDOUT:';
            $lines[] = $stdOut;
        }

        if ($stdError = $initialTestSuitProcess->getErrorOutput()) {
            $lines[] = 'STDERR:';
            $lines[] = $stdError;
        }

        $this->io->error($lines);
    }

    public function logBadMsiErrorMessage(MetricsCalculator $metricsCalculator, float $minMsi, string $type): void
    {
        if (!$minMsi) {
            throw MsiCalculationException::create('min-msi');
        }

        $this->io->error(
            sprintf(
                self::CI_FLAG_ERROR,
                ($type === TestRunConstraintChecker::MSI_FAILURE ? 'MSI' : 'Covered Code MSI'),
                $minMsi,
                ($type === TestRunConstraintChecker::MSI_FAILURE ?
                    $metricsCalculator->getMutationScoreIndicator() :
                    $metricsCalculator->getCoveredCodeMutationScoreIndicator()
                )
            )
        );
    }

    public function logMissedDebuggerOrCoverageOption(): void
    {
        $this->io->error([
            'Neither phpdbg or xdebug has been found. One of those is required by Infection in order to generate coverage data. Either:',
            self::INFECTION_USAGE_SUGGESTION,
        ]);
    }

    public function logRunningWithDebugger(string $debugger): void
    {
        $this->io->writeln(sprintf(self::RUNNING_WITH_DEBUGGER_NOTE, $debugger));
    }

    public function logNotInControlOfExitCodes(): void
    {
        $this->io->warning([
            'Infection cannot control exit codes and unable to relaunch itself.' . PHP_EOL .
            'It is your responsibility to disable xdebug/phpdbg unless needed.',
        ]);
    }
}
