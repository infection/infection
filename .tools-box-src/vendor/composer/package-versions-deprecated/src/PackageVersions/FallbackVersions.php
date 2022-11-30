<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\PackageVersions;

use Generator;
use OutOfBoundsException;
use UnexpectedValueException;
use function array_key_exists;
use function array_merge;
use function basename;
use function file_exists;
use function file_get_contents;
use function getcwd;
use function iterator_to_array;
use function json_decode;
use function json_encode;
use function sprintf;
final class FallbackVersions
{
    const ROOT_PACKAGE_NAME = 'unknown/root-package@UNKNOWN';
    private function __construct()
    {
    }
    public static function getVersion(string $packageName) : string
    {
        $versions = iterator_to_array(self::getVersions(self::getPackageData()));
        if (!array_key_exists($packageName, $versions)) {
            throw new OutOfBoundsException('Required package "' . $packageName . '" is not installed: check your ./vendor/composer/installed.json and/or ./composer.lock files');
        }
        return $versions[$packageName];
    }
    private static function getPackageData() : array
    {
        $checkedPaths = [getcwd() . '/vendor/composer/installed.json', __DIR__ . '/../../../../composer/installed.json', getcwd() . '/composer.lock', __DIR__ . '/../../../../../composer.lock', __DIR__ . '/../../composer.lock'];
        $packageData = [];
        foreach ($checkedPaths as $path) {
            if (!file_exists($path)) {
                continue;
            }
            $data = json_decode(file_get_contents($path), \true);
            switch (basename($path)) {
                case 'installed.json':
                    if (isset($data['packages'])) {
                        $packageData[] = $data['packages'];
                    } else {
                        $packageData[] = $data;
                    }
                    break;
                case 'composer.lock':
                    $packageData[] = $data['packages'] + ($data['packages-dev'] ?? []);
                    break;
                default:
            }
        }
        if ($packageData !== []) {
            return array_merge(...$packageData);
        }
        throw new UnexpectedValueException(sprintf('PackageVersions could not locate the `vendor/composer/installed.json` or your `composer.lock` ' . 'location. This is assumed to be in %s. If you customized your composer vendor directory and ran composer ' . 'installation with --no-scripts, or if you deployed without the required composer files, PackageVersions ' . 'can\'t detect installed versions.', json_encode($checkedPaths)));
    }
    /**
    @psalm-return
    */
    private static function getVersions(array $packageData) : Generator
    {
        foreach ($packageData as $package) {
            (yield $package['name'] => $package['version'] . '@' . ($package['source']['reference'] ?? $package['dist']['reference'] ?? ''));
        }
        (yield self::ROOT_PACKAGE_NAME => self::ROOT_PACKAGE_NAME);
    }
}
