<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\KevinGH\Box\Composer;

use function array_column;
use function array_filter;
use function array_key_exists;
use function array_map;
use const DIRECTORY_SEPARATOR;
use function _HumbugBoxb47773b41c19\KevinGH\Box\FileSystem\make_path_absolute;
use function realpath;
final class ComposerConfiguration
{
    public static function retrieveDevPackages(string $basePath, ?array $composerJsonDecodedContents, ?array $composerLockDecodedContents, bool $excludeDevPackages) : array
    {
        if (null === $composerJsonDecodedContents || null === $composerLockDecodedContents || \false === $excludeDevPackages) {
            return [];
        }
        return self::getDevPackagePaths($basePath, $composerJsonDecodedContents, $composerLockDecodedContents);
    }
    private static function getDevPackagePaths(string $basePath, array $composerJsonDecodedContents, array $composerLockDecodedContents) : array
    {
        $vendorDir = make_path_absolute(self::retrieveVendorDir($composerJsonDecodedContents), $basePath);
        $packageNames = self::retrieveDevPackageNames($composerLockDecodedContents);
        return array_filter(array_map(static function (string $packageName) use($vendorDir) : ?string {
            $realPath = realpath($vendorDir . DIRECTORY_SEPARATOR . $packageName);
            return \false !== $realPath ? $realPath : null;
        }, $packageNames));
    }
    public static function retrieveVendorDir(array $composerJsonDecodedContents) : string
    {
        if (\false === array_key_exists('config', $composerJsonDecodedContents)) {
            return 'vendor';
        }
        if (\false === array_key_exists('vendor-dir', $composerJsonDecodedContents['config'])) {
            return 'vendor';
        }
        return $composerJsonDecodedContents['config']['vendor-dir'];
    }
    private static function retrieveDevPackageNames(array $composerLockDecodedContents) : array
    {
        if (\false === array_key_exists('packages-dev', $composerLockDecodedContents)) {
            return [];
        }
        return array_column($composerLockDecodedContents['packages-dev'], 'name');
    }
}
