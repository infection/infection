<?php

declare(strict_types=1);

namespace Infection\E2ETests\PHPUnitTIA\Behat;

use function array_map;
use function explode;
use function trim;

/**
 * Preserves a PHPUnit run's results before another run overwrites its recordings.
 */
final readonly class PhpUnitExecutionResult
{
    /**
     * @param list<string> $command
     * @param list<string> $loadedTests
     * @param list<string> $executedTests
     * @param array<string, mixed> $configuration
     */
    public function __construct(
        public array $command,
        public string $output,
        public array $loadedTests,
        public array $executedTests,
        public array $configuration,
    ) {
    }

    public static function fromRecordings(
        string $output,
        string $configurationJson,
        string $loadedTestsJson,
        string $executedTestsJsonLines,
    ): self {
        $configuration = Json::decode($configurationJson);
        $executedTests = trim($executedTestsJsonLines);

        return new self(
            command: $configuration['command'],
            output: $output,
            loadedTests: Json::decode($loadedTestsJson),
            executedTests: array_map(
                Json::decode(...),
                $executedTests === '' ? [] : explode("\n", $executedTests),
            ),
            configuration: $configuration,
        );
    }
}
