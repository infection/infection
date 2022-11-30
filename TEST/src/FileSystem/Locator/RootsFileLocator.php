<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\FileSystem\Locator;

use function array_shift;
use function current;
use const DIRECTORY_SEPARATOR;
use function is_file;
use function _HumbugBox9658796bb9f0\Safe\realpath;
use _HumbugBox9658796bb9f0\Symfony\Component\Filesystem\Filesystem;
use _HumbugBox9658796bb9f0\Symfony\Component\Filesystem\Path;
use _HumbugBox9658796bb9f0\Webmozart\Assert\Assert;
final class RootsFileLocator implements Locator
{
    private array $roots;
    public function __construct(array $roots, private Filesystem $filesystem)
    {
        Assert::allString($roots);
        $this->roots = $roots;
    }
    public function locate(string $fileName) : string
    {
        $canonicalFileName = Path::canonicalize($fileName);
        if ($this->filesystem->isAbsolutePath($canonicalFileName)) {
            if ($this->filesystem->exists($canonicalFileName) && is_file($canonicalFileName)) {
                return realpath($canonicalFileName);
            }
            throw FileNotFound::fromFileName($canonicalFileName, $this->roots);
        }
        foreach ($this->roots as $path) {
            $file = $path . DIRECTORY_SEPARATOR . $canonicalFileName;
            if ($this->filesystem->exists($file) && is_file($file)) {
                return realpath($file);
            }
        }
        throw FileNotFound::fromFileName($canonicalFileName, $this->roots);
    }
    public function locateOneOf(array $fileNames) : string
    {
        $file = $this->innerLocateOneOf($fileNames);
        if ($file === null) {
            throw FileNotFound::fromFiles($fileNames, $this->roots);
        }
        return $file;
    }
    private function innerLocateOneOf(array $fileNames) : ?string
    {
        if ($fileNames === []) {
            return null;
        }
        try {
            return $this->locate(current($fileNames));
        } catch (FileNotFound) {
            array_shift($fileNames);
            return $this->innerLocateOneOf($fileNames);
        }
    }
}
