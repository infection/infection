<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\PackageVersions;

use _HumbugBoxb47773b41c19\Composer\InstalledVersions;
use OutOfBoundsException;
use UnexpectedValueException;
\class_exists(InstalledVersions::class);
final class Versions
{
    const ROOT_PACKAGE_NAME = 'unknown/root-package@UNKNOWN';
    const VERSIONS = [];
    private function __construct()
    {
    }
    /**
    @psalm-pure
    @psalm-suppress
    */
    public static function rootPackageName() : string
    {
        if (!\class_exists(InstalledVersions::class, \false) || !InstalledVersions::getRawData()) {
            return self::ROOT_PACKAGE_NAME;
        }
        return InstalledVersions::getRootPackage()['name'];
    }
    public static function getVersion(string $packageName) : string
    {
        if (!self::composer2ApiUsable()) {
            return FallbackVersions::getVersion($packageName);
        }
        /**
        @psalm-suppress */
        if ($packageName === self::ROOT_PACKAGE_NAME) {
            $rootPackage = InstalledVersions::getRootPackage();
            return $rootPackage['pretty_version'] . '@' . $rootPackage['reference'];
        }
        return InstalledVersions::getPrettyVersion($packageName) . '@' . InstalledVersions::getReference($packageName);
    }
    private static function composer2ApiUsable() : bool
    {
        if (!\class_exists(InstalledVersions::class, \false)) {
            return \false;
        }
        if (\method_exists(InstalledVersions::class, 'getAllRawData')) {
            $rawData = InstalledVersions::getAllRawData();
            if (\count($rawData) === 1 && \count($rawData[0]) === 0) {
                return \false;
            }
        } else {
            $rawData = InstalledVersions::getRawData();
            if ($rawData === null || $rawData === []) {
                return \false;
            }
        }
        return \true;
    }
}
