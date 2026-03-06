<?php

declare(strict_types=1);

namespace Infection\Tests\Fixtures\Reporter;

use Infection\Reporter\LineMutationTestingResultsReporter;

final readonly class DummyLineMutationTestingResultsReporter implements LineMutationTestingResultsReporter
{
    /**
     * @param string[] $lines
     */
    public function __construct(private array $lines)
    {
    }

    public function getLines(): array
    {
        return $this->lines;
    }
}
