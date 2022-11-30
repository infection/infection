<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\Humbug\PhpScoper;

use Iterator;
use _HumbugBoxb47773b41c19\PackageVersions\Versions;
use function array_pop;
use function count;
use function str_split;
use function str_starts_with;
use function strrpos;
use function substr;
function get_php_scoper_version() : string
{
    if (str_starts_with(__FILE__, 'phar:')) {
        return '@git_version_placeholder@';
    }
    $rawVersion = Versions::getVersion('humbug/php-scoper');
    [$prettyVersion, $commitHash] = \explode('@', $rawVersion);
    return $prettyVersion . '@' . substr($commitHash, 0, 7);
}
function get_common_path(array $paths) : string
{
    $nbPaths = count($paths);
    if (0 === $nbPaths) {
        return '';
    }
    $pathRef = (string) array_pop($paths);
    if (1 === $nbPaths) {
        $commonPath = $pathRef;
    } else {
        $commonPath = '';
        foreach (str_split($pathRef) as $pos => $char) {
            foreach ($paths as $path) {
                if (!isset($path[$pos]) || $path[$pos] !== $char) {
                    break 2;
                }
            }
            $commonPath .= $char;
        }
    }
    foreach (['/', '\\'] as $separator) {
        $lastSeparatorPos = strrpos($commonPath, $separator);
        if (\false !== $lastSeparatorPos) {
            $commonPath = \rtrim(substr($commonPath, 0, $lastSeparatorPos), $separator);
            break;
        }
    }
    return $commonPath;
}
function chain(iterable ...$iterables) : Iterator
{
    foreach ($iterables as $iterable) {
        yield from $iterable;
    }
}
