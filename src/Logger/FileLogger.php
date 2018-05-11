<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Logger;

use Infection\Mutant\MetricsCalculator;
use Symfony\Component\Filesystem\Filesystem;

/**
 * @internal
 */
abstract class FileLogger implements MutationTestingResultsLogger
{
    /**
     * @var string
     */
    private $logFilePath;

    /**
     * @var MetricsCalculator
     */
    protected $metricsCalculator;

    /**
     * @var Filesystem
     */
    private $fs;

    /**
     * @var bool
     */
    protected $isDebugMode;

    public function __construct(
        string $logFilePath,
        MetricsCalculator $metricsCalculator,
        Filesystem $fs,
        bool $isDebugMode
    ) {
        $this->metricsCalculator = $metricsCalculator;
        $this->fs = $fs;
        $this->isDebugMode = $isDebugMode;
        $this->logFilePath = $logFilePath;
    }

    public function log()
    {
        $this->fs->dumpFile($this->logFilePath, implode($this->getLogLines(), "\n"));
    }

    abstract protected function getLogLines(): array;
}
