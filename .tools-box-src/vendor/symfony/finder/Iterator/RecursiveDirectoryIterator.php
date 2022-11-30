<?php

namespace _HumbugBoxb47773b41c19\Symfony\Component\Finder\Iterator;

use _HumbugBoxb47773b41c19\Symfony\Component\Finder\Exception\AccessDeniedException;
use _HumbugBoxb47773b41c19\Symfony\Component\Finder\SplFileInfo;
/**
@extends
*/
class RecursiveDirectoryIterator extends \RecursiveDirectoryIterator
{
    private bool $ignoreUnreadableDirs;
    private ?bool $rewindable = null;
    private string $rootPath;
    private string $subPath;
    private string $directorySeparator = '/';
    public function __construct(string $path, int $flags, bool $ignoreUnreadableDirs = \false)
    {
        if ($flags & (self::CURRENT_AS_PATHNAME | self::CURRENT_AS_SELF)) {
            throw new \RuntimeException('This iterator only support returning current as fileinfo.');
        }
        parent::__construct($path, $flags);
        $this->ignoreUnreadableDirs = $ignoreUnreadableDirs;
        $this->rootPath = $path;
        if ('/' !== \DIRECTORY_SEPARATOR && !($flags & self::UNIX_PATHS)) {
            $this->directorySeparator = \DIRECTORY_SEPARATOR;
        }
    }
    public function current() : SplFileInfo
    {
        if (!isset($this->subPath)) {
            $this->subPath = $this->getSubPath();
        }
        $subPathname = $this->subPath;
        if ('' !== $subPathname) {
            $subPathname .= $this->directorySeparator;
        }
        $subPathname .= $this->getFilename();
        if ('/' !== ($basePath = $this->rootPath)) {
            $basePath .= $this->directorySeparator;
        }
        return new SplFileInfo($basePath . $subPathname, $this->subPath, $subPathname);
    }
    public function hasChildren(bool $allowLinks = \false) : bool
    {
        $hasChildren = parent::hasChildren($allowLinks);
        if (!$hasChildren || !$this->ignoreUnreadableDirs) {
            return $hasChildren;
        }
        try {
            parent::getChildren();
            return \true;
        } catch (\UnexpectedValueException) {
            return \false;
        }
    }
    public function getChildren() : \RecursiveDirectoryIterator
    {
        try {
            $children = parent::getChildren();
            if ($children instanceof self) {
                $children->ignoreUnreadableDirs = $this->ignoreUnreadableDirs;
                $children->rewindable =& $this->rewindable;
                $children->rootPath = $this->rootPath;
            }
            return $children;
        } catch (\UnexpectedValueException $e) {
            throw new AccessDeniedException($e->getMessage(), $e->getCode(), $e);
        }
    }
    public function rewind() : void
    {
        if (\false === $this->isRewindable()) {
            return;
        }
        parent::rewind();
    }
    public function isRewindable() : bool
    {
        if (null !== $this->rewindable) {
            return $this->rewindable;
        }
        if (\false !== ($stream = @\opendir($this->getPath()))) {
            $infos = \stream_get_meta_data($stream);
            \closedir($stream);
            if ($infos['seekable']) {
                return $this->rewindable = \true;
            }
        }
        return $this->rewindable = \false;
    }
}
