<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\KevinGH\Box\PhpScoper;

use Closure;
use function func_get_args;
use _HumbugBoxb47773b41c19\Humbug\PhpScoper\Patcher\Patcher;
use _HumbugBoxb47773b41c19\Laravel\SerializableClosure\SerializableClosure;
final class SerializablePatcher implements Patcher
{
    public static function create(callable $patcher) : self
    {
        if ($patcher instanceof Patcher) {
            $patcher = static fn(mixed ...$args) => $patcher(...$args);
        }
        return new self(new SerializableClosure($patcher));
    }
    private function __construct(private Closure|SerializableClosure $patch)
    {
    }
    public function __invoke(string $filePath, string $prefix, string $contents) : string
    {
        return ($this->patch)(...func_get_args());
    }
}
