<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Logger;

use Infection\Mutant\MetricsCalculator;
use Infection\Process\MutantProcessInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Exception\IOException;
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
    protected $isDebugVerbosity;

    /**
     * @var bool
     */
    protected $isDebugMode;

    /**
     * @var OutputInterface
     */
    private $output;

    public function __construct(
        OutputInterface $output,
        string $logFilePath,
        MetricsCalculator $metricsCalculator,
        Filesystem $fs,
        bool $isDebugVerbosity,
        bool $isDebugMode
    ) {
        $this->logFilePath = $logFilePath;
        $this->metricsCalculator = $metricsCalculator;
        $this->fs = $fs;
        $this->isDebugVerbosity = $isDebugVerbosity;
        $this->isDebugMode = $isDebugMode;
        $this->output = $output;
    }

    public function log(): void
    {
        try {
            $this->fs->dumpFile($this->logFilePath, implode("\n", $this->getLogLines()));
        } catch (IOException $e) {
            $this->output->writeln(sprintf('<error>%s</error>', $e->getMessage()));
        }
    }

    abstract protected function getLogLines(): array;

    /**
     * @param MutantProcessInterface[] $processes
     */
    final protected function sortProcesses(array &$processes): void
    {
        usort($processes, function (MutantProcessInterface $a, MutantProcessInterface $b): int {
            if ($a->getOriginalFilePath() === $b->getOriginalFilePath()) {
                return $a->getOriginalStartingLine() <=> $b->getOriginalStartingLine();
            }

            return $a->getOriginalFilePath() <=> $b->getOriginalFilePath();
        });
    }
}
