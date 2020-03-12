<?php

declare(strict_types=1);

namespace Infection\Event;

final class TotalLineCodeCoverageWasCalculated
{
    private $codeCoverage;

    public function __construct(float $codeCoverage)
    {
        $this->codeCoverage = $codeCoverage;
    }

    public function getCodeCoverage(): float
    {
        return $this->codeCoverage;
    }
}
