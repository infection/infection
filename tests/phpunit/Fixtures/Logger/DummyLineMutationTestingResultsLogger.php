<?php

declare(strict_types=1);

namespace Infection\Tests\Fixtures\Logger;

use Infection\Logger\LineMutationTestingResultsLogger;

final class DummyLineMutationTestingResultsLogger implements LineMutationTestingResultsLogger
{
    /**
     * @param string[] $lines
     */
    public function __construct(private readonly array $lines)
    {
    }

    public function getLogLines(): array
    {
        return $this->lines;
    }
}
