<?php

declare(strict_types=1);

/*
 * This worker runs a PHP script, such as vendor/bin/phpunit, in a forked child for each request.
 * The worker loads only files from the Composer vendor directory: a child reports the vendor
 * files it loaded, and the worker loads these files before the next fork.
 *
 * Request, one line on STDIN:  {"argv": ["/path/to/vendor/bin/phpunit", "--configuration", "..."], "env": {"TEST_TOKEN": 1}, "timeout": 5.0}
 * Response on STDOUT:          the output of the child, then "\0FORK <exit code> <timed out>\n"
 */

namespace Infection\ForkWorker;

use function array_diff;
use function array_filter;
use function array_keys;
use function array_map;
use function array_values;
use Composer\Autoload\ClassLoader;
use function count;
use function dirname;
use function fclose;
use function feof;
use function fgets;
use function fread;
use function fwrite;
use function get_included_files;
use function is_file;
use function json_decode;
use function json_encode;
use function max;
use function microtime;
use function pcntl_fork;
use function pcntl_waitpid;
use function pcntl_wexitstatus;
use function pcntl_wifexited;
use function pcntl_wtermsig;
use function posix_kill;
use function printf;
use function putenv;
use function realpath;
use function register_shutdown_function;
use const SIGKILL;
use const STDIN;
use function str_ends_with;
use function str_starts_with;
use function stream_select;
use function stream_socket_pair;
use const STREAM_PF_UNIX;
use const STREAM_SOCK_STREAM;

const KILLED_EXIT_CODE_BASE = 128;

/**
 * Finds the Composer vendor directory that contains the given script.
 */
function find_vendor_dir(string $script): ?string
{
    $dir = dirname((string) realpath($script));

    while (!is_file($dir . '/composer/autoload_classmap.php') && $dir !== dirname($dir)) {
        $dir = dirname($dir);
    }

    return is_file($dir . '/composer/autoload_classmap.php') ? $dir : null;
}

/**
 * Makes vendor classes loadable for the time of a preload, without the project's own autoload files.
 */
function vendor_class_loader(string $vendorDir): ClassLoader
{
    require_once $vendorDir . '/composer/ClassLoader.php';

    $loader = new ClassLoader($vendorDir);
    $loader->addClassMap(require $vendorDir . '/composer/autoload_classmap.php');

    $psr4 = require $vendorDir . '/composer/autoload_psr4.php';
    array_map($loader->setPsr4(...), array_keys($psr4), $psr4);

    return $loader;
}

/**
 * @return array<string, string> the Composer "files" autoload entries from the vendor directory
 */
function vendor_autoload_files(string $vendorDir): array
{
    if (!is_file($vendorDir . '/composer/autoload_files.php')) {
        return [];
    }

    return array_filter(
        require $vendorDir . '/composer/autoload_files.php',
        static fn (string $file) => str_starts_with((string) realpath($file), $vendorDir . '/'),
    );
}

/**
 * Loads the Composer "files" autoload entries from the vendor directory.
 */
function preload_autoload_files(string $vendorDir): void
{
    foreach (vendor_autoload_files($vendorDir) as $identifier => $file) {
        // Composer skips a file with this flag, thus a child loads only the project's own files.
        $GLOBALS['__composer_autoload_files'][$identifier] = true;

        require_once $file;
    }
}

/**
 * @param list<string> $files
 */
function preload(string $vendorDir, array $files): void
{
    $loader = vendor_class_loader($vendorDir);
    $loader->register();

    foreach ($files as $file) {
        require_once $file;
    }

    $loader->unregister();
}

/**
 * @return list<string> the vendor files that the current process loaded and the worker did not
 */
function new_vendor_files(string $vendorDir, array $known): array
{
    $files = array_filter(
        get_included_files(),
        static fn (string $file) => str_starts_with($file, $vendorDir . '/')
            && str_ends_with($file, '.php')
            && !str_starts_with($file, $vendorDir . '/composer/')
            && $file !== $vendorDir . '/autoload.php',
    );

    return array_values(array_diff($files, $known));
}

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
 * Runs the requested script in the current (child) process.
 *
 * @param array{argv: list<string>, env: array<string, string|int>, timeout: float} $request
 * @param resource $report
 */
function run_script(array $request, ?string $vendorDir, $report): never
{
    $arguments = $request['argv'];

    set_environment($request['env']);

    if ($vendorDir !== null) {
        $known = get_included_files();

        register_shutdown_function(static function () use ($vendorDir, $known, $report): void {
            fwrite($report, (string) json_encode(new_vendor_files($vendorDir, $known)));
        });
    }

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
 * @param array{argv: list<string>, env: array<string, string|int>, timeout: float} $request
 * @return list<string> the vendor files to preload before the next request
 */
function handle(array $request, ?string $vendorDir): array
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

$vendorDir = null;

while (false !== $line = fgets(STDIN)) {
    $request = json_decode($line, true);

    if ($vendorDir === null && null !== $vendorDir = find_vendor_dir($request['argv'][0])) {
        preload_autoload_files($vendorDir);
    }

    $files = handle($request, $vendorDir);

    if ($files !== []) {
        preload($vendorDir, $files);
    }
}
