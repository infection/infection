<?php

namespace _HumbugBox9658796bb9f0\Symfony\Component\Console\Tester;

use _HumbugBox9658796bb9f0\Symfony\Component\Console\Command\Command;
use _HumbugBox9658796bb9f0\Symfony\Component\Console\Completion\CompletionInput;
use _HumbugBox9658796bb9f0\Symfony\Component\Console\Completion\CompletionSuggestions;
class CommandCompletionTester
{
    private $command;
    public function __construct(Command $command)
    {
        $this->command = $command;
    }
    public function complete(array $input) : array
    {
        $currentIndex = \count($input);
        if ('' === \end($input)) {
            \array_pop($input);
        }
        \array_unshift($input, $this->command->getName());
        $completionInput = CompletionInput::fromTokens($input, $currentIndex);
        $completionInput->bind($this->command->getDefinition());
        $suggestions = new CompletionSuggestions();
        $this->command->complete($completionInput, $suggestions);
        $options = [];
        foreach ($suggestions->getOptionSuggestions() as $option) {
            $options[] = '--' . $option->getName();
        }
        return \array_map('strval', \array_merge($options, $suggestions->getValueSuggestions()));
    }
}
