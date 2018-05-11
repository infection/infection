<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Console;

use Infection\Mutant\Exception\MsiCalculationException;
use Infection\Mutant\MetricsCalculator;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Process\Process;

/**
 * @internal
 */
final class ConsoleOutput
{
    const CI_FLAG_ERROR = 'The minimum required %s percentage should be %s%%, but actual is %s%%. Improve your tests!';

    /**
     * @var SymfonyStyle
     */
    private $io;

    public function __construct(SymfonyStyle $io)
    {
        $this->io = $io;
    }

    public function logVerbosityDeprecationNotice(string $valueToUse)
    {
        $this->io->note('Numeric versions of log-verbosity have been deprecated, please use, ' . $valueToUse . ' to keep the same result');
    }

    public function logUnkownVerbosityOption(string $default)
    {
        $this->io->note('Running infection with an unknown log-verbosity option, falling back to ' . $default . ' option');
    }

    public function logInitialTestsDoNotPass(Process $initialTestSuitProcess, string $testFrameworkKey)
    {
        $lines = [
            'Project tests must be in a passing state before running Infection.',
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

    public function logBadMsiErrorMessage(MetricsCalculator $metricsCalculator, float $minMsi)
    {
        if (!$minMsi) {
            throw MsiCalculationException::create('min-msi');
        }

        $this->io->error(
            sprintf(
                self::CI_FLAG_ERROR,
                'MSI',
                $minMsi,
                $metricsCalculator->getMutationScoreIndicator()
            )
        );
    }

    public function logBadCoveredMsiErrorMessage(MetricsCalculator $metricsCalculator, float $minCoveredMsi)
    {
        if (!$minCoveredMsi) {
            throw MsiCalculationException::create('min-covered-msi');
        }

        $this->io->error(
            sprintf(
                self::CI_FLAG_ERROR,
                'Covered Code MSI',
                $minCoveredMsi,
                $metricsCalculator->getCoveredCodeMutationScoreIndicator()
            )
        );
    }

    public function logMissedDebuggerOrCoverageOption()
    {
        $this->io->error([
            'Neither phpdbg or xdebug has been found. One of those is required by Infection in order to generate coverage data. Either:',
            '- Enable xdebug and run infection again' . PHP_EOL .
            '- Use phpdbg: phpdbg -qrr infection' . PHP_EOL .
            '- Use --coverage option with path to the existing coverage report' . PHP_EOL .
            '- Use --initial-tests-php-options option with `-d zend_extension=xdebug.so` and/or any extra php parameters',
        ]);
    }
}
