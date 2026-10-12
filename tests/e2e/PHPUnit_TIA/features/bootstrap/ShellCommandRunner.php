<?php

declare(strict_types=1);

namespace Infection\E2ETests\PHPUnitTIA\Behat;

use Closure;
use Stringable;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Exception\ProcessSignaledException;
use Symfony\Component\Process\Exception\ProcessTimedOutException;
use Symfony\Component\Process\Exception\RuntimeException;
use Symfony\Component\Process\Process;
use function array_merge;
use function trim;

/**
 * Copied from src/Process/SymfonyProcessShellCommandRunner.php.
 *
 * @phpstan-type ProcessCallback = Closure('out'|'err', string): void
 */
final class ShellCommandRunner
{
    private const string DEFAULT_SHELL_VERBOSITY = '0';

    /**
     * The default timeout used by Symfony Process.
     *
     * @see Process::__construct
     */
    public const float DEFAULT_TIMEOUT = 60.0;

    /**
     * @param string[] $command
     * @param ProcessCallback|null $callback
     * @param array<string, string|Stringable|false> $env
     *
     * @throws ProcessFailedException When process didn't terminate successfully.
     * @throws RuntimeException When process can't be launched.
     * @throws ProcessTimedOutException When process timed out.
     * @throws ProcessSignaledException When process stopped after receiving signal.
     */
    public function mustRun(
        array $command,
        ?Closure $callback = null,
        ?string $cwd = null,
        array $env = [],
        mixed $input = null,
        ?float $timeout = self::DEFAULT_TIMEOUT,
        ?float $idleTimeout = null,
    ): string
    {
        $process = self::createProcess(
            $command,
            $cwd,
            $env,
            $input,
            $timeout,
            $idleTimeout,
        );

        return trim($process->mustRun($callback)->getOutput());
    }

    /**
     * @param string[] $command
     * @param array<string, string|Stringable|false> $env
     */
    private static function createProcess(
        array $command,
        ?string $cwd,
        array $env,
        mixed $input,
        ?float $timeout,
        ?float $idleTimeout,
    ): Process {
        $process = new Process(
            $command,
            $cwd,
            array_merge(
                ['SHELL_VERBOSITY' => self::DEFAULT_SHELL_VERBOSITY],
                $env,
            ),
            $input,
            $timeout,
        );
        $process->setIdleTimeout($idleTimeout);

        return $process;
    }
}
