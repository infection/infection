<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\Humbug\PhpScoper\Scoper;

final class NullScoper implements Scoper
{
    public function scope(string $filePath, string $contents) : string
    {
        return $contents;
    }
}
