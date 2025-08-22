<?php

declare(strict_types=1);

namespace newSrc\Framework;

use Symfony\Component\Filesystem\Filesystem as SymfonyFilesystem;
use Symfony\Component\Finder\Finder;
use function is_dir;
use function is_file;
use function is_readable;

final class Filesystem extends SymfonyFilesystem
{
    public function isReadableFile(string $path): bool
    {
        return is_file($path) && is_readable($path);
    }

    public function isReadableDirectory(string $path): bool
    {
        return is_dir($path) && is_readable($path);
    }

    public function createFinder(): Finder
    {
        return Finder::create();
    }
}
