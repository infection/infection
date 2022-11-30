<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\Fidry\Console\DependencyInjection\Compiler;

use _HumbugBoxb47773b41c19\Fidry\Console\Command\LazyCommand;
use _HumbugBoxb47773b41c19\Fidry\Console\Command\SymfonyCommand;
use InvalidArgumentException;
use _HumbugBoxb47773b41c19\Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use _HumbugBoxb47773b41c19\Symfony\Component\DependencyInjection\ContainerBuilder;
use _HumbugBoxb47773b41c19\Symfony\Component\DependencyInjection\Definition;
use function sprintf;
final class AddConsoleCommandPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container) : void
    {
        $container->addDefinitions(self::createDefinitions($container));
    }
    private static function createDefinitions(ContainerBuilder $containerBuilder) : array
    {
        $tagsByServiceId = $containerBuilder->findTaggedServiceIds('fidry.console_command');
        $commandDefinitions = [];
        foreach ($tagsByServiceId as $id => $_tags) {
            $commandDefinitions[$id] = self::createDefinition($id, $containerBuilder);
        }
        return $commandDefinitions;
    }
    private static function createDefinition(string $id, ContainerBuilder $containerBuilder) : Definition
    {
        $decoratedCommandDefinition = $containerBuilder->getDefinition($id);
        /**
        @psalm-suppress */
        $commandTagAttributes = self::createCommandTagAttributes($id, $decoratedCommandDefinition->getClass(), $containerBuilder);
        $definition = new Definition(SymfonyCommand::class, [$decoratedCommandDefinition]);
        $definition->addTag('console.command', $commandTagAttributes);
        return $definition;
    }
    private static function createCommandTagAttributes(string $id, ?string $definitionClassName, ContainerBuilder $containerBuilder) : array
    {
        if (!self::isLazyCommand($id, $definitionClassName, $containerBuilder)) {
            return [];
        }
        return ['command' => $definitionClassName::getName(), 'description' => $definitionClassName::getDescription()];
    }
    /**
    @psalm-assert-if-true
    */
    private static function isLazyCommand(string $id, ?string $definitionClassName, ContainerBuilder $containerBuilder) : bool
    {
        if (null === $definitionClassName) {
            return \false;
        }
        $classReflection = $containerBuilder->getReflectionClass($definitionClassName);
        if (null === $classReflection) {
            throw new InvalidArgumentException(sprintf('Class "%s" used for service "%s" cannot be found.', $definitionClassName, $id));
        }
        return $classReflection->isSubclassOf(LazyCommand::class);
    }
}
