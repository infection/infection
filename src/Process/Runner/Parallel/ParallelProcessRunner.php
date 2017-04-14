<?php

declare(strict_types=1);

namespace Infection\Process\Runner;

use Symfony\Component\Process\Exception\ProcessTimedOutException;
use Symfony\Component\Process\Process;

class ParallelProcessRunner
{
    /**
     * @var Process[]
     */
    protected $processes = [];

    protected $timeouts = [];

    public function __construct(Process ...$processes)
    {
        $this->processes = $processes;
    }

    public function run()
    {
        foreach ($this->processes as $process) {
            $process->getProcess()->start();
        }
        usleep(1000);
        while ($this->stillRunning()) {
            usleep(1000);
        }
        $this->processes = [];
    }

    public function stillRunning()
    {
        foreach ($this->processes as $index => $process) {
            try {
                $process->getProcess()->checkTimeout();
            } catch (ProcessTimedOutException $e) {
                $process->markTimeout();
            }
            if ($process->getProcess()->isRunning()) {
                return true;
            }
        }
    }

    public function reset()
    {
        $this->processes = [];
    }
}
