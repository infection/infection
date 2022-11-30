<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\Humbug\PhpScoper\Scoper;

use _HumbugBoxb47773b41c19\Humbug\PhpScoper\Throwable\Exception\ParsingException;
interface Scoper
{
    public function scope(string $filePath, string $contents) : string;
}
