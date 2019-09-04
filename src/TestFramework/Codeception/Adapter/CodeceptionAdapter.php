<?php

declare(strict_types=1);

namespace Infection\TestFramework\Codeception\Adapter;

use Infection\TestFramework\AbstractTestFrameworkAdapter;
use Infection\TestFramework\Coverage\XMLLineCodeCoverage;
use Infection\TestFramework\MemoryUsageAware;
use Infection\TestFramework\TestFrameworkTypes;

class CodeceptionAdapter extends AbstractTestFrameworkAdapter implements MemoryUsageAware
{
    public const EXECUTABLE = 'codecept';

    public function testsPass(string $output): bool
    {
        if (preg_match('/failures!/i', $output)) {
            return false;
        }

        if (preg_match('/errors!/i', $output)) {
            return false;
        }

        // OK (XX tests, YY assertions)
        $isOk = preg_match('/OK\s\(/', $output);

        // "OK, but incomplete, skipped, or risky tests!"
        $isOkWithInfo = preg_match('/OK\s?,/', $output);

        // "Warnings!" - e.g. when deprecated functions are used, but tests pass
        $isWarning = preg_match('/warnings!/i', $output);

        return $isOk || $isOkWithInfo || $isWarning;
    }

    public function getInitialTestRunCommandLine(string $configPath, string $extraOptions, array $phpExtraArgs): array
    {
        $commandLine = parent::getInitialTestRunCommandLine($configPath, $extraOptions, $phpExtraArgs);

        /*
         * Codeception does not support settings for coverage reports in `codeception.yaml`, so we have to
         * add values in the command line for initial tests run, but don't add coverage for mutants command lines
         */
        return array_merge(
            $commandLine,
            [
                '--coverage-phpunit',
                XMLLineCodeCoverage::CODECEPTION_COVERAGE_DIR,
            ]
        );
    }

    public function getMemoryUsed(string $output): float
    {
        if (preg_match('/Memory: (\d+(?:\.\d+))\s*MB/', $output, $match)) {
            return (float) $match[1];
        }

        return -1;
    }

    public function getName(): string
    {
        return TestFrameworkTypes::CODECEPTION;
    }
}
