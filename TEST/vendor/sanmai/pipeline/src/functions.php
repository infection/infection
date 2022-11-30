<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Pipeline;

function map(callable $func = null) : Standard
{
    $pipeline = new Standard();
    if (!$func) {
        return $pipeline;
    }
    return $pipeline->map($func);
}
function take(iterable $input = null) : Standard
{
    return new Standard($input);
}
function fromArray(array $input) : Standard
{
    return new Standard($input);
}
function zip(iterable $base, iterable ...$inputs) : Standard
{
    $result = take($base);
    $result->zip(...$inputs);
    return $result;
}
