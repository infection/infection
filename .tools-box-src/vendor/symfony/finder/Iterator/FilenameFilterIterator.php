<?php

namespace _HumbugBoxb47773b41c19\Symfony\Component\Finder\Iterator;

use _HumbugBoxb47773b41c19\Symfony\Component\Finder\Glob;
/**
@extends
*/
class FilenameFilterIterator extends MultiplePcreFilterIterator
{
    public function accept() : bool
    {
        return $this->isAccepted($this->current()->getFilename());
    }
    protected function toRegex(string $str) : string
    {
        return $this->isRegex($str) ? $str : Glob::toRegex($str);
    }
}
