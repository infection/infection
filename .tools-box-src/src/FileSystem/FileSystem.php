<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\KevinGH\Box\FileSystem;

use function array_reverse;
use function defined;
use const DIRECTORY_SEPARATOR;
use function error_get_last;
use function escapeshellarg;
use function exec;
use function file_exists;
use function file_get_contents;
use FilesystemIterator;
use function is_array;
use function is_dir;
use function is_link;
use function iterator_to_array;
use function random_int;
use function realpath;
use function rmdir;
use function sprintf;
use function str_replace;
use function strrpos;
use function substr;
use _HumbugBoxb47773b41c19\Symfony\Component\Filesystem\Exception\IOException;
use _HumbugBoxb47773b41c19\Symfony\Component\Filesystem\Filesystem as SymfonyFilesystem;
use _HumbugBoxb47773b41c19\Symfony\Component\Filesystem\Path;
use function sys_get_temp_dir;
use Traversable;
use function unlink;
use _HumbugBoxb47773b41c19\Webmozart\Assert\Assert;
final class FileSystem extends SymfonyFilesystem
{
    public function canonicalize(string $path) : string
    {
        return Path::canonicalize($path);
    }
    public function normalize(string $path) : string
    {
        return Path::normalize($path);
    }
    public function getDirectory(string $path) : string
    {
        return Path::getDirectory($path);
    }
    public function getHomeDirectory() : string
    {
        return Path::getHomeDirectory();
    }
    public function getRoot(string $path) : string
    {
        return Path::getRoot($path);
    }
    public function getFilename(string $path) : string
    {
        return Path::getFilename($path);
    }
    public function getFilenameWithoutExtension($path, $extension = null) : string
    {
        return Path::getFilenameWithoutExtension($path, $extension);
    }
    public function getExtension(string $path, bool $forceLowerCase = \false) : string
    {
        return Path::getExtension($path, $forceLowerCase);
    }
    public function hasExtension(string $path, $extensions = null, bool $ignoreCase = \false) : bool
    {
        return Path::hasExtension($path, $extensions, $ignoreCase);
    }
    public function changeExtension(string $path, string $extension) : string
    {
        return Path::changeExtension($path, $extension);
    }
    public function isRelativePath(string $path) : bool
    {
        return !$this->isAbsolutePath($path);
    }
    public function makeAbsolute(string $path, string $basePath) : string
    {
        return Path::makeAbsolute($path, $basePath);
    }
    public function makeRelative(string $path, string $basePath) : string
    {
        return Path::makeRelative($path, $basePath);
    }
    public function isLocal(string $path) : bool
    {
        return Path::isLocal($path);
    }
    public function getLongestCommonBasePath(array $paths) : ?string
    {
        return Path::getLongestCommonBasePath(...$paths);
    }
    public function join(array|string $paths) : string
    {
        return Path::join($paths);
    }
    public function isBasePath(string $basePath, string $ofPath) : bool
    {
        return Path::isBasePath($basePath, $ofPath);
    }
    public function escapePath(string $path) : string
    {
        return str_replace('/', DIRECTORY_SEPARATOR, $path);
    }
    public function getFileContents(string $file) : string
    {
        Assert::file($file);
        Assert::readable($file);
        if (\false === ($contents = @file_get_contents($file))) {
            throw new IOException(sprintf('Failed to read file "%s": %s.', $file, error_get_last()['message']), 0, null, $file);
        }
        return $contents;
    }
    public function makeTmpDir(string $namespace, string $className) : string
    {
        if (\false !== ($pos = strrpos($className, '\\'))) {
            $shortClass = substr($className, $pos + 1);
        } else {
            $shortClass = $className;
        }
        $systemTempDir = str_replace('\\', '/', realpath(sys_get_temp_dir()));
        $basePath = $systemTempDir . '/' . $namespace . '/' . $shortClass;
        $result = \false;
        $attempts = 0;
        do {
            $tmpDir = $this->escapePath($basePath . random_int(10000, 99999));
            try {
                $this->mkdir($tmpDir, 0777);
                $result = \true;
            } catch (IOException) {
                ++$attempts;
            }
        } while (\false === $result && $attempts <= 10);
        return $tmpDir;
    }
    public function remove($files) : void
    {
        if ($files instanceof Traversable) {
            $files = iterator_to_array($files, \false);
        } elseif (!is_array($files)) {
            $files = [$files];
        }
        $files = array_reverse($files);
        foreach ($files as $file) {
            if (defined('PHP_WINDOWS_VERSION_BUILD') && is_dir($file)) {
                exec(sprintf('rd /s /q %s', escapeshellarg($file)));
            } elseif (is_link($file)) {
                if (!@(unlink($file) || '\\' !== DIRECTORY_SEPARATOR || rmdir($file)) && file_exists($file)) {
                    $error = error_get_last();
                    throw new IOException(sprintf('Failed to remove symlink "%s": %s.', $file, $error['message']));
                }
            } elseif (is_dir($file)) {
                $this->remove(new FilesystemIterator($file, FilesystemIterator::CURRENT_AS_PATHNAME | FilesystemIterator::SKIP_DOTS));
                if (!@rmdir($file) && file_exists($file)) {
                    $error = error_get_last();
                    throw new IOException(sprintf('Failed to remove directory "%s": %s.', $file, $error['message']));
                }
            } elseif (!@unlink($file) && file_exists($file)) {
                $error = error_get_last();
                throw new IOException(sprintf('Failed to remove file "%s": %s.', $file, $error['message']));
            } elseif (file_exists($file)) {
                throw new IOException(sprintf('Failed to remove file "%s".', $file));
            }
        }
    }
}
