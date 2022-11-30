<?php

namespace _HumbugBoxb47773b41c19\Amp\Process\Internal\Windows;

use _HumbugBoxb47773b41c19\Amp\Deferred;
use _HumbugBoxb47773b41c19\Amp\Process\Internal\ProcessHandle;
final class Handle extends ProcessHandle
{
    public function __construct()
    {
        $this->joinDeferred = new Deferred();
        $this->pidDeferred = new Deferred();
    }
    public $joinDeferred;
    public $exitCodeWatcher;
    public $exitCodeRequested = \false;
    public $proc;
    public $wrapperPid;
    public $wrapperStderrPipe;
    public $sockets = [];
    public $stdioDeferreds;
    public $childPidWatcher;
    public $connectTimeoutWatcher;
    public $securityTokens;
}
