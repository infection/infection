<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\KevinGH\Box\Console;

use function file_exists;
use _HumbugBoxb47773b41c19\KevinGH\Box\Configuration\NoConfigurationFound;
use _HumbugBoxb47773b41c19\KevinGH\Box\NotInstantiable;
use function realpath;
final class ConfigurationLocator
{
    use NotInstantiable;
    private const FILE_NAME = 'box.json';
    private static array $candidates;
    public static function findDefaultPath() : string
    {
        if (!isset(self::$candidates)) {
            self::$candidates = [self::FILE_NAME, self::FILE_NAME . '.dist'];
        }
        foreach (self::$candidates as $candidate) {
            if (file_exists($candidate)) {
                return realpath($candidate);
            }
        }
        throw new NoConfigurationFound();
    }
}
