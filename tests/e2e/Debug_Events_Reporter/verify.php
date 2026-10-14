<?php

declare(strict_types=1);

$events = array_map(
    static fn (string $line): array => json_decode(
        $line,
        associative: true,
        flags: JSON_THROW_ON_ERROR,
    ),
    file($argv[1], FILE_IGNORE_NEW_LINES),
);

foreach ($events as $index => $event) {
    if ($event['sequence'] !== $index + 1) {
        throw new RuntimeException('Event sequence numbers must start at one and be consecutive.');
    }
}

$allEventNames = array_map(
    static fn (array $event): string => substr(
        $event['event'],
        strrpos($event['event'], '\\') + 1,
    ),
    $events,
);

$eventNames = array_values(
    array_filter(
        $allEventNames,
        static fn (string $name): bool => $name !== 'InitialTestCaseWasCompleted',
    ),
);

$expectedEventNames = [
    'ApplicationExecutionWasStarted',
    'InitialTestSuiteWasStarted',
    'InitialTestSuiteWasFinished',
    'MutationTestingWasStarted',
    'MutationGenerationWasStarted',
    'MutationEvaluationWasStarted',
    'MutableFileWasProcessed',
    'MutationGenerationWasFinished',
    'MutantProcessWasFinished',
    'MutationTestingWasFinished',
];

if ($eventNames !== $expectedEventNames) {
    throw new RuntimeException(
        sprintf(
            'The event trace must cover execution through mutation-testing completion in dispatch order. Got: %s',
            json_encode($eventNames),
        ),
    );
}

$eventsByName = array_combine(
    $allEventNames,
    $events,
);

if ($eventsByName['MutantProcessWasFinished']['data']['executionResult']['detectionStatus'] !== 'killed by tests') {
    throw new RuntimeException('The trace must record that PHPUnit killed the mutant.');
}
