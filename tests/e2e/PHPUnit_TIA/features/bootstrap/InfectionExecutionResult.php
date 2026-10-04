<?php

declare(strict_types=1);

namespace Infection\E2ETests\PHPUnitTIA\Behat;

use Webmozart\Assert\Assert;
use function array_filter;
use function array_flip;
use function array_intersect_key;
use function array_map;
use function array_values;
use function explode;
use function trim;

/**
 * Preserves an Infection run's command, output, and reports across subsequent runs.
 */
final readonly class InfectionExecutionResult
{
    /**
     * @param list<string> $command
     * @param list<array<string, mixed>> $executionEvents
     * @param array<string, mixed> $report
     */
    public function __construct(
        public array $command,
        public string $output,
        public array $executionEvents,
        public array $report,
    ) {
    }

    /**
     * @param list<string> $command
     */
    public static function fromRecordings(
        array $command,
        string $output,
        string $executionReportJsonLines,
        string $infectionReportJson,
    ): self {
        $executionReport = trim($executionReportJsonLines);

        return new self(
            command: $command,
            output: $output,
            executionEvents: array_map(
                Json::decode(...),
                $executionReport === ''
                    ? []
                    : explode(
                        "\n",
                        $executionReport,
                    ),
            ),
            report: Json::decode($infectionReportJson),
        );
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function getSources(): array
    {
        return $this->selectEvents(
            'source_processed',
            [
                'file',
                'mutationHashes',
                'coverage',
            ],
        );
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function getMutations(): array
    {
        return $this->selectEvents(
            'mutant_finished',
            [
                'hash',
                'file',
                'mutator',
                'startLine',
                'endLine',
                'diff',
                'status',
            ],
        );
    }

    public function getMsi(): float
    {
        return $this->report['stats']['msi'];
    }

    public function getInitialPhpUnitOutput(): string
    {
        $initialTestResults = $this->selectEvents(
            'initial_tests_finished',
            ['output'],
        );

        Assert::count(
            $initialTestResults,
            1,
            'Expected exactly one completed initial PHPUnit execution in the Infection report.',
        );

        return $initialTestResults[0]['output'];
    }

    /**
     * @param list<string> $fields
     *
     * @return list<array<string, mixed>>
     */
    private function selectEvents(string $type, array $fields): array
    {
        $selectFields = static fn (array $event): array => array_intersect_key(
            $event,
            array_flip($fields),
        );

        return array_map(
            $selectFields,
            $this->getEvents($type),
        );
    }

    /** @return list<array<string, mixed>> */
    public function getEvents(string $type): array
    {
        return array_values(
            array_filter(
                $this->executionEvents,
                static fn (array $event): bool => $event['event'] === $type,
            ),
        );
    }
}
