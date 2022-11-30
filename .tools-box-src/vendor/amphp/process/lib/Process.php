<?php

namespace _HumbugBoxb47773b41c19\Amp\Process;

use _HumbugBoxb47773b41c19\Amp\Loop;
use _HumbugBoxb47773b41c19\Amp\Process\Internal\Posix\Runner as PosixProcessRunner;
use _HumbugBoxb47773b41c19\Amp\Process\Internal\ProcessHandle;
use _HumbugBoxb47773b41c19\Amp\Process\Internal\ProcessRunner;
use _HumbugBoxb47773b41c19\Amp\Process\Internal\ProcessStatus;
use _HumbugBoxb47773b41c19\Amp\Process\Internal\Windows\Runner as WindowsProcessRunner;
use _HumbugBoxb47773b41c19\Amp\Promise;
use function _HumbugBoxb47773b41c19\Amp\call;
final class Process
{
    private $processRunner;
    private $command;
    private $cwd = "";
    private $env = [];
    private $options;
    private $handle;
    private $pid;
    public function __construct($command, string $cwd = null, array $env = [], array $options = [])
    {
        $command = \is_array($command) ? \implode(" ", \array_map(__NAMESPACE__ . "\\escapeArguments", $command)) : (string) $command;
        $cwd = $cwd ?? "";
        $envVars = [];
        foreach ($env as $key => $value) {
            if (\is_array($value)) {
                throw new \Error("\$env cannot accept array values");
            }
            $envVars[(string) $key] = (string) $value;
        }
        $this->command = $command;
        $this->cwd = $cwd;
        $this->env = $envVars;
        $this->options = $options;
        $this->processRunner = Loop::getState(self::class);
        if ($this->processRunner === null) {
            $this->processRunner = IS_WINDOWS ? new WindowsProcessRunner() : new PosixProcessRunner();
            Loop::setState(self::class, $this->processRunner);
        }
    }
    public function __destruct()
    {
        if ($this->handle !== null) {
            $this->processRunner->destroy($this->handle);
        }
    }
    public function __clone()
    {
        throw new \Error("Cloning is not allowed!");
    }
    public function start() : Promise
    {
        if ($this->handle) {
            throw new StatusError("Process has already been started.");
        }
        return call(function () {
            $this->handle = $this->processRunner->start($this->command, $this->cwd, $this->env, $this->options);
            return $this->pid = (yield $this->handle->pidDeferred->promise());
        });
    }
    public function join() : Promise
    {
        if (!$this->handle) {
            throw new StatusError("Process has not been started.");
        }
        return $this->processRunner->join($this->handle);
    }
    public function kill()
    {
        if (!$this->isRunning()) {
            throw new StatusError("Process is not running.");
        }
        $this->processRunner->kill($this->handle);
    }
    public function signal(int $signo)
    {
        if (!$this->isRunning()) {
            throw new StatusError("Process is not running.");
        }
        $this->processRunner->signal($this->handle, $signo);
    }
    public function getPid() : int
    {
        if (!$this->pid) {
            throw new StatusError("Process has not been started or has not completed starting.");
        }
        return $this->pid;
    }
    public function getCommand() : string
    {
        return $this->command;
    }
    public function getWorkingDirectory() : string
    {
        if ($this->cwd === "") {
            return \getcwd() ?: "";
        }
        return $this->cwd;
    }
    public function getEnv() : array
    {
        return $this->env;
    }
    public function getOptions() : array
    {
        return $this->options;
    }
    public function isRunning() : bool
    {
        return $this->handle && $this->handle->status !== ProcessStatus::ENDED;
    }
    public function getStdin() : ProcessOutputStream
    {
        if (!$this->handle || $this->handle->status === ProcessStatus::STARTING) {
            throw new StatusError("Process has not been started or has not completed starting.");
        }
        return $this->handle->stdin;
    }
    public function getStdout() : ProcessInputStream
    {
        if (!$this->handle || $this->handle->status === ProcessStatus::STARTING) {
            throw new StatusError("Process has not been started or has not completed starting.");
        }
        return $this->handle->stdout;
    }
    public function getStderr() : ProcessInputStream
    {
        if (!$this->handle || $this->handle->status === ProcessStatus::STARTING) {
            throw new StatusError("Process has not been started or has not completed starting.");
        }
        return $this->handle->stderr;
    }
    public function __debugInfo() : array
    {
        return ['command' => $this->getCommand(), 'cwd' => $this->getWorkingDirectory(), 'env' => $this->getEnv(), 'options' => $this->getOptions(), 'pid' => $this->pid, 'status' => $this->handle ? $this->handle->status : -1];
    }
}
