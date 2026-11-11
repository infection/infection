<?php

declare(strict_types=1);

namespace SourceSymbolSelection;

final class Calculator
{
    public function add(
        int $left,
        int $right,
    ): int {
        return $left + $right;
    }

    public function addThree(
        int $first,
        int $second,
        int $third,
    ): int {
        return $first + $second + $third;
    }
}
