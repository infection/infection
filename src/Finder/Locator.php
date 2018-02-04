<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */
declare(strict_types=1);

namespace Infection\Finder;

class Locator
{
    /**
     * @var array
     */
    private $paths;

    public function __construct(array $paths)
    {
        $this->paths = $paths;
    }

    public function locate(string $name, string $additionalPath = null)
    {
        if ($this->isAbsolutePath($name)) {
            if (!file_exists($name)) {
                throw new \Exception(sprintf('The file/folder "%s" does not exist.', $name));
            }

            return realpath($name);
        }

        $paths = $this->getUniqueMergedPaths($additionalPath);

        foreach ($paths as $path) {
            if (@file_exists($file = $path . DIRECTORY_SEPARATOR . $name)) {
                return realpath($file);
            }
        }

        throw new \Exception(
            sprintf('The file/folder "%s" does not exist (in: %s).', $name, implode(', ', $this->paths))
        );
    }

    public function locateAnyOf(array $fileNames): string
    {
        if (!$fileNames) {
            throw new \Exception('Files are not found');
        }

        try {
            return $this->locate($fileNames[0]);
        } catch (\Exception $e) {
            array_shift($fileNames);

            return $this->locateAnyOf($fileNames);
        }
    }

    public function locateDirectories(string $wildcard, string $additionalPath = null)
    {
        $allDirectoryNames = [];

        $paths = $this->getUniqueMergedPaths($additionalPath);

        foreach ($paths as $path) {
            $directoryNames = glob($path . '/' . $wildcard, GLOB_ONLYDIR);

            if ($directoryNames) {
                return array_map('realpath', $directoryNames);
            }
        }

        return $allDirectoryNames;
    }

    private function getUniqueMergedPaths(string $additionalPath = null): array
    {
        if ($additionalPath === null) {
            return $this->paths;
        }

        $paths = $this->paths;

        if ($additionalPath !== null) {
            array_unshift($paths, $additionalPath);
        }

        return array_unique($paths);
    }

    private function isAbsolutePath(string $file): bool
    {
        return $file[0] === '/' || $file[0] === '\\'
            || (
                strlen($file) > 3 && ctype_alpha($file[0])
                && $file[1] === ':'
                && ($file[2] === '\\' || $file[2] === '/')
            )
            || null !== parse_url($file, PHP_URL_SCHEME);
    }
}
