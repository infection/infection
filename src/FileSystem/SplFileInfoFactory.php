<?php

declare(strict_types=1);

namespace Infection\FileSystem;

use Infection\CannotBeInstantiated;
use SplFileInfo;
use Symfony\Component\Filesystem\Path;
use Symfony\Component\Finder\SplFileInfo as SymfonyFinderSplFileInfo;

final class SplFileInfoFactory
{
    use CannotBeInstantiated;

    public static function fromPath(string $path, string $basePath): SymfonyFinderSplFileInfo
    {
        return self::create(
            new SplFileInfo($path),
            $basePath,
        );
    }

    public static function create(
        SplFileInfo $splFileInfo,
        string $basePath,
    ): SymfonyFinderSplFileInfo
    {
        $realPath = $splFileInfo->getRealPath();

        // If no base path provided, use the directory of the file as a base
        if ($basePath === '') {
            $basePath = $splFileInfo->getPath();
            $relativePath = '';
            $relativePathname = $splFileInfo->getFilename();
        } else {
            // Calculate relative paths from the base path using Symfony Path
            $canonicalBasePath = Path::canonicalize($basePath);
            $canonicalFilePath = Path::canonicalize($splFileInfo->getPath());
            $canonicalRealPath = Path::canonicalize($realPath);

            $relativePath = Path::makeRelative($canonicalFilePath, $canonicalBasePath);
            $relativePathname = Path::makeRelative($canonicalRealPath, $canonicalBasePath);

            // Ensure an empty relative path is handled correctly
            if ($relativePath === '.') {
                $relativePath = '';
            }
        }

        return new SymfonyFinderSplFileInfo(
            $realPath,
            $relativePath,
            $relativePathname,
        );
    }
}
