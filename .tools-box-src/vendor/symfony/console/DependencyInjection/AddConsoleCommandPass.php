<?php

namespace _HumbugBoxb47773b41c19\Symfony\Component\Console\DependencyInjection;

use _HumbugBoxb47773b41c19\Symfony\Component\Console\Command\Command;
use _HumbugBoxb47773b41c19\Symfony\Component\Console\Command\LazyCommand;
use _HumbugBoxb47773b41c19\Symfony\Component\Console\CommandLoader\ContainerCommandLoader;
use _HumbugBoxb47773b41c19\Symfony\Component\DependencyInjection\Argument\ServiceClosureArgument;
use _HumbugBoxb47773b41c19\Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use _HumbugBoxb47773b41c19\Symfony\Component\DependencyInjection\Compiler\ServiceLocatorTagPass;
use _HumbugBoxb47773b41c19\Symfony\Component\DependencyInjection\ContainerBuilder;
use _HumbugBoxb47773b41c19\Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use _HumbugBoxb47773b41c19\Symfony\Component\DependencyInjection\Reference;
use _HumbugBoxb47773b41c19\Symfony\Component\DependencyInjection\TypedReference;
class AddConsoleCommandPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $commandServices = $container->findTaggedServiceIds('console.command', \true);
        $lazyCommandMap = [];
        $lazyCommandRefs = [];
        $serviceIds = [];
        foreach ($commandServices as $id => $tags) {
            $definition = $container->getDefinition($id);
            $definition->addTag('container.no_preload');
            $class = $container->getParameterBag()->resolveValue($definition->getClass());
            if (isset($tags[0]['command'])) {
                $aliases = $tags[0]['command'];
            } else {
                if (!($r = $container->getReflectionClass($class))) {
                    throw new InvalidArgumentException(\sprintf('Class "%s" used for service "%s" cannot be found.', $class, $id));
                }
                if (!$r->isSubclassOf(Command::class)) {
                    throw new InvalidArgumentException(\sprintf('The service "%s" tagged "%s" must be a subclass of "%s".', $id, 'console.command', Command::class));
                }
                $aliases = \str_replace('%', '%%', $class::getDefaultName() ?? '');
            }
            $aliases = \explode('|', $aliases ?? '');
            $commandName = \array_shift($aliases);
            if ($isHidden = '' === $commandName) {
                $commandName = \array_shift($aliases);
            }
            if (null === $commandName) {
                if (!$definition->isPublic() || $definition->isPrivate() || $definition->hasTag('container.private')) {
                    $commandId = 'console.command.public_alias.' . $id;
                    $container->setAlias($commandId, $id)->setPublic(\true);
                    $id = $commandId;
                }
                $serviceIds[] = $id;
                continue;
            }
            $description = $tags[0]['description'] ?? null;
            unset($tags[0]);
            $lazyCommandMap[$commandName] = $id;
            $lazyCommandRefs[$id] = new TypedReference($id, $class);
            foreach ($aliases as $alias) {
                $lazyCommandMap[$alias] = $id;
            }
            foreach ($tags as $tag) {
                if (isset($tag['command'])) {
                    $aliases[] = $tag['command'];
                    $lazyCommandMap[$tag['command']] = $id;
                }
                $description ??= $tag['description'] ?? null;
            }
            $definition->addMethodCall('setName', [$commandName]);
            if ($aliases) {
                $definition->addMethodCall('setAliases', [$aliases]);
            }
            if ($isHidden) {
                $definition->addMethodCall('setHidden', [\true]);
            }
            if (!$description) {
                if (!($r = $container->getReflectionClass($class))) {
                    throw new InvalidArgumentException(\sprintf('Class "%s" used for service "%s" cannot be found.', $class, $id));
                }
                if (!$r->isSubclassOf(Command::class)) {
                    throw new InvalidArgumentException(\sprintf('The service "%s" tagged "%s" must be a subclass of "%s".', $id, 'console.command', Command::class));
                }
                $description = \str_replace('%', '%%', $class::getDefaultDescription() ?? '');
            }
            if ($description) {
                $definition->addMethodCall('setDescription', [$description]);
                $container->register('.' . $id . '.lazy', LazyCommand::class)->setArguments([$commandName, $aliases, $description, $isHidden, new ServiceClosureArgument($lazyCommandRefs[$id])]);
                $lazyCommandRefs[$id] = new Reference('.' . $id . '.lazy');
            }
        }
        $container->register('console.command_loader', ContainerCommandLoader::class)->setPublic(\true)->addTag('container.no_preload')->setArguments([ServiceLocatorTagPass::register($container, $lazyCommandRefs), $lazyCommandMap]);
        $container->setParameter('console.command.ids', $serviceIds);
    }
}
