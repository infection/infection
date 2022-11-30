<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Later;

/**
 * @template T
 *
 * @param callable():(iterable<T>) $generator
 *
 * @return Interfaces\Deferred<T>
 */
function later(callable $generator) : Interfaces\Deferred
{
    return new Deferred($generator());
}
/**
@template
*/
function lazy(iterable $iterableOrGenerator) : Interfaces\Deferred
{
    return new Deferred($iterableOrGenerator);
}
/**
@template
*/
function now($input) : Interfaces\Deferred
{
    return new Immediate($input);
}
