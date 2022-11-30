<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\Process\Runner;

use _HumbugBox9658796bb9f0\Symfony\Component\Process\Process;
interface ProcessBearer
{
    public function getProcess() : Process;
    public function markAsTimedOut() : void;
    public function terminateProcess() : void;
}
