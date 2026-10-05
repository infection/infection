<?php

declare(strict_types=1);

/*
 * This worker runs a PHP script, such as vendor/bin/phpunit, in a forked child for each request.
 *
 * Before the first fork the worker loads the Composer autoload file of the script, as the script
 * itself does before the test framework bootstrap. After each run the child reports the vendor
 * files it loaded, and the worker loads these files before the next fork.
 *
 * Request, one line on file descriptor 3 (STDIN stays free for the script):
 *   {"argv": ["/path/to/vendor/bin/phpunit", "--configuration", "..."], "env": {"TEST_TOKEN": 1}, "timeout": 5.0,
 *    "autoload": "/path/to/vendor/autoload.php", "source": "/path/to/src/Mutated.php"}
 * Response on STDOUT: the output of the child, then "\0FORK <exit code> <timed out>\n"
 */

namespace Infection\ForkWorker;

use function array_diff;
use function array_filter;
use function array_values;
use function count;
use function dirname;
use function fclose;
use function feof;
use function fgets;
use function fopen;
use function fread;
use function fwrite;
use function get_included_files;
use function in_array;
use function json_decode;
use function json_encode;
use function max;
use function microtime;
use function pcntl_exec;
use function pcntl_fork;
use function pcntl_waitpid;
use function pcntl_wexitstatus;
use function pcntl_wifexited;
use function pcntl_wtermsig;
use const PHP_BINARY;
use function posix_kill;
use function printf;
use function putenv;
use function realpath;
use function register_shutdown_function;
use const SIGKILL;
use function str_ends_with;
use function str_starts_with;
use function stream_select;
use function stream_socket_pair;
use const STREAM_PF_UNIX;
use const STREAM_SOCK_STREAM;

const KILLED_EXIT_CODE_BASE = 128;

/**
 * @param array<string, string|int> $env
 */
function set_environment(array $env): void
{
    foreach ($env as $name => $value) {
        putenv($name . '=' . $value);
        $_ENV[$name] = $_SERVER[$name] = (string) $value;
    }
}

/**
 * @param list<string> $known
 * @return list<string> the vendor files that the current process loaded and the worker did not
 */
function new_vendor_files(string $vendorDir, array $known): array
{
    $files = array_filter(
        array_diff(get_included_files(), $known),
        static fn (string $file) => str_starts_with($file, $vendorDir . '/') && str_ends_with($file, '.php'),
    );

    return array_values($files);
}

/**
 * Runs the requested script in the current (child) process.
 *
 * @param array{argv: list<string>, env: array<string, string|int>, source: string} $request
 * @param resource $report
 */
function run_script(array $request, string $vendorDir, $report): never
{
    $arguments = $request['argv'];
    $known = get_included_files();

    set_environment($request['env']);

    // A file that the worker loaded cannot be replaced with its mutant: run the script in a new PHP process.
    if (in_array(realpath($request['source']), $known, true)) {
        pcntl_exec(PHP_BINARY, $arguments);
    }

    register_shutdown_function(static function () use ($vendorDir, $known, $report): void {
        fwrite($report, (string) json_encode(new_vendor_files($vendorDir, $known)));
    });

    $GLOBALS['argv'] = $_SERVER['argv'] = $arguments;
    $GLOBALS['argc'] = $_SERVER['argc'] = count($arguments);

    require $arguments[0];

    exit(0);
}

/**
 * Reads the report of a child until the child exits. Kills the child at the deadline.
 *
 * @param resource $report
 * @return array{string, bool} the report and the timeout flag
 */
function await_child(int $pid, $report, float $deadline): array
{
    $data = '';

    while (!feof($report)) {
        $remaining = max(0, $deadline - microtime(true));
        $read = [$report];
        $write = $except = null;

        if (stream_select($read, $write, $except, (int) $remaining, (int) (($remaining - (int) $remaining) * 1e6)) < 1) {
            posix_kill($pid, SIGKILL);

            return ['', true];
        }

        $data .= fread($report, 65536);
    }

    return [$data, false];
}

/**
 * @param array{argv: list<string>, env: array<string, string|int>, timeout: float, source: string} $request
 * @return list<string> the vendor files to load before the next request
 */
function handle(array $request, string $vendorDir): array
{
    [$reader, $writer] = stream_socket_pair(STREAM_PF_UNIX, STREAM_SOCK_STREAM, 0);

    $pid = pcntl_fork();

    if ($pid === 0) {
        fclose($reader);
        run_script($request, $vendorDir, $writer);
    }

    fclose($writer);

    [$report, $timedOut] = await_child($pid, $reader, microtime(true) + $request['timeout']);

    pcntl_waitpid($pid, $status);

    printf(
        "\0FORK %d %d\n",
        pcntl_wifexited($status) ? pcntl_wexitstatus($status) : KILLED_EXIT_CODE_BASE + pcntl_wtermsig($status),
        $timedOut,
    );

    return json_decode($report, true) ?? [];
}

/**
 * Loads the autoload file the same way the script does, with the environment of a run.
 *
 * @param array{env: array<string, string|int>, autoload: string} $request
 * @return string the vendor directory
 */
function load_autoload_file(array $request): string
{
    set_environment($request['env']);

    require $request['autoload'];

    return dirname((string) realpath($request['autoload']));
}

/**
 * @param list<string> $files
 */
function preload(array $files): void
{
    foreach ($files as $file) {
        require_once $file;
    }
}

$vendorDir = null;

$requests = fopen('php://fd/3', 'r');

while (false !== $line = fgets($requests)) {
    $request = json_decode($line, true);

    $vendorDir ??= load_autoload_file($request);

    preload(handle($request, $vendorDir));
}
