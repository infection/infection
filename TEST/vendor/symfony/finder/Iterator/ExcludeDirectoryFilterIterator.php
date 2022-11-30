<?php

namespace _HumbugBox9658796bb9f0\Symfony\Component\Finder\Iterator;

/**
@extends
@implements
*/
class ExcludeDirectoryFilterIterator extends \FilterIterator implements \RecursiveIterator
{
    private $iterator;
    private $isRecursive;
    private $excludedDirs = [];
    private $excludedPattern;
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
    #[\ReturnTypeWillChange]
    public function accept()
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
    #[\ReturnTypeWillChange]
    public function hasChildren()
    {
        return $this->isRecursive && $this->iterator->hasChildren();
    }
    #[\ReturnTypeWillChange]
    public function getChildren()
    {
        $children = new self($this->iterator->getChildren(), []);
        $children->excludedDirs = $this->excludedDirs;
        $children->excludedPattern = $this->excludedPattern;
        return $children;
    }
}
