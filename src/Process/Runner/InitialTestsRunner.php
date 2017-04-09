<?php

declare(strict_types=1);

namespace Infection\Process\Runner;

use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

class InitialTestsRunner
{
    /**
     * @var Process
     */
    private $process;

    /**
     * @var OutputInterface
     */
    private $output;

    /**
     * @param Process $process
     * @param OutputInterface $output
     */
    public function __construct(Process $process, OutputInterface $output)
    {
        $this->process = $process;
        $this->output = $output;
    }

    // TODO extract output logic from here
    public function run() : Result
    {
        $process = $this->process;
        $progressBar = new ProgressBar($this->output);
        $progressBar->setFormat('verbose');

        $process->run(function ($type) use ($process, $progressBar) {
            if ($process::ERR === $type) {
                $process->stop();
            }

            // TODO parse PHPUnit output and add if (!ok) {stop()}

            $progressBar->advance();
        });
        $progressBar->finish();
        $this->output->writeln('');

        return new Result($process);
    }
}