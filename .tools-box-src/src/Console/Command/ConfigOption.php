<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\KevinGH\Box\Console\Command;

use _HumbugBoxb47773b41c19\Fidry\Console\Input\IO;
use _HumbugBoxb47773b41c19\KevinGH\Box\Configuration\Configuration;
use _HumbugBoxb47773b41c19\KevinGH\Box\Console\ConfigurationLoader;
use _HumbugBoxb47773b41c19\KevinGH\Box\Json\JsonValidationException;
use _HumbugBoxb47773b41c19\KevinGH\Box\NotInstantiable;
use _HumbugBoxb47773b41c19\Symfony\Component\Console\Input\InputOption;
final class ConfigOption
{
    use NotInstantiable;
    private const CONFIG_PARAM = 'config';
    public static function getOptionInput() : InputOption
    {
        return new InputOption(self::CONFIG_PARAM, 'c', InputOption::VALUE_REQUIRED, 'The alternative configuration file path.');
    }
    public static function getConfig(IO $io, bool $allowNoFile = \false) : Configuration
    {
        return ConfigurationLoader::getConfig($io->getInput()->getOption(self::CONFIG_PARAM), $io, $allowNoFile);
    }
}
