<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\Humbug\PhpScoper\Patcher;

use function array_reduce;
final class PatcherChain implements Patcher
{
    public function __construct(private array $patchers = [])
    {
    }
    public function __invoke(string $filePath, string $prefix, string $contents) : string
    {
        return array_reduce($this->patchers, static fn(string $contents, callable $patcher) => $patcher($filePath, $prefix, $contents), $contents);
    }
    public function getPatchers() : array
    {
        return $this->patchers;
    }
}
