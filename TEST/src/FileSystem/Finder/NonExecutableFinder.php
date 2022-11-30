<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\FileSystem\Finder;

use function array_merge;
use function explode;
use function file_exists;
use function getenv;
use const PATH_SEPARATOR;
use function _HumbugBox9658796bb9f0\Safe\sprintf;
final class NonExecutableFinder
{
    public function searchNonExecutables(array $probableNames, array $extraDirectories = []) : ?string
    {
        $path = getenv('PATH') ?: getenv('Path');
        if ($path === \false || $path === '') {
            return null;
        }
        $dirs = array_merge(explode(PATH_SEPARATOR, $path), $extraDirectories);
        foreach ($dirs as $dir) {
            foreach ($probableNames as $name) {
                $fileName = sprintf('%s/%s', $dir, $name);
                if (file_exists($fileName)) {
                    return $fileName;
                }
            }
        }
        return null;
    }
}
