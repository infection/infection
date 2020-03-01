<?php

declare(strict_types=1);

namespace Infection\Logger;

use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;

final class LineLoggerRegistry implements MutationTestingResultsLogger
{
    /**
     * @var FileLogger[]
     */
    private $loggers = [];

    public function __construct(
        OutputInterface $output,
        string $logFilePath,
        Filesystem $fileSystem,
        LineMutationTestingResultsLogger ...$lineLoggers
    ) {
        foreach ($lineLoggers as $logger) {
            $this->loggers[] = new FileLogger(
                $output,
                $logFilePath,
                $fileSystem,
                $logger
            );
        }
    }

    public function log(): void
    {
        foreach ($this->loggers as $logger) {
            $logger->log();
        }
    }
}
