<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\FileSystem;

use _HumbugBox9658796bb9f0\Symfony\Component\Filesystem\Filesystem;
use Traversable;
final class DummyFileSystem extends Filesystem
{
    public function copy(string $originFile, string $targetFile, bool $overwriteNewerFiles = \false) : void
    {
    }
    public function mkdir($dirs, int $mode = 0777) : void
    {
    }
    public function exists($files) : void
    {
    }
    public function touch($files, ?int $time = null, ?int $atime = null) : void
    {
    }
    public function remove($files) : void
    {
    }
    public function chmod($files, int $mode, int $umask = 00, bool $recursive = \false) : void
    {
    }
    public function chown($files, $user, bool $recursive = \false) : void
    {
    }
    public function chgrp($files, $group, bool $recursive = \false) : void
    {
    }
    public function rename(string $origin, string $target, bool $overwrite = \false) : void
    {
    }
    public function symlink(string $originDir, string $targetDir, bool $copyOnWindows = \false) : void
    {
    }
    public function hardlink(string $originFile, $targetFiles) : void
    {
    }
    public function readlink(string $path, bool $canonicalize = \false) : void
    {
    }
    public function makePathRelative(string $endPath, string $startPath) : void
    {
    }
    public function mirror(string $originDir, string $targetDir, ?Traversable $iterator = null, array $options = []) : void
    {
    }
    public function isAbsolutePath(string $file) : void
    {
    }
    public function tempnam(string $dir, string $prefix) : void
    {
    }
    public function dumpFile(string $filename, $content) : void
    {
    }
    public function appendToFile(string $filename, $content) : void
    {
    }
    public static function handleError($type, $msg) : void
    {
    }
}
