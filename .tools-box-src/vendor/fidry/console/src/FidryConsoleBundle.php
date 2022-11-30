<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\Fidry\Console;

use _HumbugBoxb47773b41c19\Fidry\Console\DependencyInjection\Compiler\AddConsoleCommandPass;
use _HumbugBoxb47773b41c19\Fidry\Console\DependencyInjection\FidryConsoleExtension;
use _HumbugBoxb47773b41c19\Symfony\Component\DependencyInjection\Compiler\PassConfig;
use _HumbugBoxb47773b41c19\Symfony\Component\DependencyInjection\ContainerBuilder;
use _HumbugBoxb47773b41c19\Symfony\Component\HttpKernel\Bundle\Bundle;
use _HumbugBoxb47773b41c19\Symfony\Component\HttpKernel\DependencyInjection\Extension;
final class FidryConsoleBundle extends Bundle
{
    public function getContainerExtension() : Extension
    {
        return new FidryConsoleExtension();
    }
    public function build(ContainerBuilder $container) : void
    {
        parent::build($container);
        $container->addCompilerPass(new AddConsoleCommandPass(), PassConfig::TYPE_BEFORE_REMOVING, 10);
    }
}
