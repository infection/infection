<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\KevinGH\Box\FileSystem;

use _HumbugBoxb47773b41c19\Symfony\Component\Filesystem\Exception\FileNotFoundException;
use _HumbugBoxb47773b41c19\Symfony\Component\Filesystem\Exception\IOException;
use _HumbugBoxb47773b41c19\Symfony\Component\Filesystem\Path;
use Traversable;
function canonicalize(string $path) : string
{
    static $fileSystem;
    if (null === $fileSystem) {
        $fileSystem = new FileSystem();
    }
    return $fileSystem->canonicalize($path);
}
function normalize(string $path) : string
{
    static $fileSystem;
    if (null === $fileSystem) {
        $fileSystem = new FileSystem();
    }
    return $fileSystem->normalize($path);
}
function directory(string $path) : string
{
    static $fileSystem;
    if (null === $fileSystem) {
        $fileSystem = new FileSystem();
    }
    return $fileSystem->getDirectory($path);
}
function home_directory() : string
{
    static $fileSystem;
    if (null === $fileSystem) {
        $fileSystem = new FileSystem();
    }
    return $fileSystem->getHomeDirectory();
}
function root(string $path) : string
{
    static $fileSystem;
    if (null === $fileSystem) {
        $fileSystem = new FileSystem();
    }
    return $fileSystem->getRoot($path);
}
function filename(string $path) : string
{
    static $fileSystem;
    if (null === $fileSystem) {
        $fileSystem = new FileSystem();
    }
    return $fileSystem->getFilename($path);
}
function filename_without_extension($path, $extension = null) : string
{
    static $fileSystem;
    if (null === $fileSystem) {
        $fileSystem = new FileSystem();
    }
    return $fileSystem->getFilenameWithoutExtension($path, $extension);
}
function extension(string $path, bool $forceLowerCase = \false) : string
{
    static $fileSystem;
    if (null === $fileSystem) {
        $fileSystem = new FileSystem();
    }
    return $fileSystem->getExtension($path, $forceLowerCase);
}
function has_extension(string $path, $extensions = null, bool $ignoreCase = \false) : bool
{
    static $fileSystem;
    if (null === $fileSystem) {
        $fileSystem = new FileSystem();
    }
    return $fileSystem->hasExtension($path, $extensions, $ignoreCase);
}
function change_extension(string $path, string $extension) : string
{
    static $fileSystem;
    if (null === $fileSystem) {
        $fileSystem = new FileSystem();
    }
    return $fileSystem->changeExtension($path, $extension);
}
function is_absolute_path(string $path) : bool
{
    return Path::isAbsolute($path);
}
function is_relative_path(string $path) : bool
{
    static $fileSystem;
    if (null === $fileSystem) {
        $fileSystem = new FileSystem();
    }
    return $fileSystem->isRelativePath($path);
}
function make_path_absolute(string $path, string $basePath) : string
{
    static $fileSystem;
    if (null === $fileSystem) {
        $fileSystem = new FileSystem();
    }
    return $fileSystem->makeAbsolute($path, $basePath);
}
function make_path_relative(string $path, string $basePath) : string
{
    static $fileSystem;
    if (null === $fileSystem) {
        $fileSystem = new FileSystem();
    }
    return $fileSystem->makeRelative($path, $basePath);
}
function is_local(string $path) : bool
{
    static $fileSystem;
    if (null === $fileSystem) {
        $fileSystem = new FileSystem();
    }
    return $fileSystem->isLocal($path);
}
function longest_common_base_path(array $paths) : ?string
{
    static $fileSystem;
    if (null === $fileSystem) {
        $fileSystem = new FileSystem();
    }
    return $fileSystem->getLongestCommonBasePath($paths);
}
function join(array|string $paths) : string
{
    static $fileSystem;
    if (null === $fileSystem) {
        $fileSystem = new FileSystem();
    }
    return $fileSystem->join($paths);
}
function is_base_path(string $basePath, string $ofPath) : bool
{
    static $fileSystem;
    if (null === $fileSystem) {
        $fileSystem = new FileSystem();
    }
    return $fileSystem->isBasePath($basePath, $ofPath);
}
function escape_path(string $path) : string
{
    static $fileSystem;
    if (null === $fileSystem) {
        $fileSystem = new FileSystem();
    }
    return $fileSystem->escapePath($path);
}
function file_contents(string $file) : string
{
    static $fileSystem;
    if (null === $fileSystem) {
        $fileSystem = new FileSystem();
    }
    return $fileSystem->getFileContents($file);
}
function copy(string $originFile, string $targetFile, bool $overwriteNewerFiles = \false) : void
{
    static $fileSystem;
    if (null === $fileSystem) {
        $fileSystem = new FileSystem();
    }
    $fileSystem->copy($originFile, $targetFile, $overwriteNewerFiles);
}
function mkdir(iterable|string $dirs, int $mode = 0777) : void
{
    static $fileSystem;
    if (null === $fileSystem) {
        $fileSystem = new FileSystem();
    }
    $fileSystem->mkdir($dirs, $mode);
}
function remove(iterable|string $files) : void
{
    static $fileSystem;
    if (null === $fileSystem) {
        $fileSystem = new FileSystem();
    }
    $fileSystem->remove($files);
}
function exists(iterable|string $files) : bool
{
    static $fileSystem;
    if (null === $fileSystem) {
        $fileSystem = new FileSystem();
    }
    return $fileSystem->exists($files);
}
function touch(iterable|string $files, ?int $time = null, ?int $atime = null) : void
{
    static $fileSystem;
    if (null === $fileSystem) {
        $fileSystem = new FileSystem();
    }
    $fileSystem->touch($files, $time, $atime);
}
function chmod(iterable|string $files, int $mode, int $umask = 00, bool $recursive = \false) : void
{
    static $fileSystem;
    if (null === $fileSystem) {
        $fileSystem = new FileSystem();
    }
    $fileSystem->chmod($files, $mode, $umask, $recursive);
}
function chown(iterable|string $files, string $user, bool $recursive = \false) : void
{
    static $fileSystem;
    if (null === $fileSystem) {
        $fileSystem = new FileSystem();
    }
    $fileSystem->chown($files, $user, $recursive);
}
function chgrp(iterable|string $files, string $group, bool $recursive = \false) : void
{
    static $fileSystem;
    if (null === $fileSystem) {
        $fileSystem = new FileSystem();
    }
    $fileSystem->chgrp($files, $group, $recursive);
}
function rename(string $origin, string $target, bool $overwrite = \false) : void
{
    static $fileSystem;
    if (null === $fileSystem) {
        $fileSystem = new FileSystem();
    }
    $fileSystem->rename($origin, $target, $overwrite);
}
function symlink(string $originDir, string $targetDir, bool $copyOnWindows = \false) : void
{
    static $fileSystem;
    if (null === $fileSystem) {
        $fileSystem = new FileSystem();
    }
    $fileSystem->symlink($originDir, $targetDir, $copyOnWindows);
}
function hardlink(string $originFile, array|string $targetFiles) : void
{
    static $fileSystem;
    if (null === $fileSystem) {
        $fileSystem = new FileSystem();
    }
    $fileSystem->hardlink($originFile, $targetFiles);
}
function readlink(string $path, bool $canonicalize = \false) : ?string
{
    static $fileSystem;
    if (null === $fileSystem) {
        $fileSystem = new FileSystem();
    }
    return $fileSystem->readlink($path, $canonicalize);
}
function mirror(string $originDir, string $targetDir, ?Traversable $iterator = null, array $options = []) : void
{
    static $fileSystem;
    if (null === $fileSystem) {
        $fileSystem = new FileSystem();
    }
    $fileSystem->mirror($originDir, $targetDir, $iterator, $options);
}
function make_tmp_dir(string $namespace, string $className) : string
{
    static $fileSystem;
    if (null === $fileSystem) {
        $fileSystem = new FileSystem();
    }
    return $fileSystem->makeTmpDir($namespace, $className);
}
function tempnam($dir, $prefix) : string
{
    static $fileSystem;
    if (null === $fileSystem) {
        $fileSystem = new FileSystem();
    }
    return $fileSystem->tempnam($dir, $prefix);
}
function dump_file(string $filename, ?string $content = null) : void
{
    static $fileSystem;
    if (null === $fileSystem) {
        $fileSystem = new FileSystem();
    }
    $fileSystem->dumpFile($filename, $content);
}
function append_to_file(string $filename, string $content) : void
{
    static $fileSystem;
    if (null === $fileSystem) {
        $fileSystem = new FileSystem();
    }
    $fileSystem->appendToFile($filename, $content);
}
