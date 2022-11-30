<?php

namespace _HumbugBox9658796bb9f0\Symfony\Component\Finder;

use _HumbugBox9658796bb9f0\Symfony\Component\Finder\Comparator\DateComparator;
use _HumbugBox9658796bb9f0\Symfony\Component\Finder\Comparator\NumberComparator;
use _HumbugBox9658796bb9f0\Symfony\Component\Finder\Exception\DirectoryNotFoundException;
use _HumbugBox9658796bb9f0\Symfony\Component\Finder\Iterator\CustomFilterIterator;
use _HumbugBox9658796bb9f0\Symfony\Component\Finder\Iterator\DateRangeFilterIterator;
use _HumbugBox9658796bb9f0\Symfony\Component\Finder\Iterator\DepthRangeFilterIterator;
use _HumbugBox9658796bb9f0\Symfony\Component\Finder\Iterator\ExcludeDirectoryFilterIterator;
use _HumbugBox9658796bb9f0\Symfony\Component\Finder\Iterator\FilecontentFilterIterator;
use _HumbugBox9658796bb9f0\Symfony\Component\Finder\Iterator\FilenameFilterIterator;
use _HumbugBox9658796bb9f0\Symfony\Component\Finder\Iterator\LazyIterator;
use _HumbugBox9658796bb9f0\Symfony\Component\Finder\Iterator\SizeRangeFilterIterator;
use _HumbugBox9658796bb9f0\Symfony\Component\Finder\Iterator\SortableIterator;
/**
@implements
*/
class Finder implements \IteratorAggregate, \Countable
{
    public const IGNORE_VCS_FILES = 1;
    public const IGNORE_DOT_FILES = 2;
    public const IGNORE_VCS_IGNORED_FILES = 4;
    private $mode = 0;
    private $names = [];
    private $notNames = [];
    private $exclude = [];
    private $filters = [];
    private $depths = [];
    private $sizes = [];
    private $followLinks = \false;
    private $reverseSorting = \false;
    private $sort = \false;
    private $ignore = 0;
    private $dirs = [];
    private $dates = [];
    private $iterators = [];
    private $contains = [];
    private $notContains = [];
    private $paths = [];
    private $notPaths = [];
    private $ignoreUnreadableDirs = \false;
    private static $vcsPatterns = ['.svn', '_svn', 'CVS', '_darcs', '.arch-params', '.monotone', '.bzr', '.git', '.hg'];
    public function __construct()
    {
        $this->ignore = static::IGNORE_VCS_FILES | static::IGNORE_DOT_FILES;
    }
    public static function create()
    {
        return new static();
    }
    public function directories()
    {
        $this->mode = Iterator\FileTypeFilterIterator::ONLY_DIRECTORIES;
        return $this;
    }
    public function files()
    {
        $this->mode = Iterator\FileTypeFilterIterator::ONLY_FILES;
        return $this;
    }
    public function depth($levels)
    {
        foreach ((array) $levels as $level) {
            $this->depths[] = new Comparator\NumberComparator($level);
        }
        return $this;
    }
    public function date($dates)
    {
        foreach ((array) $dates as $date) {
            $this->dates[] = new Comparator\DateComparator($date);
        }
        return $this;
    }
    public function name($patterns)
    {
        $this->names = \array_merge($this->names, (array) $patterns);
        return $this;
    }
    public function notName($patterns)
    {
        $this->notNames = \array_merge($this->notNames, (array) $patterns);
        return $this;
    }
    public function contains($patterns)
    {
        $this->contains = \array_merge($this->contains, (array) $patterns);
        return $this;
    }
    public function notContains($patterns)
    {
        $this->notContains = \array_merge($this->notContains, (array) $patterns);
        return $this;
    }
    public function path($patterns)
    {
        $this->paths = \array_merge($this->paths, (array) $patterns);
        return $this;
    }
    public function notPath($patterns)
    {
        $this->notPaths = \array_merge($this->notPaths, (array) $patterns);
        return $this;
    }
    public function size($sizes)
    {
        foreach ((array) $sizes as $size) {
            $this->sizes[] = new Comparator\NumberComparator($size);
        }
        return $this;
    }
    public function exclude($dirs)
    {
        $this->exclude = \array_merge($this->exclude, (array) $dirs);
        return $this;
    }
    public function ignoreDotFiles(bool $ignoreDotFiles)
    {
        if ($ignoreDotFiles) {
            $this->ignore |= static::IGNORE_DOT_FILES;
        } else {
            $this->ignore &= ~static::IGNORE_DOT_FILES;
        }
        return $this;
    }
    public function ignoreVCS(bool $ignoreVCS)
    {
        if ($ignoreVCS) {
            $this->ignore |= static::IGNORE_VCS_FILES;
        } else {
            $this->ignore &= ~static::IGNORE_VCS_FILES;
        }
        return $this;
    }
    public function ignoreVCSIgnored(bool $ignoreVCSIgnored)
    {
        if ($ignoreVCSIgnored) {
            $this->ignore |= static::IGNORE_VCS_IGNORED_FILES;
        } else {
            $this->ignore &= ~static::IGNORE_VCS_IGNORED_FILES;
        }
        return $this;
    }
    public static function addVCSPattern($pattern)
    {
        foreach ((array) $pattern as $p) {
            self::$vcsPatterns[] = $p;
        }
        self::$vcsPatterns = \array_unique(self::$vcsPatterns);
    }
    public function sort(\Closure $closure)
    {
        $this->sort = $closure;
        return $this;
    }
    public function sortByName(bool $useNaturalSort = \false)
    {
        $this->sort = $useNaturalSort ? Iterator\SortableIterator::SORT_BY_NAME_NATURAL : Iterator\SortableIterator::SORT_BY_NAME;
        return $this;
    }
    public function sortByType()
    {
        $this->sort = Iterator\SortableIterator::SORT_BY_TYPE;
        return $this;
    }
    public function sortByAccessedTime()
    {
        $this->sort = Iterator\SortableIterator::SORT_BY_ACCESSED_TIME;
        return $this;
    }
    public function reverseSorting()
    {
        $this->reverseSorting = \true;
        return $this;
    }
    public function sortByChangedTime()
    {
        $this->sort = Iterator\SortableIterator::SORT_BY_CHANGED_TIME;
        return $this;
    }
    public function sortByModifiedTime()
    {
        $this->sort = Iterator\SortableIterator::SORT_BY_MODIFIED_TIME;
        return $this;
    }
    public function filter(\Closure $closure)
    {
        $this->filters[] = $closure;
        return $this;
    }
    public function followLinks()
    {
        $this->followLinks = \true;
        return $this;
    }
    public function ignoreUnreadableDirs(bool $ignore = \true)
    {
        $this->ignoreUnreadableDirs = $ignore;
        return $this;
    }
    public function in($dirs)
    {
        $resolvedDirs = [];
        foreach ((array) $dirs as $dir) {
            if (\is_dir($dir)) {
                $resolvedDirs[] = [$this->normalizeDir($dir)];
            } elseif ($glob = \glob($dir, (\defined('GLOB_BRACE') ? \GLOB_BRACE : 0) | \GLOB_ONLYDIR | \GLOB_NOSORT)) {
                \sort($glob);
                $resolvedDirs[] = \array_map([$this, 'normalizeDir'], $glob);
            } else {
                throw new DirectoryNotFoundException(\sprintf('The "%s" directory does not exist.', $dir));
            }
        }
        $this->dirs = \array_merge($this->dirs, ...$resolvedDirs);
        return $this;
    }
    #[\ReturnTypeWillChange]
    public function getIterator()
    {
        if (0 === \count($this->dirs) && 0 === \count($this->iterators)) {
            throw new \LogicException('You must call one of in() or append() methods before iterating over a Finder.');
        }
        if (1 === \count($this->dirs) && 0 === \count($this->iterators)) {
            $iterator = $this->searchInDirectory($this->dirs[0]);
            if ($this->sort || $this->reverseSorting) {
                $iterator = (new Iterator\SortableIterator($iterator, $this->sort, $this->reverseSorting))->getIterator();
            }
            return $iterator;
        }
        $iterator = new \AppendIterator();
        foreach ($this->dirs as $dir) {
            $iterator->append(new \IteratorIterator(new LazyIterator(function () use($dir) {
                return $this->searchInDirectory($dir);
            })));
        }
        foreach ($this->iterators as $it) {
            $iterator->append($it);
        }
        if ($this->sort || $this->reverseSorting) {
            $iterator = (new Iterator\SortableIterator($iterator, $this->sort, $this->reverseSorting))->getIterator();
        }
        return $iterator;
    }
    public function append(iterable $iterator)
    {
        if ($iterator instanceof \IteratorAggregate) {
            $this->iterators[] = $iterator->getIterator();
        } elseif ($iterator instanceof \Iterator) {
            $this->iterators[] = $iterator;
        } elseif (\is_iterable($iterator)) {
            $it = new \ArrayIterator();
            foreach ($iterator as $file) {
                $file = $file instanceof \SplFileInfo ? $file : new \SplFileInfo($file);
                $it[$file->getPathname()] = $file;
            }
            $this->iterators[] = $it;
        } else {
            throw new \InvalidArgumentException('Finder::append() method wrong argument type.');
        }
        return $this;
    }
    public function hasResults()
    {
        foreach ($this->getIterator() as $_) {
            return \true;
        }
        return \false;
    }
    #[\ReturnTypeWillChange]
    public function count()
    {
        return \iterator_count($this->getIterator());
    }
    private function searchInDirectory(string $dir) : \Iterator
    {
        $exclude = $this->exclude;
        $notPaths = $this->notPaths;
        if (static::IGNORE_VCS_FILES === (static::IGNORE_VCS_FILES & $this->ignore)) {
            $exclude = \array_merge($exclude, self::$vcsPatterns);
        }
        if (static::IGNORE_DOT_FILES === (static::IGNORE_DOT_FILES & $this->ignore)) {
            $notPaths[] = '#(^|/)\\..+(/|$)#';
        }
        $minDepth = 0;
        $maxDepth = \PHP_INT_MAX;
        foreach ($this->depths as $comparator) {
            switch ($comparator->getOperator()) {
                case '>':
                    $minDepth = $comparator->getTarget() + 1;
                    break;
                case '>=':
                    $minDepth = $comparator->getTarget();
                    break;
                case '<':
                    $maxDepth = $comparator->getTarget() - 1;
                    break;
                case '<=':
                    $maxDepth = $comparator->getTarget();
                    break;
                default:
                    $minDepth = $maxDepth = $comparator->getTarget();
            }
        }
        $flags = \RecursiveDirectoryIterator::SKIP_DOTS;
        if ($this->followLinks) {
            $flags |= \RecursiveDirectoryIterator::FOLLOW_SYMLINKS;
        }
        $iterator = new Iterator\RecursiveDirectoryIterator($dir, $flags, $this->ignoreUnreadableDirs);
        if ($exclude) {
            $iterator = new Iterator\ExcludeDirectoryFilterIterator($iterator, $exclude);
        }
        $iterator = new \RecursiveIteratorIterator($iterator, \RecursiveIteratorIterator::SELF_FIRST);
        if ($minDepth > 0 || $maxDepth < \PHP_INT_MAX) {
            $iterator = new Iterator\DepthRangeFilterIterator($iterator, $minDepth, $maxDepth);
        }
        if ($this->mode) {
            $iterator = new Iterator\FileTypeFilterIterator($iterator, $this->mode);
        }
        if ($this->names || $this->notNames) {
            $iterator = new Iterator\FilenameFilterIterator($iterator, $this->names, $this->notNames);
        }
        if ($this->contains || $this->notContains) {
            $iterator = new Iterator\FilecontentFilterIterator($iterator, $this->contains, $this->notContains);
        }
        if ($this->sizes) {
            $iterator = new Iterator\SizeRangeFilterIterator($iterator, $this->sizes);
        }
        if ($this->dates) {
            $iterator = new Iterator\DateRangeFilterIterator($iterator, $this->dates);
        }
        if ($this->filters) {
            $iterator = new Iterator\CustomFilterIterator($iterator, $this->filters);
        }
        if ($this->paths || $notPaths) {
            $iterator = new Iterator\PathFilterIterator($iterator, $this->paths, $notPaths);
        }
        if (static::IGNORE_VCS_IGNORED_FILES === (static::IGNORE_VCS_IGNORED_FILES & $this->ignore)) {
            $iterator = new Iterator\VcsIgnoredFilterIterator($iterator, $dir);
        }
        return $iterator;
    }
    private function normalizeDir(string $dir) : string
    {
        if ('/' === $dir) {
            return $dir;
        }
        $dir = \rtrim($dir, '/' . \DIRECTORY_SEPARATOR);
        if (\preg_match('#^(ssh2\\.)?s?ftp://#', $dir)) {
            $dir .= '/';
        }
        return $dir;
    }
}
