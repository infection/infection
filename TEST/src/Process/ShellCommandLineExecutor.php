<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\Process;

use _HumbugBox9658796bb9f0\Symfony\Component\Process\Process;
use function trim;
class ShellCommandLineExecutor
{
    public function execute(array $command) : string
    {
        return trim((new Process($command))->mustRun()->getOutput());
    }
}
