<?php

namespace _HumbugBoxb47773b41c19\Amp\Parallel\Worker\Internal;

use _HumbugBoxb47773b41c19\Amp\ByteStream;
use _HumbugBoxb47773b41c19\Amp\Parallel\Context\Context;
use _HumbugBoxb47773b41c19\Amp\Parallel\Context\Process;
use _HumbugBoxb47773b41c19\Amp\Promise;
use function _HumbugBoxb47773b41c19\Amp\call;
class WorkerProcess implements Context
{
    private $process;
    public function __construct($script, array $env = [], string $binary = null)
    {
        $this->process = new Process($script, null, $env, $binary);
    }
    public function receive() : Promise
    {
        return $this->process->receive();
    }
    public function send($data) : Promise
    {
        return $this->process->send($data);
    }
    public function isRunning() : bool
    {
        return $this->process->isRunning();
    }
    public function start() : Promise
    {
        return call(function () {
            $result = (yield $this->process->start());
            $stdout = $this->process->getStdout();
            $stdout->unreference();
            $stderr = $this->process->getStderr();
            $stderr->unreference();
            ByteStream\pipe($stdout, ByteStream\getStdout());
            ByteStream\pipe($stderr, ByteStream\getStderr());
            return $result;
        });
    }
    public function kill() : void
    {
        if ($this->process->isRunning()) {
            $this->process->kill();
        }
    }
    public function join() : Promise
    {
        return $this->process->join();
    }
}
