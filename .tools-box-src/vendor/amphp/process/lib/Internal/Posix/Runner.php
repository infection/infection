<?php

namespace _HumbugBoxb47773b41c19\Amp\Process\Internal\Posix;

use _HumbugBoxb47773b41c19\Amp\ByteStream\ResourceInputStream;
use _HumbugBoxb47773b41c19\Amp\ByteStream\ResourceOutputStream;
use _HumbugBoxb47773b41c19\Amp\Deferred;
use _HumbugBoxb47773b41c19\Amp\Loop;
use _HumbugBoxb47773b41c19\Amp\Process\Internal\ProcessHandle;
use _HumbugBoxb47773b41c19\Amp\Process\Internal\ProcessRunner;
use _HumbugBoxb47773b41c19\Amp\Process\Internal\ProcessStatus;
use _HumbugBoxb47773b41c19\Amp\Process\ProcessException;
use _HumbugBoxb47773b41c19\Amp\Process\ProcessInputStream;
use _HumbugBoxb47773b41c19\Amp\Process\ProcessOutputStream;
use _HumbugBoxb47773b41c19\Amp\Promise;
final class Runner implements ProcessRunner
{
    const FD_SPEC = [["pipe", "r"], ["pipe", "w"], ["pipe", "w"], ["pipe", "w"]];
    private static $fdPath;
    public static function onProcessEndExtraDataPipeReadable($watcher, $stream, Handle $handle)
    {
        Loop::cancel($watcher);
        $handle->extraDataPipeWatcher = null;
        $handle->status = ProcessStatus::ENDED;
        if (!\is_resource($stream) || \feof($stream)) {
            $handle->joinDeferred->fail(new ProcessException("Process ended unexpectedly"));
        } else {
            $handle->joinDeferred->resolve((int) \rtrim(@\stream_get_contents($stream)));
        }
        $handle->wait();
    }
    public static function onProcessStartExtraDataPipeReadable($watcher, $stream, $data)
    {
        Loop::cancel($watcher);
        $pid = \rtrim(@\fgets($stream));
        list($handle, $pipes, $deferreds) = $data;
        if (!$pid || !\is_numeric($pid)) {
            $error = new ProcessException("Could not determine PID");
            $handle->pidDeferred->fail($error);
            foreach ($deferreds as $deferred) {
                $deferred->fail($error);
            }
            if ($handle->status < ProcessStatus::ENDED) {
                $handle->status = ProcessStatus::ENDED;
                $handle->joinDeferred->fail($error);
            }
            return;
        }
        $handle->status = ProcessStatus::RUNNING;
        $handle->pidDeferred->resolve((int) $pid);
        $deferreds[0]->resolve($pipes[0]);
        $deferreds[1]->resolve($pipes[1]);
        $deferreds[2]->resolve($pipes[2]);
        if ($handle->extraDataPipeWatcher !== null) {
            Loop::enable($handle->extraDataPipeWatcher);
        }
    }
    public function start(string $command, string $cwd = null, array $env = [], array $options = []) : ProcessHandle
    {
        $command = \sprintf('{ (%s) <&3 3<&- 3>/dev/null & } 3<&0; trap "" INT TERM QUIT HUP;' . 'pid=$!; echo $pid >&3; wait $pid; RC=$?; echo $RC >&3; exit $RC', $command);
        $handle = new Handle();
        $handle->proc = @\proc_open($command, $this->generateFds(), $pipes, $cwd ?: null, $env ?: null, $options);
        if (!\is_resource($handle->proc)) {
            $message = "Could not start process";
            if ($error = \error_get_last()) {
                $message .= \sprintf(" Errno: %d; %s", $error["type"], $error["message"]);
            }
            throw new ProcessException($message);
        }
        $status = \proc_get_status($handle->proc);
        if (!$status) {
            \proc_close($handle->proc);
            throw new ProcessException("Could not get process status");
        }
        $handle->shellPid = \proc_get_status($handle->proc)['pid'];
        $stdinDeferred = new Deferred();
        $handle->stdin = new ProcessOutputStream($stdinDeferred->promise());
        $stdoutDeferred = new Deferred();
        $handle->stdout = new ProcessInputStream($stdoutDeferred->promise());
        $stderrDeferred = new Deferred();
        $handle->stderr = new ProcessInputStream($stderrDeferred->promise());
        $handle->extraDataPipe = $pipes[3];
        \stream_set_blocking($pipes[3], \false);
        $handle->extraDataPipeStartWatcher = Loop::onReadable($pipes[3], [self::class, 'onProcessStartExtraDataPipeReadable'], [$handle, [new ResourceOutputStream($pipes[0]), new ResourceInputStream($pipes[1]), new ResourceInputStream($pipes[2])], [$stdinDeferred, $stdoutDeferred, $stderrDeferred]]);
        $handle->extraDataPipeWatcher = Loop::onReadable($pipes[3], [self::class, 'onProcessEndExtraDataPipeReadable'], $handle);
        Loop::unreference($handle->extraDataPipeWatcher);
        Loop::disable($handle->extraDataPipeWatcher);
        return $handle;
    }
    private function generateFds() : array
    {
        if (self::$fdPath === null) {
            self::$fdPath = \file_exists("/dev/fd") ? "/dev/fd" : "/proc/self/fd";
        }
        $fdList = @\scandir(self::$fdPath, \SCANDIR_SORT_NONE);
        if ($fdList === \false) {
            throw new ProcessException("Unable to list open file descriptors");
        }
        $fdList = \array_filter($fdList, function (string $path) : bool {
            return $path !== "." && $path !== "..";
        });
        $fds = [];
        foreach ($fdList as $id) {
            $fds[(int) $id] = ["file", "/dev/null", "r"];
        }
        return self::FD_SPEC + $fds;
    }
    public function join(ProcessHandle $handle) : Promise
    {
        if ($handle->extraDataPipeWatcher !== null) {
            Loop::reference($handle->extraDataPipeWatcher);
        }
        return $handle->joinDeferred->promise();
    }
    public function kill(ProcessHandle $handle)
    {
        if ($handle->extraDataPipeWatcher !== null) {
            Loop::cancel($handle->extraDataPipeWatcher);
            $handle->extraDataPipeWatcher = null;
        }
        if ($handle->extraDataPipeStartWatcher !== null) {
            Loop::cancel($handle->extraDataPipeStartWatcher);
            $handle->extraDataPipeStartWatcher = null;
        }
        if (!\proc_terminate($handle->proc, 9)) {
            throw new ProcessException("Terminating process failed");
        }
        $handle->pidDeferred->promise()->onResolve(function ($error, $pid) {
            if ($error) {
                return;
            }
            @\posix_kill($pid, 9);
        });
        if ($handle->status < ProcessStatus::ENDED) {
            $handle->status = ProcessStatus::ENDED;
            $handle->joinDeferred->fail(new ProcessException("The process was killed"));
        }
        $this->free($handle);
    }
    public function signal(ProcessHandle $handle, int $signo)
    {
        $handle->pidDeferred->promise()->onResolve(function ($error, $pid) use($signo) {
            if ($error) {
                return;
            }
            @\posix_kill($pid, $signo);
        });
    }
    public function destroy(ProcessHandle $handle)
    {
        if ($handle->status < ProcessStatus::ENDED && \getmypid() === $handle->originalParentPid) {
            try {
                $this->kill($handle);
                return;
            } catch (ProcessException $e) {
            }
        }
        $this->free($handle);
    }
    private function free(Handle $handle)
    {
        if ($handle->extraDataPipeWatcher !== null) {
            Loop::cancel($handle->extraDataPipeWatcher);
            $handle->extraDataPipeWatcher = null;
        }
        if ($handle->extraDataPipeStartWatcher !== null) {
            Loop::cancel($handle->extraDataPipeStartWatcher);
            $handle->extraDataPipeStartWatcher = null;
        }
        if (\is_resource($handle->extraDataPipe)) {
            \fclose($handle->extraDataPipe);
        }
        $handle->stdin->close();
        $handle->stdout->close();
        $handle->stderr->close();
        if (\is_resource($handle->proc)) {
            \proc_close($handle->proc);
        }
    }
}
