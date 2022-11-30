<?php

namespace _HumbugBox9658796bb9f0\Symfony\Component\Finder\Iterator;

/**
@extends
*/
class FileTypeFilterIterator extends \FilterIterator
{
    public const ONLY_FILES = 1;
    public const ONLY_DIRECTORIES = 2;
    private $mode;
    public function __construct(\Iterator $iterator, int $mode)
    {
        $this->mode = $mode;
        parent::__construct($iterator);
    }
    #[\ReturnTypeWillChange]
    public function accept()
    {
        $fileinfo = $this->current();
        if (self::ONLY_DIRECTORIES === (self::ONLY_DIRECTORIES & $this->mode) && $fileinfo->isFile()) {
            return \false;
        } elseif (self::ONLY_FILES === (self::ONLY_FILES & $this->mode) && $fileinfo->isDir()) {
            return \false;
        }
        return \true;
    }
}
