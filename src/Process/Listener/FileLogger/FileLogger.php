<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Process\Listener\FileLogger;

use Infection\Filesystem\Filesystem;
use Infection\Mutant\MetricsCalculator;

abstract class FileLogger
{
    /**
     * @var string
     */
    protected $logFilePath;
    /**
     * @var MetricsCalculator
     */
    protected $metricsCalculator;
    /**
     * @var Filesystem
     */
    protected $fs;
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
        $this->logFilePath = $logFilePath;
        $this->metricsCalculator = $metricsCalculator;
        $this->fs = $fs;
        $this->isDebugMode = $isDebugMode;
    }

    abstract public function writeToFile();

    final protected function write(array $logs)
    {
        if ($this->logFilePath !== null) {
            $this->fs->dumpFile(
                $this->logFilePath,
                implode(
                    $logs,
                    "\n"
                )
            );
        }
    }
}
