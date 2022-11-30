<?php

namespace _HumbugBox9658796bb9f0\Symfony\Component\Finder\Iterator;

/**
@extends
*/
class PathFilterIterator extends MultiplePcreFilterIterator
{
    #[\ReturnTypeWillChange]
    public function accept()
    {
        $filename = $this->current()->getRelativePathname();
        if ('\\' === \DIRECTORY_SEPARATOR) {
            $filename = \str_replace('\\', '/', $filename);
        }
        return $this->isAccepted($filename);
    }
    protected function toRegex(string $str)
    {
        return $this->isRegex($str) ? $str : '/' . \preg_quote($str, '/') . '/';
    }
}
