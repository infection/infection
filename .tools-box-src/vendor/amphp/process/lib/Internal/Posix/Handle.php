<?php

namespace _HumbugBoxb47773b41c19\Amp\Process\Internal\Posix;

use _HumbugBoxb47773b41c19\Amp\Deferred;
use _HumbugBoxb47773b41c19\Amp\Loop;
use _HumbugBoxb47773b41c19\Amp\Process\Internal\ProcessHandle;
final class Handle extends ProcessHandle
{
    public function __construct()
    {
        $this->pidDeferred = new Deferred();
        $this->joinDeferred = new Deferred();
        $this->originalParentPid = \getmypid();
    }
    public $joinDeferred;
    public $proc;
    public $extraDataPipe;
    public $extraDataPipeWatcher;
    public $extraDataPipeStartWatcher;
    public $originalParentPid;
    public $shellPid;
    public function wait()
    {
        if ($this->shellPid === 0) {
            return;
        }
        $pid = $this->shellPid;
        $this->shellPid = 0;
        Loop::unreference(Loop::repeat(100, static function (string $watcherId) use($pid) {
            if (!\extension_loaded('pcntl') || \pcntl_waitpid($pid, $status, \WNOHANG) !== 0) {
                Loop::cancel($watcherId);
            }
        }));
    }
    public function __destruct()
    {
        $this->wait();
    }
}
