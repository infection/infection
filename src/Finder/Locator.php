<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */
declare(strict_types=1);

namespace Infection\Finder;

use Infection\Finder\Exception\LocatorException;
use Symfony\Component\Filesystem\Filesystem;

class Locator
{
    /**
     * @var string[]
     */
    private $paths;

    /**
     * @var Filesystem
     */
    private $filesystem;

    public function __construct(array $paths, Filesystem $filesystem)
    {
        $this->paths = $paths;
        $this->filesystem = $filesystem;
    }

    public function locate(string $name, string $additionalPath = null)
    {
        if ($this->filesystem->isAbsolutePath($name)) {
            if ($this->filesystem->exists($name)) {
                return \realpath($name);
            }

            throw LocatorException::fileOrDirectoryDoesNotExist($name);
        }

        $paths = $this->getUniqueMergedPaths($additionalPath);

        foreach ($paths as $path) {
            $file = $path . DIRECTORY_SEPARATOR . $name;

            if ($this->filesystem->exists($file)) {
                return \realpath($file);
            }
        }

        throw LocatorException::filesOrDirectoriesDoNotExist($name, $this->paths);
    }

    public function locateAnyOf(array $fileNames): string
    {
        if (!$fileNames) {
            throw LocatorException::filesNotFound();
        }

        try {
            return $this->locate($fileNames[0]);
        } catch (\Exception $e) {
            \array_shift($fileNames);

            return $this->locateAnyOf($fileNames);
        }
    }

    private function getUniqueMergedPaths(string $additionalPath = null): array
    {
        if ($additionalPath === null) {
            return $this->paths;
        }

        $paths = $this->paths;

        if ($additionalPath !== null) {
            \array_unshift($paths, $additionalPath);
        }

        return \array_unique($paths);
    }
}
