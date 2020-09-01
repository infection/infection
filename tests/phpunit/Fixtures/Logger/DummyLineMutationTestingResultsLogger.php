<?php

declare(strict_types=1);

namespace Infection\Tests\Fixtures\Logger;

use Infection\Logger\LineMutationTestingResultsLogger;

final class DummyLineMutationTestingResultsLogger implements LineMutationTestingResultsLogger
{
    private $lines;

    /**
     * @param string[] $lines
     */
    public function __construct(array $lines)
    {
        $this->lines = $lines;
    }

    public function getLogLines(): array
    {
        return $this->lines;
    }
}
