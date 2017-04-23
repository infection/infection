<?php

declare(strict_types=1);

namespace Infection\Process\Runner;

use Infection\TestFramework\Coverage\CodeCoverageData;
use Symfony\Component\Process\Process;

class Result
{
    /**
     * @var Process
     */
    private $process;

    /**
     * @var CodeCoverageData
     */
    private $codeCoverageData;

    public function __construct(Process $process, CodeCoverageData $codeCoverageData)
    {
        $this->process = $process;
        $this->codeCoverageData = $codeCoverageData;
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

    /**
     * @return CodeCoverageData
     */
    public function getCodeCoverageData(): CodeCoverageData
    {
        return $this->codeCoverageData;
    }
}