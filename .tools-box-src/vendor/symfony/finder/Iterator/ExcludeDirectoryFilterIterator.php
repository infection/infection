<?php

namespace _HumbugBoxb47773b41c19\Symfony\Component\Finder\Iterator;

use _HumbugBoxb47773b41c19\Symfony\Component\Finder\SplFileInfo;
/**
@extends
@implements
*/
class ExcludeDirectoryFilterIterator extends \FilterIterator implements \RecursiveIterator
{
    private \Iterator $iterator;
    private bool $isRecursive;
    private array $excludedDirs = [];
    private ?string $excludedPattern = null;
    public function __construct(\Iterator $iterator, array $directories)
    {
        $this->iterator = $iterator;
        $this->isRecursive = $iterator instanceof \RecursiveIterator;
        $patterns = [];
        foreach ($directories as $directory) {
            $directory = \rtrim($directory, '/');
            if (!$this->isRecursive || \str_contains($directory, '/')) {
                $patterns[] = \preg_quote($directory, '#');
            } else {
                $this->excludedDirs[$directory] = \true;
            }
        }
        if ($patterns) {
            $this->excludedPattern = '#(?:^|/)(?:' . \implode('|', $patterns) . ')(?:/|$)#';
        }
        parent::__construct($iterator);
    }
    public function accept() : bool
    {
        if ($this->isRecursive && isset($this->excludedDirs[$this->getFilename()]) && $this->isDir()) {
            return \false;
        }
        if ($this->excludedPattern) {
            $path = $this->isDir() ? $this->current()->getRelativePathname() : $this->current()->getRelativePath();
            $path = \str_replace('\\', '/', $path);
            return !\preg_match($this->excludedPattern, $path);
        }
        return \true;
    }
    public function hasChildren() : bool
    {
        return $this->isRecursive && $this->iterator->hasChildren();
    }
    public function getChildren() : self
    {
        $children = new self($this->iterator->getChildren(), []);
        $children->excludedDirs = $this->excludedDirs;
        $children->excludedPattern = $this->excludedPattern;
        return $children;
    }
}
