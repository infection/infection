<?php

/**
 * @phpstan-type Record = array{
 *     stage: string,
 *     mutationHash: ?string,
 *     command: list<string>,
 *     php: array{memoryLimit: string, extensions: array{pcov: bool, xdebug: bool}},
 *     coverage: array{driver: 'xdebug'|'pcov'|'phpdbg'|null, xdebugAvailable: bool, pcovAvailable: bool, phpdbg: bool},
 *     xdebug: array{loaded: bool, version: string|false, mode: string|false, coverageAvailable: bool},
 *     xdebugHandler: array{
 *         restartSettingsPresent: bool,
 *         originalInisPresent: bool,
 *         allowXdebug: string|false,
 *         phprc: string|false,
 *         phpIniScanDir: string|false,
 *     },
 * }
 * @phpstan-type ExpectedRecord = array{stage: string, ...<string, mixed>}
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
     * @throws JsonException
     *
     * @return list<ExpectedRecord>
     */
    public static function loadExpected(string $filename): array
    {
        return json_decode(
            file_get_contents($filename),
            associative: true,
            flags: JSON_THROW_ON_ERROR,
        );
    }
}

$records = DebugRecords::load($argv[1]);
$expectedRecords = DebugRecords::loadExpected($argv[2]);

if (count($records) !== count($expectedRecords)) {
    throw new RuntimeException(
        sprintf('Expected %d debug records, got %d.', count($expectedRecords), count($records)),
    );
}

foreach ($expectedRecords as $index => $expectedRecord) {
    $path = sprintf('record[%d]', $index);
    assertJsonSubset($expectedRecord, $records[$index], $path);
}

/**
 * @param array<string, mixed> $expected
 * @param array<string, mixed> $actual
 */
function assertJsonSubset(array $expected, array $actual, string $path): void
{
    foreach ($expected as $key => $expectedValue) {
        $propertyPath = $path . '.' . $key;

        if (!array_key_exists($key, $actual)) {
            throw new RuntimeException(sprintf('Property "%s" is missing from the actual record.', $propertyPath));
        }

        $actualValue = $actual[$key];

        if (is_array($expectedValue) && is_array($actualValue)) {
            assertJsonSubset($expectedValue, $actualValue, $propertyPath);

            continue;
        }

        if ($actualValue !== $expectedValue) {
            throw new RuntimeException(sprintf(
                'Property "%s" does not match. Expected %s, got %s.',
                $propertyPath,
                json_encode($expectedValue),
                json_encode($actualValue),
            ));
        }
    }
}

$testMutant = $records[2];
$staticAnalysisMutant = $records[3];

if ($testMutant['mutationHash'] === null || $testMutant['mutationHash'] !== $staticAnalysisMutant['mutationHash']) {
    throw new RuntimeException(
        'The test framework and static analysis did not evaluate the same mutant.',
    );
}
