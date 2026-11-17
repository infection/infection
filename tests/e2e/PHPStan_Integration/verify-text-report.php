<?php

declare(strict_types=1);

$report = str_replace("\r\n", "\n", file_get_contents($argv[1]));

// The fixture has three escaped mutants and four killed by PHPStan.
// Each must retain the successful PHPUnit run before the PHPStan result.
$combinedAttempts = preg_match_all(
    '/^PHPUnit:\n\$ [^\n]*phpunit[^\n]*\n  PHPUnit.*?^PHPStan:\n\$ [^\n]*phpstan[^\n]*\n  \{"totals":/ms',
    $report,
);

if ($combinedAttempts !== 7) {
    throw new RuntimeException('The text report must contain the PHPUnit and PHPStan commands and outputs for all seven mutants evaluated by both tools.');
}

if (substr_count($report, "\nPHPUnit:\n") !== 7 || substr_count($report, "\nPHPStan:\n") !== 7) {
    throw new RuntimeException('Each evaluator must be labelled once per combined mutant evaluation.');
}
