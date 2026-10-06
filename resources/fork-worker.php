<?php

declare(strict_types=1);

/*
 * This worker runs a PHP script, such as vendor/bin/phpunit, in a forked child for each request.
 * The caller makes sure that the script is a PHP script. The worker loads no other code:
 * each child starts with a clean PHP state, without the start of a new PHP process.
 *
 * Descriptors:
 *   0     /dev/null
 *   1, 2  the output of the child
 *   3     requests, a JSON line each: {"argv": ["/path/to/vendor/bin/phpunit", "--configuration", "..."], "env": {"TEST_TOKEN": 1}}
 *   4     results, a line each: the exit code of the child, or 128 plus the number of the signal that stopped the child
 *
 * The worker runs a single request at a time. Each child runs in its own process group. SIGTERM or SIGINT stops the worker
 * together with the process group of its current child. After a child exits, the worker kills the rest of its process group.
 */

namespace Infection\ForkWorker;

use function count;
use function fclose;
use function fgets;
use function fopen;
use function fwrite;
use function json_decode;
use function pcntl_fork;
use function pcntl_sigprocmask;
use function pcntl_sigwaitinfo;
use function pcntl_waitpid;
use function pcntl_wexitstatus;
use function pcntl_wifexited;
use function pcntl_wtermsig;
use function posix_kill;
use function posix_setpgid;
use function putenv;
use const SIG_BLOCK;
use const SIG_SETMASK;
use const SIG_UNBLOCK;
use const SIGCHLD;
use const SIGINT;
use const SIGKILL;
use const SIGTERM;

const SIGNAL_EXIT_CODE_BASE = 128;

/**
 * Sets the environment and the arguments of the requested script in the current (child) process.
 *
 * @param array{argv: list<string>, env: array<string, string|int>} $request
 */
function prepare(array $request): void
{
    foreach ($request['env'] as $name => $value) {
        putenv($name . '=' . $value);
        $_ENV[$name] = $_SERVER[$name] = (string) $value;
    }

    $GLOBALS['argv'] = $_SERVER['argv'] = $request['argv'];
    $GLOBALS['argc'] = $_SERVER['argc'] = count($request['argv']);
}

function exit_code(int $status): int
{
    return pcntl_wifexited($status)
        ? pcntl_wexitstatus($status)
        : SIGNAL_EXIT_CODE_BASE + pcntl_wtermsig($status);
}

const SIGNALS = [SIGCHLD, SIGTERM, SIGINT];

$requests = fopen('php://fd/3', 'r');
$results = fopen('php://fd/4', 'w');

while (false !== $line = fgets($requests)) {
    // The worker consumes the signals with pcntl_sigwaitinfo() until the child is reaped.
    pcntl_sigprocmask(SIG_BLOCK, SIGNALS);

    $child = pcntl_fork();

    if ($child === 0) {
        pcntl_sigprocmask(SIG_SETMASK, []);
        posix_setpgid(0, 0);
        fclose($requests);
        fclose($results);

        prepare(json_decode($line, true));

        // The script runs in the global scope, as the main script of a PHP process does.
        unset($requests, $results, $child, $line);

        require $argv[0];

        exit(0);
    }

    // Both processes set the group, so the group exists before any kill and before the script starts.
    posix_setpgid($child, $child);

    if (pcntl_sigwaitinfo(SIGNALS) !== SIGCHLD) {
        posix_kill(-$child, SIGKILL);
        exit(0);
    }

    pcntl_waitpid($child, $status);
    posix_kill(-$child, SIGKILL);

    pcntl_sigprocmask(SIG_UNBLOCK, SIGNALS);

    fwrite($results, exit_code($status) . "\n");
}
