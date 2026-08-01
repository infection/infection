<?php

declare(strict_types=1);

$records = array_map(
    static fn (string $line): array => json_decode($line, true, flags: JSON_THROW_ON_ERROR),
    file($argv[1], FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES),
);

$byStage = [];

foreach ($records as $record) {
    $byStage[$record['stage']][] = $record;
}

foreach (['test-framework-initial', 'static-analysis-initial', 'test-framework-mutant', 'static-analysis-mutant'] as $stage) {
    if (!isset($byStage[$stage]) || count($byStage[$stage]) !== 1) {
        throw new RuntimeException(sprintf('Expected one "%s" record. Got: %s', $stage, json_encode($records)));
    }
}

$testMutant = $byStage['test-framework-mutant'][0];
$staticAnalysisMutant = $byStage['static-analysis-mutant'][0];

if ($testMutant['mutationHash'] !== $staticAnalysisMutant['mutationHash']) {
    throw new RuntimeException('The test framework and static analysis did not evaluate the same mutant.');
}

if ($staticAnalysisMutant['php']['memoryLimit'] !== '-1') {
    throw new RuntimeException('The static analysis mutant process must have an unlimited memory limit.');
}

if (!in_array('memory_limit=-1', $staticAnalysisMutant['command'], true)) {
    throw new RuntimeException('The static analysis command does not contain its memory limit override.');
}

foreach ($records as $record) {
    if (!is_bool($record['php']['extensions']['pcov']) || !is_bool($record['php']['extensions']['xdebug'])) {
        throw new RuntimeException('The coverage extension state was not recorded.');
    }
}
