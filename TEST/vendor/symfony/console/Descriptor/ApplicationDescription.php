<?php

namespace _HumbugBox9658796bb9f0\Symfony\Component\Console\Descriptor;

use _HumbugBox9658796bb9f0\Symfony\Component\Console\Application;
use _HumbugBox9658796bb9f0\Symfony\Component\Console\Command\Command;
use _HumbugBox9658796bb9f0\Symfony\Component\Console\Exception\CommandNotFoundException;
class ApplicationDescription
{
    public const GLOBAL_NAMESPACE = '_global';
    private $application;
    private $namespace;
    private $showHidden;
    private $namespaces;
    private $commands;
    private $aliases;
    public function __construct(Application $application, string $namespace = null, bool $showHidden = \false)
    {
        $this->application = $application;
        $this->namespace = $namespace;
        $this->showHidden = $showHidden;
    }
    public function getNamespaces() : array
    {
        if (null === $this->namespaces) {
            $this->inspectApplication();
        }
        return $this->namespaces;
    }
    public function getCommands() : array
    {
        if (null === $this->commands) {
            $this->inspectApplication();
        }
        return $this->commands;
    }
    public function getCommand(string $name) : Command
    {
        if (!isset($this->commands[$name]) && !isset($this->aliases[$name])) {
            throw new CommandNotFoundException(\sprintf('Command "%s" does not exist.', $name));
        }
        return $this->commands[$name] ?? $this->aliases[$name];
    }
    private function inspectApplication()
    {
        $this->commands = [];
        $this->namespaces = [];
        $all = $this->application->all($this->namespace ? $this->application->findNamespace($this->namespace) : null);
        foreach ($this->sortCommands($all) as $namespace => $commands) {
            $names = [];
            foreach ($commands as $name => $command) {
                if (!$command->getName() || !$this->showHidden && $command->isHidden()) {
                    continue;
                }
                if ($command->getName() === $name) {
                    $this->commands[$name] = $command;
                } else {
                    $this->aliases[$name] = $command;
                }
                $names[] = $name;
            }
            $this->namespaces[$namespace] = ['id' => $namespace, 'commands' => $names];
        }
    }
    private function sortCommands(array $commands) : array
    {
        $namespacedCommands = [];
        $globalCommands = [];
        $sortedCommands = [];
        foreach ($commands as $name => $command) {
            $key = $this->application->extractNamespace($name, 1);
            if (\in_array($key, ['', self::GLOBAL_NAMESPACE], \true)) {
                $globalCommands[$name] = $command;
            } else {
                $namespacedCommands[$key][$name] = $command;
            }
        }
        if ($globalCommands) {
            \ksort($globalCommands);
            $sortedCommands[self::GLOBAL_NAMESPACE] = $globalCommands;
        }
        if ($namespacedCommands) {
            \ksort($namespacedCommands);
            foreach ($namespacedCommands as $key => $commandsSet) {
                \ksort($commandsSet);
                $sortedCommands[$key] = $commandsSet;
            }
        }
        return $sortedCommands;
    }
}
