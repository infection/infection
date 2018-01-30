<?php
/**
 * Copyright © 2018 Tobias Stadler
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\TestFramework\Codeception\CommandLine;

use Infection\Mutant\Mutant;
use Infection\TestFramework\CommandLineArgumentsAndOptionsBuilder;
use Infection\TestFramework\Coverage\CodeCoverageData;

class ArgumentsAndOptionsBuilder implements CommandLineArgumentsAndOptionsBuilder
{

    /**
     * @var string
     */
    private $tempDir;

    public function __construct(string $tempDir)
    {
        $this->tempDir = $tempDir;
    }

    public function build(string $configPath, string $extraOptions, Mutant $mutant = null): string
    {
        $options = [
            'run',
            '--no-colors'
        ];

        if ($mutant !== null) {
            $options[] = '-o "paths: output: ' . $this->tempDir . '/' . $mutant->getMutation()->getHash() . '"';
            $options[] = '-o "coverage: enabled: false"';
            $options[] = '--ext "Infection\TestFramework\Codeception\CustomAutoloadFilePath"';
            $options[] = '--fail-fast';
        } else {
            $options[] = '-o "paths: output: ' . $this->tempDir . '"';
            $options[] = '-o "coverage: enabled: true"';
            $options[] = '--coverage-phpunit ' . CodeCoverageData::CODECEPTION_COVERAGE_DIR;
        }

        $options[] = $extraOptions;

        return implode(' ', $options);
    }
}
