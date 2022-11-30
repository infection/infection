<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\KevinGH\Box\Console;

use _HumbugBoxb47773b41c19\Fidry\Console\Input\IO;
use InvalidArgumentException;
use _HumbugBoxb47773b41c19\KevinGH\Box\Configuration\Configuration;
use _HumbugBoxb47773b41c19\KevinGH\Box\Configuration\ConfigurationLoader as ConfigLoader;
use _HumbugBoxb47773b41c19\KevinGH\Box\Configuration\NoConfigurationFound;
use _HumbugBoxb47773b41c19\KevinGH\Box\Json\JsonValidationException;
use _HumbugBoxb47773b41c19\KevinGH\Box\NotInstantiable;
use function sprintf;
final class ConfigurationLoader
{
    use NotInstantiable;
    public static function getConfig(?string $configPath, IO $io, bool $allowNoFile) : Configuration
    {
        $configPath = self::getConfigPath($configPath, $io, $allowNoFile);
        $configLoader = new ConfigLoader();
        try {
            return $configLoader->loadFile($configPath);
        } catch (InvalidArgumentException $invalidConfig) {
            $io->error('The configuration file is invalid.');
            throw $invalidConfig;
        }
    }
    private static function getConfigPath(?string $configPath, IO $io, bool $allowNoFile) : ?string
    {
        try {
            $configPath ??= ConfigurationLocator::findDefaultPath();
        } catch (NoConfigurationFound $noConfigurationFound) {
            if (\false === $allowNoFile) {
                throw $noConfigurationFound;
            }
            $io->comment('Loading without a configuration file.');
            return null;
        }
        $io->comment(sprintf('Loading the configuration file "<comment>%s</comment>".', $configPath));
        return $configPath;
    }
}
