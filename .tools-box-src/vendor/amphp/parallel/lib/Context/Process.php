<?php

namespace _HumbugBoxb47773b41c19\Amp\Parallel\Context;

use _HumbugBoxb47773b41c19\Amp\Loop;
use _HumbugBoxb47773b41c19\Amp\Parallel\Sync\ChannelException;
use _HumbugBoxb47773b41c19\Amp\Parallel\Sync\ChannelledSocket;
use _HumbugBoxb47773b41c19\Amp\Parallel\Sync\ExitResult;
use _HumbugBoxb47773b41c19\Amp\Parallel\Sync\SynchronizationError;
use _HumbugBoxb47773b41c19\Amp\Process\Process as BaseProcess;
use _HumbugBoxb47773b41c19\Amp\Process\ProcessInputStream;
use _HumbugBoxb47773b41c19\Amp\Process\ProcessOutputStream;
use _HumbugBoxb47773b41c19\Amp\Promise;
use _HumbugBoxb47773b41c19\Amp\TimeoutException;
use function _HumbugBoxb47773b41c19\Amp\call;
final class Process implements Context
{
    const SCRIPT_PATH = __DIR__ . "/Internal/process-runner.php";
    const KEY_LENGTH = 32;
    private static $pharScriptPath;
    private static $pharCopy;
    private static $binaryPath;
    private $hub;
    private $process;
    private $channel;
    public static function run($script, string $cwd = null, array $env = [], string $binary = null) : Promise
    {
        $process = new self($script, $cwd, $env, $binary);
        return call(function () use($process) : \Generator {
            (yield $process->start());
            return $process;
        });
    }
    public function __construct($script, string $cwd = null, array $env = [], string $binary = null)
    {
        $this->hub = Loop::getState(self::class);
        if (!$this->hub instanceof Internal\ProcessHub) {
            $this->hub = new Internal\ProcessHub();
            Loop::setState(self::class, $this->hub);
        }
        $options = ["html_errors" => "0", "display_errors" => "0", "log_errors" => "1"];
        if ($binary === null) {
            if (\PHP_SAPI === "cli") {
                $binary = \PHP_BINARY;
            } else {
                $binary = self::$binaryPath ?? self::locateBinary();
            }
        } elseif (!\is_executable($binary)) {
            throw new \Error(\sprintf("The PHP binary path '%s' was not found or is not executable", $binary));
        }
        if (\strpos(self::SCRIPT_PATH, "phar://") === 0) {
            if (self::$pharScriptPath) {
                $scriptPath = self::$pharScriptPath;
            } else {
                $path = \dirname(self::SCRIPT_PATH);
                if (\substr(\Phar::running(\false), -5) !== ".phar") {
                    self::$pharCopy = \sys_get_temp_dir() . "/phar-" . \bin2hex(\random_bytes(10)) . ".phar";
                    \copy(\Phar::running(\false), self::$pharCopy);
                    \register_shutdown_function(static function () : void {
                        @\unlink(self::$pharCopy);
                    });
                    $path = "phar://" . self::$pharCopy . "/" . \substr($path, \strlen(\Phar::running(\true)));
                }
                $contents = \file_get_contents(self::SCRIPT_PATH);
                $contents = \str_replace("__DIR__", \var_export($path, \true), $contents);
                $suffix = \bin2hex(\random_bytes(10));
                self::$pharScriptPath = $scriptPath = \sys_get_temp_dir() . "/amp-process-runner-" . $suffix . ".php";
                \file_put_contents($scriptPath, $contents);
                \register_shutdown_function(static function () : void {
                    @\unlink(self::$pharScriptPath);
                });
            }
            if (isset(self::$pharCopy) && \is_array($script) && isset($script[0])) {
                $script[0] = "phar://" . self::$pharCopy . \substr($script[0], \strlen(\Phar::running(\true)));
            }
        } else {
            $scriptPath = self::SCRIPT_PATH;
        }
        if (\is_array($script)) {
            $script = \implode(" ", \array_map("escapeshellarg", $script));
        } else {
            $script = \escapeshellarg($script);
        }
        $command = \implode(" ", [\escapeshellarg($binary), $this->formatOptions($options), \escapeshellarg($scriptPath), $this->hub->getUri(), $script]);
        $this->process = new BaseProcess($command, $cwd, $env);
    }
    private static function locateBinary() : string
    {
        $executable = \strncasecmp(\PHP_OS, "WIN", 3) === 0 ? "php.exe" : "php";
        $paths = \array_filter(\explode(\PATH_SEPARATOR, \getenv("PATH")));
        $paths[] = \PHP_BINDIR;
        $paths = \array_unique($paths);
        foreach ($paths as $path) {
            $path .= \DIRECTORY_SEPARATOR . $executable;
            if (\is_executable($path)) {
                return self::$binaryPath = $path;
            }
        }
        throw new \Error("Could not locate PHP executable binary");
    }
    private function formatOptions(array $options) : string
    {
        $result = [];
        foreach ($options as $option => $value) {
            $result[] = \sprintf("-d%s=%s", $option, $value);
        }
        return \implode(" ", $result);
    }
    private function __clone()
    {
    }
    public function start() : Promise
    {
        return call(function () : \Generator {
            try {
                $pid = (yield $this->process->start());
                (yield $this->process->getStdin()->write($this->hub->generateKey($pid, self::KEY_LENGTH)));
                $this->channel = (yield $this->hub->accept($pid));
                return $pid;
            } catch (\Throwable $exception) {
                if ($this->isRunning()) {
                    $this->kill();
                }
                throw new ContextException("Starting the process failed", 0, $exception);
            }
        });
    }
    public function isRunning() : bool
    {
        return $this->process->isRunning();
    }
    public function receive() : Promise
    {
        if ($this->channel === null) {
            throw new StatusError("The process has not been started");
        }
        return call(function () : \Generator {
            try {
                $data = (yield $this->channel->receive());
            } catch (ChannelException $e) {
                throw new ContextException("The process stopped responding, potentially due to a fatal error or calling exit", 0, $e);
            }
            if ($data instanceof ExitResult) {
                $data = $data->getResult();
                throw new SynchronizationError(\sprintf('Process unexpectedly exited with result of type: %s', \is_object($data) ? \get_class($data) : \gettype($data)));
            }
            return $data;
        });
    }
    public function send($data) : Promise
    {
        if ($this->channel === null) {
            throw new StatusError("The process has not been started");
        }
        if ($data instanceof ExitResult) {
            throw new \Error("Cannot send exit result objects");
        }
        return call(function () use($data) : \Generator {
            try {
                return (yield $this->channel->send($data));
            } catch (ChannelException $e) {
                if ($this->channel === null) {
                    throw new ContextException("The process stopped responding, potentially due to a fatal error or calling exit", 0, $e);
                }
                try {
                    $data = (yield Promise\timeout($this->join(), 100));
                } catch (ContextException|ChannelException|TimeoutException $ex) {
                    if ($this->isRunning()) {
                        $this->kill();
                    }
                    throw new ContextException("The process stopped responding, potentially due to a fatal error or calling exit", 0, $e);
                }
                throw new SynchronizationError(\sprintf('Process unexpectedly exited with result of type: %s', \is_object($data) ? \get_class($data) : \gettype($data)), 0, $e);
            }
        });
    }
    public function join() : Promise
    {
        if ($this->channel === null) {
            throw new StatusError("The process has not been started");
        }
        return call(function () : \Generator {
            try {
                $data = (yield $this->channel->receive());
            } catch (\Throwable $exception) {
                if ($this->isRunning()) {
                    $this->kill();
                }
                throw new ContextException("Failed to receive result from process", 0, $exception);
            }
            if (!$data instanceof ExitResult) {
                if ($this->isRunning()) {
                    $this->kill();
                }
                throw new SynchronizationError("Did not receive an exit result from process");
            }
            $this->channel->close();
            $code = (yield $this->process->join());
            if ($code !== 0) {
                throw new ContextException(\sprintf("Process exited with code %d", $code));
            }
            return $data->getResult();
        });
    }
    public function signal(int $signo) : void
    {
        $this->process->signal($signo);
    }
    public function getPid() : int
    {
        return $this->process->getPid();
    }
    public function getStdin() : ProcessOutputStream
    {
        return $this->process->getStdin();
    }
    public function getStdout() : ProcessInputStream
    {
        return $this->process->getStdout();
    }
    public function getStderr() : ProcessInputStream
    {
        return $this->process->getStderr();
    }
    public function kill() : void
    {
        $this->process->kill();
        if ($this->channel !== null) {
            $this->channel->close();
        }
    }
}
