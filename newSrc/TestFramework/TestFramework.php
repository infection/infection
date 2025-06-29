<?php

namespace newSrc\TestFramework;

// This is the current TestFrameworkAdapter
use newSrc\MutationAnalyzer\Mutant;
use newSrc\MutationAnalyzer\MutantExecutionResult;

interface TestFramework
{
    public function getName(): string;

    public function isSkippable(): bool;

    // E.g. code coverage for PHPUnit or Codeception, Cache for PHPStan
    public function checkRequiredArtefacts(): void;

    public function executeInitialRun(): void;

    // TODO: for a test dry run, this would be better to do it there
    public function test(Mutant $mutant): MutantExecutionResult;
}
