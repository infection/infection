<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\Humbug\PhpScoper\Scoper;

use _HumbugBoxb47773b41c19\Humbug\PhpScoper\Patcher\Patcher;
use function func_get_args;
final class PatchScoper implements Scoper
{
    public function __construct(private readonly Scoper $decoratedScoper, private readonly string $prefix, private readonly Patcher $patcher)
    {
    }
    public function scope(string $filePath, string $contents) : string
    {
        return ($this->patcher)($filePath, $this->prefix, $this->decoratedScoper->scope(...func_get_args()));
    }
}
