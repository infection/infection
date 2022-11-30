<?php

namespace _HumbugBoxb47773b41c19\Amp\Process\Internal;

use _HumbugBoxb47773b41c19\Amp\Process\ProcessException;
use _HumbugBoxb47773b41c19\Amp\Promise;
interface ProcessRunner
{
    public function start(string $command, string $cwd = null, array $env = [], array $options = []) : ProcessHandle;
    public function join(ProcessHandle $handle) : Promise;
    public function kill(ProcessHandle $handle);
    public function signal(ProcessHandle $handle, int $signo);
    public function destroy(ProcessHandle $handle);
}
