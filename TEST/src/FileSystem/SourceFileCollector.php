<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\FileSystem;

use _HumbugBox9658796bb9f0\Symfony\Component\Finder\Finder;
use _HumbugBox9658796bb9f0\Symfony\Component\Finder\SplFileInfo;
class SourceFileCollector
{
    public function collectFiles(array $sourceDirectories, array $excludeDirectories) : iterable
    {
        if ($sourceDirectories === []) {
            return [];
        }
        $finder = Finder::create()->exclude($excludeDirectories)->in($sourceDirectories)->files()->name('*.php');
        foreach ($excludeDirectories as $excludeDirectory) {
            $finder->notPath($excludeDirectory);
        }
        return $finder;
    }
}
