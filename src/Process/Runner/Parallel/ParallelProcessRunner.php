<?php

declare(strict_types=1);

namespace Infection\Process\Runner\Parallel;

use Infection\Process\MutantProcess;
use Symfony\Component\Process\Exception\ProcessTimedOutException;

class ParallelProcessRunner
{
    /**
     * @var MutantProcess[]
     */
    protected $processes = [];

    protected $timeouts = [];

    public function __construct($processes)
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
}
