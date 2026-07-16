<?php

declare(strict_types=1);

namespace Infection\Process;

/**
 * @internal
 */
final readonly class CompletedProcess
{
    /**
     * @param list<string> $command
     */
    public function __construct(
        public array $command,
        public int $exitCode,
        public string $stdout,
        public string $stderr,
    ) {
    }

    public function isSuccessful(): bool
    {
        return $this->exitCode === 0;
    }
}
