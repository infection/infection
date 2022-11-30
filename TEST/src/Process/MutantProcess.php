<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\Process;

use Closure;
use _HumbugBox9658796bb9f0\Infection\Mutant\Mutant;
use _HumbugBox9658796bb9f0\Infection\Process\Runner\ProcessBearer;
use _HumbugBox9658796bb9f0\Symfony\Component\Process\Process;
class MutantProcess implements ProcessBearer
{
    private Closure $callback;
    private bool $timedOut = \false;
    public function __construct(private Process $process, private Mutant $mutant)
    {
        $this->callback = static function () : void {
        };
    }
    public function getProcess() : Process
    {
        return $this->process;
    }
    public function getMutant() : Mutant
    {
        return $this->mutant;
    }
    public function markAsTimedOut() : void
    {
        $this->timedOut = \true;
    }
    public function isTimedOut() : bool
    {
        return $this->timedOut;
    }
    /**
     * @param Closure(): void $callback
     */
    public function registerTerminateProcessClosure(Closure $callback) : void
    {
        $this->callback = $callback;
    }
    public function terminateProcess() : void
    {
        ($this->callback)();
    }
}
