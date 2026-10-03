<?php

declare(strict_types=1);

namespace Infection\E2ETests\PHPUnitTIA;

final class Unrelated
{
    public function calculate(int $a, int $b): int
    {
        return $a * $b;
    }
}
