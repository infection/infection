<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\Humbug\PhpScoper\Patcher;

final class NullPatcher implements Patcher
{
    public function __invoke(string $filePath, string $prefix, string $contents) : string
    {
        return $contents;
    }
}
