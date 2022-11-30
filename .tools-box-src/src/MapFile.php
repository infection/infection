<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\KevinGH\Box;

use function _HumbugBoxb47773b41c19\KevinGH\Box\FileSystem\make_path_relative;
use function preg_quote;
use function preg_replace;
final class MapFile
{
    public function __construct(private string $basePath, private array $map)
    {
    }
    public function __invoke(string $path) : ?string
    {
        $relativePath = make_path_relative($path, $this->basePath);
        foreach ($this->map as $item) {
            foreach ($item as $match => $replace) {
                if ('' === $match) {
                    return $replace . '/' . $relativePath;
                }
                if (\str_starts_with($relativePath, $match)) {
                    return preg_replace('/^' . preg_quote($match, '/') . '/', $replace, $relativePath);
                }
            }
        }
        return $relativePath;
    }
    public function getMap() : array
    {
        return $this->map;
    }
}
