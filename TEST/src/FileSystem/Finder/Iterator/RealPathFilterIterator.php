<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\FileSystem\Finder\Iterator;

use const DIRECTORY_SEPARATOR;
use function preg_quote;
use ReturnTypeWillChange;
use function str_replace;
use _HumbugBox9658796bb9f0\Symfony\Component\Finder\Iterator\MultiplePcreFilterIterator;
/**
@template-covariant
@template-covariant
@extends
*/
final class RealPathFilterIterator extends MultiplePcreFilterIterator
{
    #[ReturnTypeWillChange]
    public function accept()
    {
        $filename = $this->current()->getRealPath();
        if ('\\' === DIRECTORY_SEPARATOR) {
            $filename = str_replace('\\', '/', $filename);
        }
        return $this->isAccepted($filename);
    }
    protected function toRegex($str) : string
    {
        return $this->isRegex($str) ? $str : '/' . preg_quote($str, '/') . '/';
    }
}
