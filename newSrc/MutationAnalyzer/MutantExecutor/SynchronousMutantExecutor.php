<?php

declare(strict_types=1);

namespace newSrc\MutationAnalyzer\MutantExecutor;

use newSrc\MutationAnalyzer\Mutant;
use newSrc\MutationAnalyzer\MutantExecutionResult;
use newSrc\MutationAnalyzer\MutantExecutionStatus;
use newSrc\TestFramework\TestFramework;

final class SynchronousMutantExecutor implements MutantExecutor
{
    /**
     * @param TestFramework[] $testFrameworks
     */
    public function __construct(
        private array $testFrameworks,
    ) {
    }

    public function execute(Mutant $mutant): MutantExecutionResult
    {
        $results = [];

        foreach ($this->testFrameworks as $testFramework) {
            $result = $testFramework->test($mutant);
            $resultStatus = $result->getStatus();

            $results[] = $result;

            if ($resultStatus === MutantExecutionStatus::COVERED) {
                return $result;
            }

            if ($resultStatus === MutantExecutionStatus::SUSPICIOUS) {
                // TODO: discuss the strategy
                // an idea: retry this test framework with a noop test
                // if noop test passes, continue with test frameworks
                // other test framework may cover or not
            }
        }

        // Here there two possible statuses for each result: NOT_COVERED and SUSPICIOUS
        //
        // If all NOT_COVERED => aggregate = NOT COVERED
        // Otherwise (at least one suspicious) => SUSPICIOUS
        return MutantExecutionResult::aggregate($results);
    }
}
