<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\FileSystem;

use function array_filter;
use function array_map;
use function explode;
use _HumbugBox9658796bb9f0\Infection\FileSystem\Finder\Iterator\RealPathFilterIterator;
use _HumbugBox9658796bb9f0\Infection\TestFramework\Coverage\Trace;
use Iterator;
use SplFileInfo;
use _HumbugBox9658796bb9f0\Symfony\Component\Finder\Iterator\PathFilterIterator;
class SourceFileFilter implements FileFilter
{
    private array $filters;
    public function __construct(string $filter, private array $excludeDirectories)
    {
        $this->filters = array_filter(array_map('trim', explode(',', $filter)));
    }
    public function getFilters() : array
    {
        return $this->filters;
    }
    public function filter(iterable $input) : iterable
    {
        $iterator = $this->iterableToIterator($input);
        if ($this->filters !== []) {
            $iterator = new RealPathFilterIterator($iterator, $this->filters, []);
        }
        if ($this->excludeDirectories !== []) {
            $iterator = new PathFilterIterator($iterator, [], $this->excludeDirectories);
        }
        return $iterator;
    }
    private function iterableToIterator(iterable $input) : Iterator
    {
        if ($input instanceof Iterator) {
            return $input;
        }
        return (static function () use($input) : Iterator {
            yield from $input;
        })();
    }
}
