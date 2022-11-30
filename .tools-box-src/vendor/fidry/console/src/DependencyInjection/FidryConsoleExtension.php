<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\Fidry\Console\DependencyInjection;

use _HumbugBoxb47773b41c19\Fidry\Console\Command\Command;
use _HumbugBoxb47773b41c19\Symfony\Component\Config\FileLocator;
use _HumbugBoxb47773b41c19\Symfony\Component\DependencyInjection\ContainerBuilder;
use _HumbugBoxb47773b41c19\Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use _HumbugBoxb47773b41c19\Symfony\Component\HttpKernel\DependencyInjection\Extension;
final class FidryConsoleExtension extends Extension
{
    private const SERVICES_DIR = __DIR__ . '/../../resources/config';
    public function load(array $configs, ContainerBuilder $container) : void
    {
        $loader = new XmlFileLoader($container, new FileLocator(self::SERVICES_DIR));
        $loader->load('services.xml');
        $container->registerForAutoconfiguration(Command::class)->addTag('fidry.console_command');
    }
}
