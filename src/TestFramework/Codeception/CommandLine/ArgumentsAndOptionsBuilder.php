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
    private $tmpDir;

    public function __construct(string $tmpDir)
    {
        $this->tmpDir = $tmpDir;
    }

    public function build(string $configPath, string $extraOptions, Mutant $mutant = null): string
    {
        $options = [
            'run',
            '--no-colors',
            '--config=' . $configPath,
        ];

        if ($mutant !== null) {
            $options[] = '--ext "Infection\TestFramework\Codeception\CustomAutoloadFilePath"';
            $options[] = '--fail-fast';
        } else {
            $options[] = '--coverage-phpunit ' . CodeCoverageData::CODECEPTION_COVERAGE_DIR;
        }

        $options[] = $extraOptions;

        return implode(' ', $options);
    }
}
