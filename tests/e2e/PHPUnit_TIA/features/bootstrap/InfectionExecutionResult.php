<?php

declare(strict_types=1);

namespace Infection\E2ETests\PHPUnitTIA\Behat;

use Infection\Event\Events\ArtefactCollection\InitialTestExecution\InitialTestSuiteWasFinished;
use Infection\Event\Events\MutationAnalysis\MutationEvaluation\MutantProcessWasFinished;
use Infection\Event\Events\MutationAnalysis\MutationGeneration\MutableFileWasProcessed;
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
     * @param list<array<string, mixed>> $events
     * @param array<string, mixed> $report
     * @param array<string, list<array{line: int, tests: list<string>}>> $coverageBySource
     * @param array<string, string|null> $initialCache
     * @param array<string, string|null> $finalCache
     */
    public function __construct(
        public array $command,
        public string $output,
        public array $events,
        public array $report,
        public array $coverageBySource,
        public array $initialCache,
        public array $finalCache,
    ) {
    }

    /**
     * @param list<string> $command
     * @param array<string, list<array{line: int, tests: list<string>}>> $coverageBySource
     * @param array<string, string|null> $initialCache
     * @param array<string, string|null> $finalCache
     */
    public static function fromRecordings(
        array $command,
        string $output,
        string $debugEventsJsonLines,
        string $infectionReportJson,
        array $coverageBySource,
        array $initialCache,
        array $finalCache,
    ): self {
        $debugEvents = trim($debugEventsJsonLines);

        return new self(
            command: $command,
            output: $output,
            events: array_map(
                Json::decode(...),
                $debugEvents === ''
                    ? []
                    : explode(
                        "\n",
                        $debugEvents,
                    ),
            ),
            report: Json::decode($infectionReportJson),
            coverageBySource: $coverageBySource,
            initialCache: $initialCache,
            finalCache: $finalCache,
        );
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function getSources(): array
    {
        return array_map(
            function (array $source): array {
                Assert::keyExists(
                    $this->coverageBySource,
                    $source['sourceFilePath'],
                    'Every processed source must have an entry in the XML coverage report.',
                );

                return [
                    'file' => $source['sourceFilePath'],
                    'mutationHashes' => $source['mutationHashes'],
                    'coverage' => $this->coverageBySource[$source['sourceFilePath']],
                ];
            },
            $this->getEvents(MutableFileWasProcessed::class),
        );
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function getMutations(): array
    {
        return array_map(
            static fn (array $event): array => [
                'hash' => $event['executionResult']['mutantHash'],
                'file' => $event['executionResult']['originalFilePath'],
                'mutator' => $event['executionResult']['mutatorName'],
                'startLine' => $event['executionResult']['originalStartingLine'],
                'endLine' => $event['executionResult']['originalEndingLine'],
                'status' => $event['executionResult']['detectionStatus'],
            ],
            $this->getEvents(MutantProcessWasFinished::class),
        );
    }

    /**
     * @return array<string, list<array<string, mixed>>>
     */
    public function getMutationDiffs(): array
    {
        $groups = $this->report;
        unset($groups['stats']);

        return array_map(
            static fn (array $mutants): array => array_map(
                static fn (array $mutant): array => array_intersect_key(
                    $mutant,
                    array_flip(['mutator', 'diff']),
                ),
                $mutants,
            ),
            $groups,
        );
    }

    public function getMsi(): float
    {
        return $this->report['stats']['msi'];
    }

    public function getInitialPhpUnitOutput(): string
    {
        $initialTestResults = $this->getEvents(InitialTestSuiteWasFinished::class);

        Assert::count(
            $initialTestResults,
            1,
            'Expected exactly one completed initial PHPUnit execution in the Infection report.',
        );

        return $initialTestResults[0]['outputText'];
    }

    /** @return list<array<string, mixed>> */
    public function getEvents(string $type): array
    {
        return array_map(
            static fn (array $event): array => $event['data'],
            array_values(
                array_filter(
                    $this->events,
                    static fn (array $event): bool => $event['event'] === $type,
                ),
            ),
        );
    }
}
