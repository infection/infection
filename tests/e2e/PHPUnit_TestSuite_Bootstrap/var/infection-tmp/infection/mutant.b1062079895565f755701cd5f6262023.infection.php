<?php

declare(strict_types=1);

namespace Infection\E2ETests\PHPUnitTestSuiteBootstrap;

final class Calculator
{
    public function addOne(int $value): int
    {
        return $value - 1;
    }
}
