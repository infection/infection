<?php

namespace _HumbugBoxb47773b41c19\Symfony\Component\Filesystem;

use _HumbugBoxb47773b41c19\Symfony\Component\Filesystem\Exception\InvalidArgumentException;
use _HumbugBoxb47773b41c19\Symfony\Component\Filesystem\Exception\RuntimeException;
final class Path
{
    private const CLEANUP_THRESHOLD = 1250;
    private const CLEANUP_SIZE = 1000;
    private static $buffer = [];
    private static $bufferSize = 0;
    public static function canonicalize(string $path) : string
    {
        if ('' === $path) {
            return '';
        }
        if (isset(self::$buffer[$path])) {
            return self::$buffer[$path];
        }
        if ('~' === $path[0]) {
            $path = self::getHomeDirectory() . \substr($path, 1);
        }
        $path = self::normalize($path);
        [$root, $pathWithoutRoot] = self::split($path);
        $canonicalParts = self::findCanonicalParts($root, $pathWithoutRoot);
        self::$buffer[$path] = $canonicalPath = $root . \implode('/', $canonicalParts);
        ++self::$bufferSize;
        if (self::$bufferSize > self::CLEANUP_THRESHOLD) {
            self::$buffer = \array_slice(self::$buffer, -self::CLEANUP_SIZE, null, \true);
            self::$bufferSize = self::CLEANUP_SIZE;
        }
        return $canonicalPath;
    }
    public static function normalize(string $path) : string
    {
        return \str_replace('\\', '/', $path);
    }
    public static function getDirectory(string $path) : string
    {
        if ('' === $path) {
            return '';
        }
        $path = self::canonicalize($path);
        if (\false !== ($schemeSeparatorPosition = \strpos($path, '://'))) {
            $scheme = \substr($path, 0, $schemeSeparatorPosition + 3);
            $path = \substr($path, $schemeSeparatorPosition + 3);
        } else {
            $scheme = '';
        }
        if (\false === ($dirSeparatorPosition = \strrpos($path, '/'))) {
            return '';
        }
        if (0 === $dirSeparatorPosition) {
            return $scheme . '/';
        }
        if (2 === $dirSeparatorPosition && \ctype_alpha($path[0]) && ':' === $path[1]) {
            return $scheme . \substr($path, 0, 3);
        }
        return $scheme . \substr($path, 0, $dirSeparatorPosition);
    }
    public static function getHomeDirectory() : string
    {
        if (\getenv('HOME')) {
            return self::canonicalize(\getenv('HOME'));
        }
        if (\getenv('HOMEDRIVE') && \getenv('HOMEPATH')) {
            return self::canonicalize(\getenv('HOMEDRIVE') . \getenv('HOMEPATH'));
        }
        throw new RuntimeException("Cannot find the home directory path: Your environment or operating system isn't supported.");
    }
    public static function getRoot(string $path) : string
    {
        if ('' === $path) {
            return '';
        }
        if (\false !== ($schemeSeparatorPosition = \strpos($path, '://'))) {
            $scheme = \substr($path, 0, $schemeSeparatorPosition + 3);
            $path = \substr($path, $schemeSeparatorPosition + 3);
        } else {
            $scheme = '';
        }
        $firstCharacter = $path[0];
        if ('/' === $firstCharacter || '\\' === $firstCharacter) {
            return $scheme . '/';
        }
        $length = \strlen($path);
        if ($length > 1 && ':' === $path[1] && \ctype_alpha($firstCharacter)) {
            if (2 === $length) {
                return $scheme . $path . '/';
            }
            if ('/' === $path[2] || '\\' === $path[2]) {
                return $scheme . $firstCharacter . $path[1] . '/';
            }
        }
        return '';
    }
    public static function getFilenameWithoutExtension(string $path, string $extension = null) : string
    {
        if ('' === $path) {
            return '';
        }
        if (null !== $extension) {
            return \rtrim(\basename($path, $extension), '.');
        }
        return \pathinfo($path, \PATHINFO_FILENAME);
    }
    public static function getExtension(string $path, bool $forceLowerCase = \false) : string
    {
        if ('' === $path) {
            return '';
        }
        $extension = \pathinfo($path, \PATHINFO_EXTENSION);
        if ($forceLowerCase) {
            $extension = self::toLower($extension);
        }
        return $extension;
    }
    public static function hasExtension(string $path, $extensions = null, bool $ignoreCase = \false) : bool
    {
        if ('' === $path) {
            return \false;
        }
        $actualExtension = self::getExtension($path, $ignoreCase);
        if ([] === $extensions || null === $extensions) {
            return '' !== $actualExtension;
        }
        if (\is_string($extensions)) {
            $extensions = [$extensions];
        }
        foreach ($extensions as $key => $extension) {
            if ($ignoreCase) {
                $extension = self::toLower($extension);
            }
            $extensions[$key] = \ltrim($extension, '.');
        }
        return \in_array($actualExtension, $extensions, \true);
    }
    public static function changeExtension(string $path, string $extension) : string
    {
        if ('' === $path) {
            return '';
        }
        $actualExtension = self::getExtension($path);
        $extension = \ltrim($extension, '.');
        if ('/' === \substr($path, -1)) {
            return $path;
        }
        if (empty($actualExtension)) {
            return $path . ('.' === \substr($path, -1) ? '' : '.') . $extension;
        }
        return \substr($path, 0, -\strlen($actualExtension)) . $extension;
    }
    public static function isAbsolute(string $path) : bool
    {
        if ('' === $path) {
            return \false;
        }
        if (\false !== ($schemeSeparatorPosition = \strpos($path, '://'))) {
            $path = \substr($path, $schemeSeparatorPosition + 3);
        }
        $firstCharacter = $path[0];
        if ('/' === $firstCharacter || '\\' === $firstCharacter) {
            return \true;
        }
        if (\strlen($path) > 1 && \ctype_alpha($firstCharacter) && ':' === $path[1]) {
            if (2 === \strlen($path)) {
                return \true;
            }
            if ('/' === $path[2] || '\\' === $path[2]) {
                return \true;
            }
        }
        return \false;
    }
    public static function isRelative(string $path) : bool
    {
        return !self::isAbsolute($path);
    }
    public static function makeAbsolute(string $path, string $basePath) : string
    {
        if ('' === $basePath) {
            throw new InvalidArgumentException(\sprintf('The base path must be a non-empty string. Got: "%s".', $basePath));
        }
        if (!self::isAbsolute($basePath)) {
            throw new InvalidArgumentException(\sprintf('The base path "%s" is not an absolute path.', $basePath));
        }
        if (self::isAbsolute($path)) {
            return self::canonicalize($path);
        }
        if (\false !== ($schemeSeparatorPosition = \strpos($basePath, '://'))) {
            $scheme = \substr($basePath, 0, $schemeSeparatorPosition + 3);
            $basePath = \substr($basePath, $schemeSeparatorPosition + 3);
        } else {
            $scheme = '';
        }
        return $scheme . self::canonicalize(\rtrim($basePath, '/\\') . '/' . $path);
    }
    public static function makeRelative(string $path, string $basePath) : string
    {
        $path = self::canonicalize($path);
        $basePath = self::canonicalize($basePath);
        [$root, $relativePath] = self::split($path);
        [$baseRoot, $relativeBasePath] = self::split($basePath);
        if ('' === $root && '' !== $baseRoot) {
            if ('' === $relativeBasePath) {
                $relativePath = \ltrim($relativePath, './\\');
            }
            return $relativePath;
        }
        if ('' !== $root && '' === $baseRoot) {
            throw new InvalidArgumentException(\sprintf('The absolute path "%s" cannot be made relative to the relative path "%s". You should provide an absolute base path instead.', $path, $basePath));
        }
        if ($baseRoot && $root !== $baseRoot) {
            throw new InvalidArgumentException(\sprintf('The path "%s" cannot be made relative to "%s", because they have different roots ("%s" and "%s").', $path, $basePath, $root, $baseRoot));
        }
        if ('' === $relativeBasePath) {
            return $relativePath;
        }
        $parts = \explode('/', $relativePath);
        $baseParts = \explode('/', $relativeBasePath);
        $dotDotPrefix = '';
        $match = \true;
        foreach ($baseParts as $index => $basePart) {
            if ($match && isset($parts[$index]) && $basePart === $parts[$index]) {
                unset($parts[$index]);
                continue;
            }
            $match = \false;
            $dotDotPrefix .= '../';
        }
        return \rtrim($dotDotPrefix . \implode('/', $parts), '/');
    }
    public static function isLocal(string $path) : bool
    {
        return '' !== $path && !\str_contains($path, '://');
    }
    public static function getLongestCommonBasePath(string ...$paths) : ?string
    {
        [$bpRoot, $basePath] = self::split(self::canonicalize(\reset($paths)));
        for (\next($paths); null !== \key($paths) && '' !== $basePath; \next($paths)) {
            [$root, $path] = self::split(self::canonicalize(\current($paths)));
            if ($root !== $bpRoot) {
                return null;
            }
            while (\true) {
                if ('.' === $basePath) {
                    $basePath = '';
                    continue 2;
                }
                if (\str_starts_with($path . '/', $basePath . '/')) {
                    continue 2;
                }
                $basePath = \dirname($basePath);
            }
        }
        return $bpRoot . $basePath;
    }
    public static function join(string ...$paths) : string
    {
        $finalPath = null;
        $wasScheme = \false;
        foreach ($paths as $path) {
            if ('' === $path) {
                continue;
            }
            if (null === $finalPath) {
                $finalPath = $path;
                $wasScheme = \str_contains($path, '://');
                continue;
            }
            if (!\in_array(\substr($finalPath, -1), ['/', '\\'])) {
                $finalPath .= '/';
            }
            $finalPath .= $wasScheme ? $path : \ltrim($path, '/');
            $wasScheme = \false;
        }
        if (null === $finalPath) {
            return '';
        }
        return self::canonicalize($finalPath);
    }
    public static function isBasePath(string $basePath, string $ofPath) : bool
    {
        $basePath = self::canonicalize($basePath);
        $ofPath = self::canonicalize($ofPath);
        return \str_starts_with($ofPath . '/', \rtrim($basePath, '/') . '/');
    }
    private static function findCanonicalParts(string $root, string $pathWithoutRoot) : array
    {
        $parts = \explode('/', $pathWithoutRoot);
        $canonicalParts = [];
        foreach ($parts as $part) {
            if ('.' === $part || '' === $part) {
                continue;
            }
            if ('..' === $part && \count($canonicalParts) > 0 && '..' !== $canonicalParts[\count($canonicalParts) - 1]) {
                \array_pop($canonicalParts);
                continue;
            }
            if ('..' !== $part || '' === $root) {
                $canonicalParts[] = $part;
            }
        }
        return $canonicalParts;
    }
    private static function split(string $path) : array
    {
        if ('' === $path) {
            return ['', ''];
        }
        if (\false !== ($schemeSeparatorPosition = \strpos($path, '://'))) {
            $root = \substr($path, 0, $schemeSeparatorPosition + 3);
            $path = \substr($path, $schemeSeparatorPosition + 3);
        } else {
            $root = '';
        }
        $length = \strlen($path);
        if (\str_starts_with($path, '/')) {
            $root .= '/';
            $path = $length > 1 ? \substr($path, 1) : '';
        } elseif ($length > 1 && \ctype_alpha($path[0]) && ':' === $path[1]) {
            if (2 === $length) {
                $root .= $path . '/';
                $path = '';
            } elseif ('/' === $path[2]) {
                $root .= \substr($path, 0, 3);
                $path = $length > 3 ? \substr($path, 3) : '';
            }
        }
        return [$root, $path];
    }
    private static function toLower(string $string) : string
    {
        if (\false !== ($encoding = \mb_detect_encoding($string, null, \true))) {
            return \mb_strtolower($string, $encoding);
        }
        return \strtolower($string);
    }
    private function __construct()
    {
    }
}
