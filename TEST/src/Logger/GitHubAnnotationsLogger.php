<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\Logger;

use _HumbugBox9658796bb9f0\Infection\Metrics\ResultsCollector;
use function _HumbugBox9658796bb9f0\Safe\shell_exec;
use function str_replace;
use _HumbugBox9658796bb9f0\Symfony\Component\Filesystem\Path;
use function trim;
final class GitHubAnnotationsLogger implements LineMutationTestingResultsLogger
{
    public const DEFAULT_OUTPUT = 'php://stdout';
    public function __construct(private ResultsCollector $resultsCollector)
    {
    }
    public function getLogLines() : array
    {
        $lines = [];
        $projectRootDirectory = trim(shell_exec('git rev-parse --show-toplevel'));
        foreach ($this->resultsCollector->getEscapedExecutionResults() as $escapedExecutionResult) {
            $error = ['line' => $escapedExecutionResult->getOriginalStartingLine(), 'message' => <<<TEXT
Escaped Mutant for Mutator "{$escapedExecutionResult->getMutatorName()}":

{$escapedExecutionResult->getMutantDiff()}
TEXT
];
            $lines[] = $this->buildAnnotation(Path::makeRelative($escapedExecutionResult->getOriginalFilePath(), $projectRootDirectory), $error);
        }
        return $lines;
    }
    private function buildAnnotation(string $filePath, array $error) : string
    {
        $message = str_replace("\n", '%0A', $error['message']);
        return "::warning file={$filePath},line={$error['line']}::{$message}\n";
    }
}
