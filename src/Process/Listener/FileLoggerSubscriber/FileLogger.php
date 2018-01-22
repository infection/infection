<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

namespace Infection\Process\Listener\FileLoggerSubscriber;

use Infection\Config\InfectionConfig;
use Infection\Filesystem\Filesystem;
use Infection\Mutant\MetricsCalculator;

abstract class FileLogger
{
    /**
     * @var InfectionConfig
     */
    protected $infectionConfig;

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
        InfectionConfig $infectionConfig,
        MetricsCalculator $metricsCalculator,
        Filesystem $fs,
        bool $isDebugMode = true
    ) {
        $this->infectionConfig = $infectionConfig;
        $this->metricsCalculator = $metricsCalculator;
        $this->fs = $fs;
        $this->isDebugMode = $isDebugMode;
    }

    abstract public function writeToFile();
}
