<?php

namespace _HumbugBox9658796bb9f0\Symfony\Component\Finder\Iterator;

use _HumbugBox9658796bb9f0\Symfony\Component\Finder\Exception\AccessDeniedException;
use _HumbugBox9658796bb9f0\Symfony\Component\Finder\SplFileInfo;
class RecursiveDirectoryIterator extends \RecursiveDirectoryIterator
{
    private $ignoreUnreadableDirs;
    private $rewindable;
    private $rootPath;
    private $subPath;
    private $directorySeparator = '/';
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
    #[\ReturnTypeWillChange]
    public function current()
    {
        if (null === ($subPathname = $this->subPath)) {
            $subPathname = $this->subPath = $this->getSubPath();
        }
        if ('' !== $subPathname) {
            $subPathname .= $this->directorySeparator;
        }
        $subPathname .= $this->getFilename();
        if ('/' !== ($basePath = $this->rootPath)) {
            $basePath .= $this->directorySeparator;
        }
        return new SplFileInfo($basePath . $subPathname, $this->subPath, $subPathname);
    }
    #[\ReturnTypeWillChange]
    public function hasChildren($allowLinks = \false)
    {
        $hasChildren = parent::hasChildren($allowLinks);
        if (!$hasChildren || !$this->ignoreUnreadableDirs) {
            return $hasChildren;
        }
        try {
            parent::getChildren();
            return \true;
        } catch (\UnexpectedValueException $e) {
            return \false;
        }
    }
    #[\ReturnTypeWillChange]
    public function getChildren()
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
    #[\ReturnTypeWillChange]
    public function rewind()
    {
        if (\false === $this->isRewindable()) {
            return;
        }
        parent::rewind();
    }
    public function isRewindable()
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
