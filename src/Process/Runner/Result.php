<?php

declare(strict_types=1);

namespace Infection\Process\Runner;

use Symfony\Component\Process\Process;

class Result
{
    /**
     * @var Process
     */
    private $process;

    public function __construct(Process $process)
    {
        $this->process = $process;
    }

    public function isSuccessful() : bool
    {
        return $this->process->isSuccessful();
    }

    public function getExitCode() : int
    {
        return $this->process->getExitCode();
    }

    public function getErrorOutput()
    {
        return $this->process->getErrorOutput();
    }

    public function getExitCodeText()
    {
        return $this->process->getExitCodeText();
    }
}