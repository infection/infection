<?php

const STAGES = [
    'test-framework-initial',
    'static-analysis-initial',
    'test-framework-mutant',
    'static-analysis-mutant',
];

/**
 * @phpstan-type Record = array{
 *     stage: string,
 *     mutationHash: ?string,
 *     command: list<string>,
 *     php: array{memoryLimit: string, extensions: array{pcov: bool, xdebug: bool}},
 * }
 */
final class DebugRecords
{
    /**
     * @throws JsonException
     *
     * @return list<Record>
     */
    public static function load(string $filename): array
    {
        return array_map(
            static fn(string $line): array => json_decode($line, true, flags: JSON_THROW_ON_ERROR),
            file($filename, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES),
        );
    }

    /**
     * @param list<Record> $records
     * @return array<string, Record>
     */
    public static function indexByStage(array $records): array
    {
        $recordsByStage = [];

        foreach ($records as $record) {
            $stage = $record['stage'];

            if (isset($recordsByStage[$stage])) {
                throw new RuntimeException(
                    sprintf(
                        'Expected one "%s" record. Got: %s',
                        $stage,
                        json_encode($records),
                    ),
                );
            }

            $recordsByStage[$stage] = $record;
        }

        foreach (STAGES as $stage) {
            if (!isset($recordsByStage[$stage])) {
                throw new RuntimeException(
                    sprintf(
                        'Expected one "%s" record. Got: %s',
                        $stage,
                        json_encode($records),
                    ),
                );
            }
        }

        return $recordsByStage;
    }
}

$records = DebugRecords::load($argv[1]);
$recordsByStage = DebugRecords::indexByStage($records);

$testMutant = $recordsByStage['test-framework-mutant'];
$staticAnalysisMutant = $recordsByStage['static-analysis-mutant'];

if ($testMutant['mutationHash'] !== $staticAnalysisMutant['mutationHash']) {
    throw new RuntimeException(
        'The test framework and static analysis did not evaluate the same mutant.',
    );
}

if ($staticAnalysisMutant['php']['memoryLimit'] !== '-1') {
    throw new RuntimeException(
        'The static analysis mutant process must have an unlimited memory limit.',
    );
}

if (!in_array('memory_limit=-1', $staticAnalysisMutant['command'], true)) {
    throw new RuntimeException('The static analysis command does not contain its memory limit override.');
}

foreach ($records as $record) {
    if (!is_bool($record['php']['extensions']['pcov']) || !is_bool($record['php']['extensions']['xdebug'])) {
        throw new RuntimeException('The coverage extension state was not recorded.');
    }
}
