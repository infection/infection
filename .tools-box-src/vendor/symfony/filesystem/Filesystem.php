<?php

namespace _HumbugBoxb47773b41c19\Symfony\Component\Filesystem;

use _HumbugBoxb47773b41c19\Symfony\Component\Filesystem\Exception\FileNotFoundException;
use _HumbugBoxb47773b41c19\Symfony\Component\Filesystem\Exception\InvalidArgumentException;
use _HumbugBoxb47773b41c19\Symfony\Component\Filesystem\Exception\IOException;
class Filesystem
{
    private static $lastError;
    public function copy(string $originFile, string $targetFile, bool $overwriteNewerFiles = \false)
    {
        $originIsLocal = \stream_is_local($originFile) || 0 === \stripos($originFile, 'file://');
        if ($originIsLocal && !\is_file($originFile)) {
            throw new FileNotFoundException(\sprintf('Failed to copy "%s" because file does not exist.', $originFile), 0, null, $originFile);
        }
        $this->mkdir(\dirname($targetFile));
        $doCopy = \true;
        if (!$overwriteNewerFiles && null === \parse_url($originFile, \PHP_URL_HOST) && \is_file($targetFile)) {
            $doCopy = \filemtime($originFile) > \filemtime($targetFile);
        }
        if ($doCopy) {
            if (!($source = self::box('fopen', $originFile, 'r'))) {
                throw new IOException(\sprintf('Failed to copy "%s" to "%s" because source file could not be opened for reading: ', $originFile, $targetFile) . self::$lastError, 0, null, $originFile);
            }
            if (!($target = self::box('fopen', $targetFile, 'w', \false, \stream_context_create(['ftp' => ['overwrite' => \true]])))) {
                throw new IOException(\sprintf('Failed to copy "%s" to "%s" because target file could not be opened for writing: ', $originFile, $targetFile) . self::$lastError, 0, null, $originFile);
            }
            $bytesCopied = \stream_copy_to_stream($source, $target);
            \fclose($source);
            \fclose($target);
            unset($source, $target);
            if (!\is_file($targetFile)) {
                throw new IOException(\sprintf('Failed to copy "%s" to "%s".', $originFile, $targetFile), 0, null, $originFile);
            }
            if ($originIsLocal) {
                self::box('chmod', $targetFile, \fileperms($targetFile) | \fileperms($originFile) & 0111);
                if ($bytesCopied !== ($bytesOrigin = \filesize($originFile))) {
                    throw new IOException(\sprintf('Failed to copy the whole content of "%s" to "%s" (%g of %g bytes copied).', $originFile, $targetFile, $bytesCopied, $bytesOrigin), 0, null, $originFile);
                }
            }
        }
    }
    public function mkdir(string|iterable $dirs, int $mode = 0777)
    {
        foreach ($this->toIterable($dirs) as $dir) {
            if (\is_dir($dir)) {
                continue;
            }
            if (!self::box('mkdir', $dir, $mode, \true) && !\is_dir($dir)) {
                throw new IOException(\sprintf('Failed to create "%s": ', $dir) . self::$lastError, 0, null, $dir);
            }
        }
    }
    public function exists(string|iterable $files) : bool
    {
        $maxPathLength = \PHP_MAXPATHLEN - 2;
        foreach ($this->toIterable($files) as $file) {
            if (\strlen($file) > $maxPathLength) {
                throw new IOException(\sprintf('Could not check if file exist because path length exceeds %d characters.', $maxPathLength), 0, null, $file);
            }
            if (!\file_exists($file)) {
                return \false;
            }
        }
        return \true;
    }
    public function touch(string|iterable $files, int $time = null, int $atime = null)
    {
        foreach ($this->toIterable($files) as $file) {
            if (!($time ? self::box('touch', $file, $time, $atime) : self::box('touch', $file))) {
                throw new IOException(\sprintf('Failed to touch "%s": ', $file) . self::$lastError, 0, null, $file);
            }
        }
    }
    public function remove(string|iterable $files)
    {
        if ($files instanceof \Traversable) {
            $files = \iterator_to_array($files, \false);
        } elseif (!\is_array($files)) {
            $files = [$files];
        }
        self::doRemove($files, \false);
    }
    private static function doRemove(array $files, bool $isRecursive) : void
    {
        $files = \array_reverse($files);
        foreach ($files as $file) {
            if (\is_link($file)) {
                if (!(self::box('unlink', $file) || '\\' !== \DIRECTORY_SEPARATOR || self::box('rmdir', $file)) && \file_exists($file)) {
                    throw new IOException(\sprintf('Failed to remove symlink "%s": ', $file) . self::$lastError);
                }
            } elseif (\is_dir($file)) {
                if (!$isRecursive) {
                    $tmpName = \dirname(\realpath($file)) . '/.' . \strrev(\strtr(\base64_encode(\random_bytes(2)), '/=', '-.'));
                    if (\file_exists($tmpName)) {
                        try {
                            self::doRemove([$tmpName], \true);
                        } catch (IOException) {
                        }
                    }
                    if (!\file_exists($tmpName) && self::box('rename', $file, $tmpName)) {
                        $origFile = $file;
                        $file = $tmpName;
                    } else {
                        $origFile = null;
                    }
                }
                $filesystemIterator = new \FilesystemIterator($file, \FilesystemIterator::CURRENT_AS_PATHNAME | \FilesystemIterator::SKIP_DOTS);
                self::doRemove(\iterator_to_array($filesystemIterator, \true), \true);
                if (!self::box('rmdir', $file) && \file_exists($file) && !$isRecursive) {
                    $lastError = self::$lastError;
                    if (null !== $origFile && self::box('rename', $file, $origFile)) {
                        $file = $origFile;
                    }
                    throw new IOException(\sprintf('Failed to remove directory "%s": ', $file) . $lastError);
                }
            } elseif (!self::box('unlink', $file) && (\str_contains(self::$lastError, 'Permission denied') || \file_exists($file))) {
                throw new IOException(\sprintf('Failed to remove file "%s": ', $file) . self::$lastError);
            }
        }
    }
    public function chmod(string|iterable $files, int $mode, int $umask = 00, bool $recursive = \false)
    {
        foreach ($this->toIterable($files) as $file) {
            if (\is_int($mode) && !self::box('chmod', $file, $mode & ~$umask)) {
                throw new IOException(\sprintf('Failed to chmod file "%s": ', $file) . self::$lastError, 0, null, $file);
            }
            if ($recursive && \is_dir($file) && !\is_link($file)) {
                $this->chmod(new \FilesystemIterator($file), $mode, $umask, \true);
            }
        }
    }
    public function chown(string|iterable $files, string|int $user, bool $recursive = \false)
    {
        foreach ($this->toIterable($files) as $file) {
            if ($recursive && \is_dir($file) && !\is_link($file)) {
                $this->chown(new \FilesystemIterator($file), $user, \true);
            }
            if (\is_link($file) && \function_exists('lchown')) {
                if (!self::box('lchown', $file, $user)) {
                    throw new IOException(\sprintf('Failed to chown file "%s": ', $file) . self::$lastError, 0, null, $file);
                }
            } else {
                if (!self::box('chown', $file, $user)) {
                    throw new IOException(\sprintf('Failed to chown file "%s": ', $file) . self::$lastError, 0, null, $file);
                }
            }
        }
    }
    public function chgrp(string|iterable $files, string|int $group, bool $recursive = \false)
    {
        foreach ($this->toIterable($files) as $file) {
            if ($recursive && \is_dir($file) && !\is_link($file)) {
                $this->chgrp(new \FilesystemIterator($file), $group, \true);
            }
            if (\is_link($file) && \function_exists('lchgrp')) {
                if (!self::box('lchgrp', $file, $group)) {
                    throw new IOException(\sprintf('Failed to chgrp file "%s": ', $file) . self::$lastError, 0, null, $file);
                }
            } else {
                if (!self::box('chgrp', $file, $group)) {
                    throw new IOException(\sprintf('Failed to chgrp file "%s": ', $file) . self::$lastError, 0, null, $file);
                }
            }
        }
    }
    public function rename(string $origin, string $target, bool $overwrite = \false)
    {
        if (!$overwrite && $this->isReadable($target)) {
            throw new IOException(\sprintf('Cannot rename because the target "%s" already exists.', $target), 0, null, $target);
        }
        if (!self::box('rename', $origin, $target)) {
            if (\is_dir($origin)) {
                $this->mirror($origin, $target, null, ['override' => $overwrite, 'delete' => $overwrite]);
                $this->remove($origin);
                return;
            }
            throw new IOException(\sprintf('Cannot rename "%s" to "%s": ', $origin, $target) . self::$lastError, 0, null, $target);
        }
    }
    private function isReadable(string $filename) : bool
    {
        $maxPathLength = \PHP_MAXPATHLEN - 2;
        if (\strlen($filename) > $maxPathLength) {
            throw new IOException(\sprintf('Could not check if file is readable because path length exceeds %d characters.', $maxPathLength), 0, null, $filename);
        }
        return \is_readable($filename);
    }
    public function symlink(string $originDir, string $targetDir, bool $copyOnWindows = \false)
    {
        self::assertFunctionExists('symlink');
        if ('\\' === \DIRECTORY_SEPARATOR) {
            $originDir = \strtr($originDir, '/', '\\');
            $targetDir = \strtr($targetDir, '/', '\\');
            if ($copyOnWindows) {
                $this->mirror($originDir, $targetDir);
                return;
            }
        }
        $this->mkdir(\dirname($targetDir));
        if (\is_link($targetDir)) {
            if (\readlink($targetDir) === $originDir) {
                return;
            }
            $this->remove($targetDir);
        }
        if (!self::box('symlink', $originDir, $targetDir)) {
            $this->linkException($originDir, $targetDir, 'symbolic');
        }
    }
    public function hardlink(string $originFile, string|iterable $targetFiles)
    {
        self::assertFunctionExists('link');
        if (!$this->exists($originFile)) {
            throw new FileNotFoundException(null, 0, null, $originFile);
        }
        if (!\is_file($originFile)) {
            throw new FileNotFoundException(\sprintf('Origin file "%s" is not a file.', $originFile));
        }
        foreach ($this->toIterable($targetFiles) as $targetFile) {
            if (\is_file($targetFile)) {
                if (\fileinode($originFile) === \fileinode($targetFile)) {
                    continue;
                }
                $this->remove($targetFile);
            }
            if (!self::box('link', $originFile, $targetFile)) {
                $this->linkException($originFile, $targetFile, 'hard');
            }
        }
    }
    private function linkException(string $origin, string $target, string $linkType)
    {
        if (self::$lastError) {
            if ('\\' === \DIRECTORY_SEPARATOR && \str_contains(self::$lastError, 'error code(1314)')) {
                throw new IOException(\sprintf('Unable to create "%s" link due to error code 1314: \'A required privilege is not held by the client\'. Do you have the required Administrator-rights?', $linkType), 0, null, $target);
            }
        }
        throw new IOException(\sprintf('Failed to create "%s" link from "%s" to "%s": ', $linkType, $origin, $target) . self::$lastError, 0, null, $target);
    }
    public function readlink(string $path, bool $canonicalize = \false) : ?string
    {
        if (!$canonicalize && !\is_link($path)) {
            return null;
        }
        if ($canonicalize) {
            if (!$this->exists($path)) {
                return null;
            }
            return \realpath($path);
        }
        return \readlink($path);
    }
    public function makePathRelative(string $endPath, string $startPath) : string
    {
        if (!$this->isAbsolutePath($startPath)) {
            throw new InvalidArgumentException(\sprintf('The start path "%s" is not absolute.', $startPath));
        }
        if (!$this->isAbsolutePath($endPath)) {
            throw new InvalidArgumentException(\sprintf('The end path "%s" is not absolute.', $endPath));
        }
        if ('\\' === \DIRECTORY_SEPARATOR) {
            $endPath = \str_replace('\\', '/', $endPath);
            $startPath = \str_replace('\\', '/', $startPath);
        }
        $splitDriveLetter = function ($path) {
            return \strlen($path) > 2 && ':' === $path[1] && '/' === $path[2] && \ctype_alpha($path[0]) ? [\substr($path, 2), \strtoupper($path[0])] : [$path, null];
        };
        $splitPath = function ($path) {
            $result = [];
            foreach (\explode('/', \trim($path, '/')) as $segment) {
                if ('..' === $segment) {
                    \array_pop($result);
                } elseif ('.' !== $segment && '' !== $segment) {
                    $result[] = $segment;
                }
            }
            return $result;
        };
        [$endPath, $endDriveLetter] = $splitDriveLetter($endPath);
        [$startPath, $startDriveLetter] = $splitDriveLetter($startPath);
        $startPathArr = $splitPath($startPath);
        $endPathArr = $splitPath($endPath);
        if ($endDriveLetter && $startDriveLetter && $endDriveLetter != $startDriveLetter) {
            return $endDriveLetter . ':/' . ($endPathArr ? \implode('/', $endPathArr) . '/' : '');
        }
        $index = 0;
        while (isset($startPathArr[$index]) && isset($endPathArr[$index]) && $startPathArr[$index] === $endPathArr[$index]) {
            ++$index;
        }
        if (1 === \count($startPathArr) && '' === $startPathArr[0]) {
            $depth = 0;
        } else {
            $depth = \count($startPathArr) - $index;
        }
        $traverser = \str_repeat('../', $depth);
        $endPathRemainder = \implode('/', \array_slice($endPathArr, $index));
        $relativePath = $traverser . ('' !== $endPathRemainder ? $endPathRemainder . '/' : '');
        return '' === $relativePath ? './' : $relativePath;
    }
    public function mirror(string $originDir, string $targetDir, \Traversable $iterator = null, array $options = [])
    {
        $targetDir = \rtrim($targetDir, '/\\');
        $originDir = \rtrim($originDir, '/\\');
        $originDirLen = \strlen($originDir);
        if (!$this->exists($originDir)) {
            throw new IOException(\sprintf('The origin directory specified "%s" was not found.', $originDir), 0, null, $originDir);
        }
        if ($this->exists($targetDir) && isset($options['delete']) && $options['delete']) {
            $deleteIterator = $iterator;
            if (null === $deleteIterator) {
                $flags = \FilesystemIterator::SKIP_DOTS;
                $deleteIterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($targetDir, $flags), \RecursiveIteratorIterator::CHILD_FIRST);
            }
            $targetDirLen = \strlen($targetDir);
            foreach ($deleteIterator as $file) {
                $origin = $originDir . \substr($file->getPathname(), $targetDirLen);
                if (!$this->exists($origin)) {
                    $this->remove($file);
                }
            }
        }
        $copyOnWindows = $options['copy_on_windows'] ?? \false;
        if (null === $iterator) {
            $flags = $copyOnWindows ? \FilesystemIterator::SKIP_DOTS | \FilesystemIterator::FOLLOW_SYMLINKS : \FilesystemIterator::SKIP_DOTS;
            $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($originDir, $flags), \RecursiveIteratorIterator::SELF_FIRST);
        }
        $this->mkdir($targetDir);
        $filesCreatedWhileMirroring = [];
        foreach ($iterator as $file) {
            if ($file->getPathname() === $targetDir || $file->getRealPath() === $targetDir || isset($filesCreatedWhileMirroring[$file->getRealPath()])) {
                continue;
            }
            $target = $targetDir . \substr($file->getPathname(), $originDirLen);
            $filesCreatedWhileMirroring[$target] = \true;
            if (!$copyOnWindows && \is_link($file)) {
                $this->symlink($file->getLinkTarget(), $target);
            } elseif (\is_dir($file)) {
                $this->mkdir($target);
            } elseif (\is_file($file)) {
                $this->copy($file, $target, $options['override'] ?? \false);
            } else {
                throw new IOException(\sprintf('Unable to guess "%s" file type.', $file), 0, null, $file);
            }
        }
    }
    public function isAbsolutePath(string $file) : bool
    {
        return '' !== $file && (\strspn($file, '/\\', 0, 1) || \strlen($file) > 3 && \ctype_alpha($file[0]) && ':' === $file[1] && \strspn($file, '/\\', 2, 1) || null !== \parse_url($file, \PHP_URL_SCHEME));
    }
    public function tempnam(string $dir, string $prefix, string $suffix = '') : string
    {
        [$scheme, $hierarchy] = $this->getSchemeAndHierarchy($dir);
        if ((null === $scheme || 'file' === $scheme || 'gs' === $scheme) && '' === $suffix) {
            if ($tmpFile = self::box('tempnam', $hierarchy, $prefix)) {
                if (null !== $scheme && 'gs' !== $scheme) {
                    return $scheme . '://' . $tmpFile;
                }
                return $tmpFile;
            }
            throw new IOException('A temporary file could not be created: ' . self::$lastError);
        }
        for ($i = 0; $i < 10; ++$i) {
            $tmpFile = $dir . '/' . $prefix . \uniqid(\mt_rand(), \true) . $suffix;
            if (!($handle = self::box('fopen', $tmpFile, 'x+'))) {
                continue;
            }
            self::box('fclose', $handle);
            return $tmpFile;
        }
        throw new IOException('A temporary file could not be created: ' . self::$lastError);
    }
    public function dumpFile(string $filename, $content)
    {
        if (\is_array($content)) {
            throw new \TypeError(\sprintf('Argument 2 passed to "%s()" must be string or resource, array given.', __METHOD__));
        }
        $dir = \dirname($filename);
        if (!\is_dir($dir)) {
            $this->mkdir($dir);
        }
        $tmpFile = $this->tempnam($dir, \basename($filename));
        try {
            if (\false === self::box('file_put_contents', $tmpFile, $content)) {
                throw new IOException(\sprintf('Failed to write file "%s": ', $filename) . self::$lastError, 0, null, $filename);
            }
            self::box('chmod', $tmpFile, \file_exists($filename) ? \fileperms($filename) : 0666 & ~\umask());
            $this->rename($tmpFile, $filename, \true);
        } finally {
            if (\file_exists($tmpFile)) {
                self::box('unlink', $tmpFile);
            }
        }
    }
    public function appendToFile(string $filename, $content)
    {
        if (\is_array($content)) {
            throw new \TypeError(\sprintf('Argument 2 passed to "%s()" must be string or resource, array given.', __METHOD__));
        }
        $dir = \dirname($filename);
        if (!\is_dir($dir)) {
            $this->mkdir($dir);
        }
        $lock = \func_num_args() > 2 && \func_get_arg(2);
        if (\false === self::box('file_put_contents', $filename, $content, \FILE_APPEND | ($lock ? \LOCK_EX : 0))) {
            throw new IOException(\sprintf('Failed to write file "%s": ', $filename) . self::$lastError, 0, null, $filename);
        }
    }
    private function toIterable(string|iterable $files) : iterable
    {
        return \is_iterable($files) ? $files : [$files];
    }
    private function getSchemeAndHierarchy(string $filename) : array
    {
        $components = \explode('://', $filename, 2);
        return 2 === \count($components) ? [$components[0], $components[1]] : [null, $components[0]];
    }
    private static function assertFunctionExists(string $func) : void
    {
        if (!\function_exists($func)) {
            throw new IOException(\sprintf('Unable to perform filesystem operation because the "%s()" function has been disabled.', $func));
        }
    }
    private static function box(string $func, mixed ...$args) : mixed
    {
        self::assertFunctionExists($func);
        self::$lastError = null;
        \set_error_handler(__CLASS__ . '::handleError');
        try {
            return $func(...$args);
        } finally {
            \restore_error_handler();
        }
    }
    public static function handleError(int $type, string $msg)
    {
        self::$lastError = $msg;
    }
}
