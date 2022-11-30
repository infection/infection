<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\Fidry\Console\Command;

use LogicException;
/**
@psalm-require-implements
*/
trait CommandAwareness
{
    private CommandRegistry $_commandRegistry;
    public function setCommandRegistry(CommandRegistry $commandRegistry) : void
    {
        $this->_commandRegistry = $commandRegistry;
    }
    private function getCommandRegistry() : CommandRegistry
    {
        /**
        @psalm-suppress */
        if (!isset($this->_commandRegistry)) {
            throw new LogicException('Expected the command registry to be configured');
        }
        return $this->_commandRegistry;
    }
}
