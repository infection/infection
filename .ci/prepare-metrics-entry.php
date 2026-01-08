#!/usr/bin/env php
<?php

declare(strict_types=1);

/**
 * Prepares the metrics entry JSON from timing data and optional Infection summary.
 *
 * Usage: php .ci/prepare-metrics-entry.php \
 *          --timing=timing.json \
 *          --output=entry.json \
 *          [--infection-summary=infection-summary.json] \
 *          [--commit-date=2024-01-01T00:00:00+00:00] \
 *          [--git-sha=abc123] \
 *          [--git-ref=refs/heads/main] \
 *          [--trigger=push] \
 *          [--cpu-cores=4] \
 *          [--cpu-model="Intel..."] \
 *          [--cpu-arch=x86_64] \
 *          [--total-memory-kb=16000000] \
 *          [--image-version=unknown]
 */

$options = getopt('', [
    'timing:',
    'output:',
    'infection-summary::',
    'commit-date:',
    'git-sha:',
    'git-ref:',
    'trigger:',
    'cpu-cores:',
    'cpu-model:',
    'cpu-arch:',
    'total-memory-kb:',
    'image-version::',
]);

// Required options
$timingFile = $options['timing'] ?? null;
$outputFile = $options['output'] ?? null;

if ($timingFile === null || $outputFile === null) {
    fwrite(STDERR, "Usage: php prepare-metrics-entry.php --timing=FILE --output=FILE [options]\n");
    exit(1);
}

if (!file_exists($timingFile)) {
    fwrite(STDERR, "Error: Timing file not found: $timingFile\n");
    exit(1);
}

// Read timing data
$timingJson = file_get_contents($timingFile);
$timing = json_decode($timingJson, true);

if ($timing === null) {
    fwrite(STDERR, "Error: Failed to parse timing JSON: " . json_last_error_msg() . "\n");
    fwrite(STDERR, "Content: $timingJson\n");
    exit(1);
}

// Read infection summary if available
$mutations = null;
$infectionSummaryFile = $options['infection-summary'] ?? 'infection-summary.json';
if (file_exists($infectionSummaryFile)) {
    $summaryJson = file_get_contents($infectionSummaryFile);
    $summary = json_decode($summaryJson, true);
    if ($summary !== null && isset($summary['stats'])) {
        $mutations = [
            'total' => $summary['stats']['totalMutantsCount'] ?? 0,
            'killed' => $summary['stats']['killedCount'] ?? 0,
            'msi' => $summary['stats']['msi'] ?? 0,
        ];
    }
}

// Get parameters with defaults
$cpuCores = (int) ($options['cpu-cores'] ?? 1);
$commitDate = $options['commit-date'] ?? date('c');
$gitSha = $options['git-sha'] ?? 'unknown';
$gitRef = $options['git-ref'] ?? 'unknown';
$trigger = $options['trigger'] ?? 'unknown';
$cpuModel = $options['cpu-model'] ?? 'unknown';
$cpuArch = $options['cpu-arch'] ?? 'unknown';
$totalMemoryKb = (int) ($options['total-memory-kb'] ?? 0);
$imageVersion = $options['image-version'] ?? 'unknown';

// Extract timing values
$wallClock = (float) ($timing['wall_clock_sec'] ?? 0);
$userTime = (float) ($timing['user_time_sec'] ?? 0);
$sysTime = (float) ($timing['system_time_sec'] ?? 0);
$maxRss = (int) ($timing['max_rss_kb'] ?? 0);

// Calculate derived metrics
$wallPerCore = $cpuCores > 0 ? round($wallClock / $cpuCores, 2) : 0;
$cpuTotal = round($userTime + $sysTime, 2);

$wallPerMutation = 0;
$userPerMutation = 0;
$memPerMutation = 0;

if ($mutations !== null && $mutations['total'] > 0) {
    $wallPerMutation = round($wallClock / $mutations['total'], 4);
    $userPerMutation = round($userTime / $mutations['total'], 4);
    $memPerMutation = round($maxRss / $mutations['total'], 2);
}

// Build the entry
$entry = array_merge($timing, [
    'timestamp' => gmdate('Y-m-d\TH:i:s\Z'),
    'commit_date' => $commitDate,
    'git_sha' => $gitSha,
    'git_ref' => $gitRef,
    'trigger' => $trigger,
    'php_version' => PHP_VERSION,
    'config' => [
        'name' => 'baseline',
        'args' => '--no-progress --threads=max',
    ],
    'runner' => [
        'os' => 'ubuntu-latest',
        'image_version' => $imageVersion,
        'cpu_model' => $cpuModel,
        'cpu_cores' => $cpuCores,
        'cpu_arch' => $cpuArch,
        'total_memory_kb' => $totalMemoryKb,
    ],
]);

if ($mutations !== null) {
    $entry['mutations'] = $mutations;
}

$entry['derived'] = [
    'wall_clock_per_core' => $wallPerCore,
    'cpu_time_total' => $cpuTotal,
    'wall_clock_per_mutation' => $wallPerMutation,
    'user_time_per_mutation' => $userPerMutation,
    'memory_per_mutation_kb' => $memPerMutation,
];

// Write output
$json = json_encode($entry, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

if ($json === false) {
    fwrite(STDERR, "Error: Failed to encode JSON: " . json_last_error_msg() . "\n");
    exit(1);
}

file_put_contents($outputFile, $json . "\n");

echo "Final metrics entry:\n";
echo $json . "\n";
