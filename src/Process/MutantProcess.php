<?php

declare(strict_types=1);

namespace Infection\Process;

use Infection\Mutant\Mutant;
use Symfony\Component\Process\Process;

class MutantProcess
{
    /**
     * @var Process
     */
    private $process;
    /**
     * @var Mutant
     */
    private $mutant;

    public function __construct(Process $process, Mutant $mutant)
    {
        $this->process = $process;
        $this->mutant = $mutant;
    }

    /**
     * @return Process
     */
    public function getProcess(): Process
    {
        return $this->process;
    }

    /**
     * @return Mutant
     */
    public function getMutant(): Mutant
    {
        return $this->mutant;
    }
}