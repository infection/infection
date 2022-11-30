<?php

namespace _HumbugBoxb47773b41c19\Amp\Parallel\Context\Internal;

use _HumbugBoxb47773b41c19\Amp\Parallel\Context\Process;
use _HumbugBoxb47773b41c19\Amp\Parallel\Sync;
use _HumbugBoxb47773b41c19\Amp\Promise;
use function _HumbugBoxb47773b41c19\Amp\call;
use function _HumbugBoxb47773b41c19\Amp\getCurrentTime;
\define("AMP_CONTEXT", "process");
\define("AMP_CONTEXT_ID", \getmypid());
if (\function_exists("cli_set_process_title")) {
    @\cli_set_process_title("amp-process");
}
(function () : void {
    $paths = [\dirname(__DIR__, 5) . "/autoload.php", \dirname(__DIR__, 3) . "/vendor/autoload.php"];
    foreach ($paths as $path) {
        if (\file_exists($path)) {
            $autoloadPath = $path;
            break;
        }
    }
    if (!isset($autoloadPath)) {
        \trigger_error("Could not locate autoload.php in any of the following files: " . \implode(", ", $paths), \E_USER_ERROR);
        exit(1);
    }
    require $autoloadPath;
})();
(function () use($argc, $argv) : void {
    --$argc;
    \array_shift($argv);
    if (!isset($argv[0])) {
        \trigger_error("No socket path provided", \E_USER_ERROR);
        exit(1);
    }
    --$argc;
    $uri = \array_shift($argv);
    $key = "";
    do {
        if (($chunk = \fread(\STDIN, Process::KEY_LENGTH)) === \false || \feof(\STDIN)) {
            \trigger_error("Could not read key from parent", \E_USER_ERROR);
            exit(1);
        }
        $key .= $chunk;
    } while (\strlen($key) < Process::KEY_LENGTH);
    $connectStart = getCurrentTime();
    while (!($socket = \stream_socket_client($uri, $errno, $errstr, 5, \STREAM_CLIENT_CONNECT))) {
        if (getCurrentTime() < $connectStart + 5000) {
            \trigger_error("Could not connect to IPC socket", \E_USER_ERROR);
            exit(1);
        }
        \usleep(50 * 1000);
    }
    $channel = new Sync\ChannelledSocket($socket, $socket);
    try {
        Promise\wait($channel->send($key));
    } catch (\Throwable $exception) {
        \trigger_error("Could not send key to parent", \E_USER_ERROR);
        exit(1);
    }
    try {
        if (!isset($argv[0])) {
            throw new \Error("No script path given");
        }
        if (!\is_file($argv[0])) {
            throw new \Error(\sprintf("No script found at '%s' (be sure to provide the full path to the script)", $argv[0]));
        }
        try {
            $callable = (function () use($argc, $argv) : callable {
                return require $argv[0];
            })();
        } catch (\TypeError $exception) {
            throw new \Error(\sprintf("Script '%s' did not return a callable function", $argv[0]), 0, $exception);
        } catch (\ParseError $exception) {
            throw new \Error(\sprintf("Script '%s' contains a parse error: " . $exception->getMessage(), $argv[0]), 0, $exception);
        }
        $result = new Sync\ExitSuccess(Promise\wait(call($callable, $channel)));
    } catch (\Throwable $exception) {
        $result = new Sync\ExitFailure($exception);
    }
    try {
        Promise\wait(call(function () use($channel, $result) : \Generator {
            try {
                (yield $channel->send($result));
            } catch (Sync\SerializationException $exception) {
                (yield $channel->send(new Sync\ExitFailure($exception)));
            }
        }));
    } catch (\Throwable $exception) {
        \trigger_error("Could not send result to parent; be sure to shutdown the child before ending the parent", \E_USER_ERROR);
        exit(1);
    }
})();
