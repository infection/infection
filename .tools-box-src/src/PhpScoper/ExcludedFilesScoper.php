<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\KevinGH\Box\PhpScoper;

use function array_flip;
use function array_key_exists;
use function func_get_args;
use _HumbugBoxb47773b41c19\Humbug\PhpScoper\Scoper\Scoper as PhpScoperScoper;
final class ExcludedFilesScoper implements PhpScoperScoper
{
    private array $excludedFilePathsAsKeys;
    public function __construct(private PhpScoperScoper $decoratedScoper, string ...$excludedFilePaths)
    {
        $this->excludedFilePathsAsKeys = array_flip($excludedFilePaths);
    }
    public function scope(string $filePath, string $contents) : string
    {
        if (array_key_exists($filePath, $this->excludedFilePathsAsKeys)) {
            return $contents;
        }
        return $this->decoratedScoper->scope(...func_get_args());
    }
}
