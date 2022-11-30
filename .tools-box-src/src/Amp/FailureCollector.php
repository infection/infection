<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\KevinGH\Box\Amp;

use _HumbugBoxb47773b41c19\Amp\MultiReasonException;
use function array_map;
use function array_unique;
use _HumbugBoxb47773b41c19\KevinGH\Box\NotInstantiable;
use Throwable;
final class FailureCollector
{
    use NotInstantiable;
    public static function collectReasons(MultiReasonException $exception) : array
    {
        return array_unique(array_map(static fn(Throwable $throwable) => $throwable->getMessage(), $exception->getReasons()));
    }
}
