<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0;

include 'vendor/autoload.php';
use function _HumbugBox9658796bb9f0\Pipeline\map;
use function _HumbugBox9658796bb9f0\Pipeline\take;
$iterable = \range(5, 7);
$pipeline = take($iterable);
$pipeline->zip(\range(1, 3), map(function () {
    (yield 1);
    (yield 2);
    (yield 3);
}));
$pipeline->unpack(function (int $a, int $b, int $c) {
    return $a - $b - $c;
});
$pipeline->map(function ($i) {
    (yield $i ** 2);
    (yield $i ** 3);
});
$pipeline->cast(function ($i) {
    return $i - 1;
});
$pipeline->map(function ($i) {
    (yield [$i, 2]);
    (yield [$i, 4]);
});
$pipeline->unpack(function ($i, $j) {
    (yield $i * $j);
});
$pipeline->map(function ($i) {
    if ($i > 50) {
        (yield $i);
    }
});
$pipeline->filter(function ($value) {
    return $value > 100;
});
$value = $pipeline->reduce(function ($carry, $valuetem) {
    return $carry + $valuetem;
}, 0);
\var_dump($value);
$sum = take([1, 2, 3])->reduce();
\var_dump($sum);
$leaguePipeline = (new \_HumbugBox9658796bb9f0\League\Pipeline\Pipeline())->pipe(function ($payload) {
    return $payload + 1;
})->pipe(function ($payload) {
    return $payload * 2;
});
$pipeline = new \_HumbugBox9658796bb9f0\Pipeline\Standard(new \ArrayIterator([10, 20, 30]));
$pipeline->map($leaguePipeline);
$result = \iterator_to_array($pipeline);
\var_dump($result);
$pipeline = map(function () {
    (yield 1);
    (yield 2);
});
$pipeline->map(function ($value) {
    (yield $value + 1);
    (yield $value + 2);
});
$pipeline->slice(1, -1);
$arrayResult = $pipeline->toArray();
\var_dump($arrayResult);
