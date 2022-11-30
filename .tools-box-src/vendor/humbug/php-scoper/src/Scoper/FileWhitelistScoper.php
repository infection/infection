<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\Humbug\PhpScoper\Scoper;

use function array_flip;
use function array_key_exists;
use function func_get_args;
final class FileWhitelistScoper implements Scoper
{
    private readonly array $filePaths;
    public function __construct(private readonly Scoper $decoratedScoper, string ...$filePaths)
    {
        $this->filePaths = array_flip($filePaths);
    }
    public function scope(string $filePath, string $contents) : string
    {
        if (array_key_exists($filePath, $this->filePaths)) {
            return $contents;
        }
        return $this->decoratedScoper->scope(...func_get_args());
    }
}
