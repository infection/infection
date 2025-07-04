<?php

declare(strict_types=1);

namespace newSrc\MutationAnalyzer;

final class MutantExecutionResult
{
    /**
     * @param self[] $results
     */
    public static function aggregate(array $results): self {}

    public function getStatus(): MutantExecutionStatus
    {
    }
}
