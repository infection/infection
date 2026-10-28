<?php

namespace CustomCountFunction;

function count(array $array): int
{
    return 1;
}

final class CustomCount
{
    public function compare(array $array): bool
    {
        return count($array) === 0;
    }
}
