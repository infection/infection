<?php

namespace _HumbugBoxb47773b41c19\Symfony\Component\Finder\Iterator;

use _HumbugBoxb47773b41c19\Symfony\Component\Finder\SplFileInfo;
/**
@extends
*/
class FilecontentFilterIterator extends MultiplePcreFilterIterator
{
    public function accept() : bool
    {
        if (!$this->matchRegexps && !$this->noMatchRegexps) {
            return \true;
        }
        $fileinfo = $this->current();
        if ($fileinfo->isDir() || !$fileinfo->isReadable()) {
            return \false;
        }
        $content = $fileinfo->getContents();
        if (!$content) {
            return \false;
        }
        return $this->isAccepted($content);
    }
    protected function toRegex(string $str) : string
    {
        return $this->isRegex($str) ? $str : '/' . \preg_quote($str, '/') . '/';
    }
}
