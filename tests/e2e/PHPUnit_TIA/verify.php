<?php

declare(strict_types=1);

$expectedTests = [
    'cold' => ['CalculatorTest', 'UnrelatedTest'],
    'warm' => ['CalculatorTest'],
    'project-seeded' => ['CalculatorTest'],
    'declared-cold' => ['CalculatorTest', 'UnrelatedTest'],
    'declared-warm' => ['CalculatorTest'],
    'disabled' => ['CalculatorTest', 'UnrelatedTest'],
    'project-reuse' => ['CalculatorTest'],
    'relocated' => ['CalculatorTest'],
    'invalidated' => ['CalculatorTest', 'UnrelatedTest'],
];

foreach ($expectedTests as $phase => $expected) {
    $junit = simplexml_load_file(__DIR__ . '/var/' . $phase . '/junit.xml');
    $actual = array_map(
        static fn (SimpleXMLElement $test): string => basename(str_replace('\\', '/', (string) $test['class'])),
        $junit->xpath('//testcase'),
    );
    sort($actual);

    if ($actual !== $expected) {
        throw new RuntimeException(sprintf('%s initial run: expected %s, got %s', $phase, json_encode($expected), json_encode($actual)));
    }

    if (in_array($phase, ['project-reuse', 'relocated', 'invalidated'], true)) {
        $output = file_get_contents(__DIR__ . '/var/' . $phase . '/console.log');
        $diagnostic = $phase === 'invalidated'
            ? 'the configuration changed since the test impact data was recorded'
            : '1 of 2 tests can be affected by what changed';

        if (!str_contains($output, $diagnostic)) {
            throw new RuntimeException('Missing impact diagnostic in ' . $phase . ': ' . $output);
        }

        if ($phase !== 'invalidated' && !str_contains($output, 'Recorded:')) {
            throw new RuntimeException('Missing recording timestamp in ' . $phase);
        }

        continue;
    }

    $results = json_decode(file_get_contents(__DIR__ . '/var/' . $phase . '/mutations.json'), true, flags: JSON_THROW_ON_ERROR);

    if (count($results['killed']) !== 1 || !str_contains($results['killed'][0]['processOutput'], 'Tests: 1, Assertions: 1, Failures: 1.')) {
        throw new RuntimeException('Expected the selected Calculator test to kill the Plus mutant in ' . $phase);
    }

    $mutants[$phase] = $results['killed'][0]['mutator'];

    if (str_contains($results['killed'][0]['processOutput'], 'Impact:')) {
        throw new RuntimeException('TIA leaked into the mutant process in ' . $phase);
    }

    $coverage = simplexml_load_file(__DIR__ . '/var/' . $phase . '/coverage.xml');
    $coverage->registerXPathNamespace('p', 'https://schema.phpunit.de/coverage/1.0');
    $lines[$phase] = array_map(
        static fn (SimpleXMLElement $line): string => $line->asXML(),
        $coverage->xpath('//p:coverage/p:line'),
    );
}

foreach ($mutants as $phase => $mutant) {
    if ($mutants['cold'] !== $mutant) {
        throw new RuntimeException('TIA changed the generated mutant in ' . $phase);
    }

    if ($lines['cold'] === [] || $lines['cold'] !== $lines[$phase]) {
        throw new RuntimeException('TIA changed the line-to-test coverage consumed by Infection in ' . $phase);
    }
}

echo "Initial tests: observed=2/1, project-seeded=1, declared=2/1, disabled=2. Same coverage and mutant; TIA absent from mutant execution.\n";
echo "Shared recording: project=1, relocated=1, changed execution settings=2 with invalidation reason.\n";
