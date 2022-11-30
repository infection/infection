<?php

namespace _HumbugBoxb47773b41c19\iter;

function range($start, $end, $step = null) : \Iterator
{
    if ($start == $end) {
        (yield $start);
    } elseif ($start < $end) {
        if (null === $step) {
            $step = 1;
        } elseif ($step <= 0) {
            throw new \InvalidArgumentException('If start < end the step must be positive');
        }
        for ($i = $start; $i <= $end; $i += $step) {
            (yield $i);
        }
    } else {
        if (null === $step) {
            $step = -1;
        } elseif ($step >= 0) {
            throw new \InvalidArgumentException('If start > end the step must be negative');
        }
        for ($i = $start; $i >= $end; $i += $step) {
            (yield $i);
        }
    }
}
function map(callable $function, iterable $iterable) : \Iterator
{
    foreach ($iterable as $key => $value) {
        (yield $key => $function($value));
    }
}
function mapKeys(callable $function, iterable $iterable) : \Iterator
{
    foreach ($iterable as $key => $value) {
        (yield $function($key) => $value);
    }
}
function mapWithKeys(callable $function, iterable $iterable) : \Iterator
{
    foreach ($iterable as $key => $value) {
        (yield $key => $function($value, $key));
    }
}
function flatMap(callable $function, iterable $iterable) : \Iterator
{
    foreach ($iterable as $value) {
        yield from $function($value);
    }
}
function reindex(callable $function, iterable $iterable) : \Iterator
{
    foreach ($iterable as $value) {
        (yield $function($value) => $value);
    }
}
function apply(callable $function, iterable $iterable) : void
{
    foreach ($iterable as $value) {
        $function($value);
    }
}
function filter(callable $predicate, iterable $iterable) : \Iterator
{
    foreach ($iterable as $key => $value) {
        if ($predicate($value)) {
            (yield $key => $value);
        }
    }
}
function enumerate(iterable $iterable) : \Iterator
{
    return toPairs($iterable);
}
function toPairs(iterable $iterable) : \Iterator
{
    foreach ($iterable as $key => $value) {
        (yield [$key, $value]);
    }
}
function fromPairs(iterable $iterable) : \Iterator
{
    foreach ($iterable as [$key, $value]) {
        (yield $key => $value);
    }
}
function reduce(callable $function, iterable $iterable, $startValue = null)
{
    $acc = $startValue;
    foreach ($iterable as $key => $value) {
        $acc = $function($acc, $value, $key);
    }
    return $acc;
}
function reductions(callable $function, iterable $iterable, $startValue = null) : \Iterator
{
    $acc = $startValue;
    foreach ($iterable as $key => $value) {
        $acc = $function($acc, $value, $key);
        (yield $acc);
    }
}
function zip(iterable ...$iterables) : \Iterator
{
    if (\count($iterables) === 0) {
        return;
    }
    $iterators = \array_map('_HumbugBoxb47773b41c19\\iter\\toIter', $iterables);
    for (apply(func\method('rewind'), $iterators); all(func\method('valid'), $iterators); apply(func\method('next'), $iterators)) {
        (yield toArray(map(func\method('key'), $iterators)) => toArray(map(func\method('current'), $iterators)));
    }
}
function zipKeyValue(iterable $keys, iterable $values) : \Iterator
{
    $keys = toIter($keys);
    $values = toIter($values);
    for ($keys->rewind(), $values->rewind(); $keys->valid() && $values->valid(); $keys->next(), $values->next()) {
        (yield $keys->current() => $values->current());
    }
}
function chain(iterable ...$iterables) : \Iterator
{
    foreach ($iterables as $iterable) {
        yield from $iterable;
    }
}
function product(iterable ...$iterables) : \Iterator
{
    $iterators = \array_map('_HumbugBoxb47773b41c19\\iter\\toIter', $iterables);
    $numIterators = \count($iterators);
    if (!$numIterators) {
        (yield [] => []);
        return;
    }
    $keyTuple = $valueTuple = \array_fill(0, $numIterators, null);
    $i = -1;
    while (\true) {
        while (++$i < $numIterators - 1) {
            $iterators[$i]->rewind();
            if (!$iterators[$i]->valid()) {
                return;
            }
            $keyTuple[$i] = $iterators[$i]->key();
            $valueTuple[$i] = $iterators[$i]->current();
        }
        foreach ($iterators[$i] as $keyTuple[$i] => $valueTuple[$i]) {
            (yield $keyTuple => $valueTuple);
        }
        while (--$i >= 0) {
            $iterators[$i]->next();
            if ($iterators[$i]->valid()) {
                $keyTuple[$i] = $iterators[$i]->key();
                $valueTuple[$i] = $iterators[$i]->current();
                continue 2;
            }
        }
        return;
    }
}
function slice(iterable $iterable, int $start, $length = \INF) : \Iterator
{
    if ($start < 0) {
        throw new \InvalidArgumentException('Start offset must be non-negative');
    }
    if ($length < 0) {
        throw new \InvalidArgumentException('Length must be non-negative');
    }
    if ($length === 0) {
        return;
    }
    $i = 0;
    foreach ($iterable as $key => $value) {
        if ($i++ < $start) {
            continue;
        }
        (yield $key => $value);
        if ($i >= $start + $length) {
            break;
        }
    }
}
function take(int $num, iterable $iterable) : \Iterator
{
    return slice($iterable, 0, $num);
}
function drop(int $num, iterable $iterable) : \Iterator
{
    return slice($iterable, $num);
}
function repeat($value, $num = \INF) : \Iterator
{
    if ($num < 0) {
        throw new \InvalidArgumentException('Number of repetitions must be non-negative');
    }
    for ($i = 0; $i < $num; ++$i) {
        (yield $value);
    }
}
function keys(iterable $iterable) : \Iterator
{
    foreach ($iterable as $key => $_) {
        (yield $key);
    }
}
function values(iterable $iterable) : \Iterator
{
    foreach ($iterable as $value) {
        (yield $value);
    }
}
function any(callable $predicate, iterable $iterable) : bool
{
    foreach ($iterable as $value) {
        if ($predicate($value)) {
            return \true;
        }
    }
    return \false;
}
function all(callable $predicate, iterable $iterable) : bool
{
    foreach ($iterable as $value) {
        if (!$predicate($value)) {
            return \false;
        }
    }
    return \true;
}
function search(callable $predicate, iterable $iterable)
{
    foreach ($iterable as $value) {
        if ($predicate($value)) {
            return $value;
        }
    }
    return null;
}
function takeWhile(callable $predicate, iterable $iterable) : \Iterator
{
    foreach ($iterable as $key => $value) {
        if (!$predicate($value)) {
            return;
        }
        (yield $key => $value);
    }
}
function dropWhile(callable $predicate, iterable $iterable) : \Iterator
{
    $failed = \false;
    foreach ($iterable as $key => $value) {
        if (!$failed && !$predicate($value)) {
            $failed = \true;
        }
        if ($failed) {
            (yield $key => $value);
        }
    }
}
function flatten(iterable $iterable, $levels = \INF) : \Iterator
{
    if ($levels < 0) {
        throw new \InvalidArgumentException('Number of levels must be non-negative');
    }
    if ($levels === 0) {
        yield from $iterable;
    } else {
        if ($levels === 1) {
            foreach ($iterable as $key => $value) {
                if (isIterable($value)) {
                    yield from $value;
                } else {
                    (yield $key => $value);
                }
            }
        } else {
            foreach ($iterable as $key => $value) {
                if (isIterable($value)) {
                    yield from flatten($value, $levels - 1);
                } else {
                    (yield $key => $value);
                }
            }
        }
    }
}
function flip(iterable $iterable) : \Iterator
{
    foreach ($iterable as $key => $value) {
        (yield $value => $key);
    }
}
function chunk(iterable $iterable, int $size, bool $preserveKeys = \false) : \Iterator
{
    if ($size <= 0) {
        throw new \InvalidArgumentException('Chunk size must be positive');
    }
    $chunk = [];
    $count = 0;
    foreach ($iterable as $key => $value) {
        if ($preserveKeys) {
            $chunk[$key] = $value;
        } else {
            $chunk[] = $value;
        }
        $count++;
        if ($count === $size) {
            (yield $chunk);
            $count = 0;
            $chunk = [];
        }
    }
    if ($count !== 0) {
        (yield $chunk);
    }
}
function chunkWithKeys(iterable $iterable, int $size) : \Iterator
{
    return chunk($iterable, $size, \true);
}
function join(string $separator, iterable $iterable) : string
{
    $str = '';
    $first = \true;
    foreach ($iterable as $value) {
        if ($first) {
            $str .= $value;
            $first = \false;
        } else {
            $str .= $separator . $value;
        }
    }
    return $str;
}
function split(string $separator, string $data) : iterable
{
    if (\strlen($separator) === 0) {
        throw new \InvalidArgumentException('Separator must be non-empty string');
    }
    return (function () use($separator, $data) {
        $offset = 0;
        while ($offset < \strlen($data) && \false !== ($nextOffset = \strpos($data, $separator, $offset))) {
            (yield \substr($data, $offset, $nextOffset - $offset));
            $offset = $nextOffset + \strlen($separator);
        }
        (yield \substr($data, $offset));
    })();
}
function count($iterable) : int
{
    if (\is_array($iterable) || $iterable instanceof \Countable) {
        return \count($iterable);
    }
    if (!$iterable instanceof \Traversable) {
        throw new \InvalidArgumentException('Argument must be iterable or implement Countable');
    }
    $count = 0;
    foreach ($iterable as $_) {
        ++$count;
    }
    return $count;
}
function isEmpty($iterable) : bool
{
    if (\is_array($iterable) || $iterable instanceof \Countable) {
        return \count($iterable) == 0;
    }
    if ($iterable instanceof \Iterator) {
        return !$iterable->valid();
    } else {
        if ($iterable instanceof \IteratorAggregate) {
            return !$iterable->getIterator()->valid();
        } else {
            throw new \InvalidArgumentException('Argument must be iterable or implement Countable');
        }
    }
}
function recurse(callable $function, iterable $iterable)
{
    return $function(map(function ($value) use($function) {
        return isIterable($value) ? recurse($function, $value) : $value;
    }, $iterable));
}
function toIter(iterable $iterable) : \Iterator
{
    if (\is_array($iterable)) {
        return new \ArrayIterator($iterable);
    }
    if ($iterable instanceof \Iterator) {
        return $iterable;
    }
    if ($iterable instanceof \IteratorAggregate) {
        return $iterable->getIterator();
    }
    $generator = function () use($iterable) {
        yield from $iterable;
    };
    return $generator();
}
function toArray(iterable $iterable) : array
{
    $array = [];
    foreach ($iterable as $value) {
        $array[] = $value;
    }
    return $array;
}
function toArrayWithKeys(iterable $iterable) : array
{
    $array = [];
    foreach ($iterable as $key => $value) {
        $array[$key] = $value;
    }
    return $array;
}
function isIterable($value)
{
    return \is_array($value) || $value instanceof \Traversable;
}
