<?php

declare(strict_types=1);

namespace Infection\TestFramework\NewCoverage\JUnit;

final readonly class TestInfo
{
    public function __construct(
        public string $location,
        public float $executionTime,
    ) {
    }
}
