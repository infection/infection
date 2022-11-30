<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\KevinGH\Box\PhpScoper;

use _HumbugBoxb47773b41c19\Humbug\PhpScoper\Patcher\Patcher;
use _HumbugBoxb47773b41c19\Humbug\PhpScoper\Patcher\PatcherChain;
use _HumbugBoxb47773b41c19\KevinGH\Box\NotInstantiable;
use _HumbugBoxb47773b41c19\Laravel\SerializableClosure\SerializableClosure;
final class PatcherFactory
{
    use NotInstantiable;
    public static function createSerializablePatchers(Patcher $patcher) : Patcher
    {
        if (!$patcher instanceof PatcherChain) {
            return $patcher;
        }
        $serializablePatchers = \array_map(static fn(callable $patcher) => SerializablePatcher::create($patcher), $patcher->getPatchers());
        return new PatcherChain($serializablePatchers);
    }
}
