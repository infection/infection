<?php

namespace _HumbugBox9658796bb9f0\Symfony\Component\Console\Input;

use _HumbugBox9658796bb9f0\Symfony\Component\Console\Exception\InvalidArgumentException;
use _HumbugBox9658796bb9f0\Symfony\Component\Console\Exception\LogicException;
class InputDefinition
{
    private $arguments;
    private $requiredCount;
    private $lastArrayArgument;
    private $lastOptionalArgument;
    private $options;
    private $negations;
    private $shortcuts;
    public function __construct(array $definition = [])
    {
        $this->setDefinition($definition);
    }
    public function setDefinition(array $definition)
    {
        $arguments = [];
        $options = [];
        foreach ($definition as $item) {
            if ($item instanceof InputOption) {
                $options[] = $item;
            } else {
                $arguments[] = $item;
            }
        }
        $this->setArguments($arguments);
        $this->setOptions($options);
    }
    public function setArguments(array $arguments = [])
    {
        $this->arguments = [];
        $this->requiredCount = 0;
        $this->lastOptionalArgument = null;
        $this->lastArrayArgument = null;
        $this->addArguments($arguments);
    }
    public function addArguments(?array $arguments = [])
    {
        if (null !== $arguments) {
            foreach ($arguments as $argument) {
                $this->addArgument($argument);
            }
        }
    }
    public function addArgument(InputArgument $argument)
    {
        if (isset($this->arguments[$argument->getName()])) {
            throw new LogicException(\sprintf('An argument with name "%s" already exists.', $argument->getName()));
        }
        if (null !== $this->lastArrayArgument) {
            throw new LogicException(\sprintf('Cannot add a required argument "%s" after an array argument "%s".', $argument->getName(), $this->lastArrayArgument->getName()));
        }
        if ($argument->isRequired() && null !== $this->lastOptionalArgument) {
            throw new LogicException(\sprintf('Cannot add a required argument "%s" after an optional one "%s".', $argument->getName(), $this->lastOptionalArgument->getName()));
        }
        if ($argument->isArray()) {
            $this->lastArrayArgument = $argument;
        }
        if ($argument->isRequired()) {
            ++$this->requiredCount;
        } else {
            $this->lastOptionalArgument = $argument;
        }
        $this->arguments[$argument->getName()] = $argument;
    }
    public function getArgument($name)
    {
        if (!$this->hasArgument($name)) {
            throw new InvalidArgumentException(\sprintf('The "%s" argument does not exist.', $name));
        }
        $arguments = \is_int($name) ? \array_values($this->arguments) : $this->arguments;
        return $arguments[$name];
    }
    public function hasArgument($name)
    {
        $arguments = \is_int($name) ? \array_values($this->arguments) : $this->arguments;
        return isset($arguments[$name]);
    }
    public function getArguments()
    {
        return $this->arguments;
    }
    public function getArgumentCount()
    {
        return null !== $this->lastArrayArgument ? \PHP_INT_MAX : \count($this->arguments);
    }
    public function getArgumentRequiredCount()
    {
        return $this->requiredCount;
    }
    public function getArgumentDefaults()
    {
        $values = [];
        foreach ($this->arguments as $argument) {
            $values[$argument->getName()] = $argument->getDefault();
        }
        return $values;
    }
    public function setOptions(array $options = [])
    {
        $this->options = [];
        $this->shortcuts = [];
        $this->negations = [];
        $this->addOptions($options);
    }
    public function addOptions(array $options = [])
    {
        foreach ($options as $option) {
            $this->addOption($option);
        }
    }
    public function addOption(InputOption $option)
    {
        if (isset($this->options[$option->getName()]) && !$option->equals($this->options[$option->getName()])) {
            throw new LogicException(\sprintf('An option named "%s" already exists.', $option->getName()));
        }
        if (isset($this->negations[$option->getName()])) {
            throw new LogicException(\sprintf('An option named "%s" already exists.', $option->getName()));
        }
        if ($option->getShortcut()) {
            foreach (\explode('|', $option->getShortcut()) as $shortcut) {
                if (isset($this->shortcuts[$shortcut]) && !$option->equals($this->options[$this->shortcuts[$shortcut]])) {
                    throw new LogicException(\sprintf('An option with shortcut "%s" already exists.', $shortcut));
                }
            }
        }
        $this->options[$option->getName()] = $option;
        if ($option->getShortcut()) {
            foreach (\explode('|', $option->getShortcut()) as $shortcut) {
                $this->shortcuts[$shortcut] = $option->getName();
            }
        }
        if ($option->isNegatable()) {
            $negatedName = 'no-' . $option->getName();
            if (isset($this->options[$negatedName])) {
                throw new LogicException(\sprintf('An option named "%s" already exists.', $negatedName));
            }
            $this->negations[$negatedName] = $option->getName();
        }
    }
    public function getOption(string $name)
    {
        if (!$this->hasOption($name)) {
            throw new InvalidArgumentException(\sprintf('The "--%s" option does not exist.', $name));
        }
        return $this->options[$name];
    }
    public function hasOption(string $name)
    {
        return isset($this->options[$name]);
    }
    public function getOptions()
    {
        return $this->options;
    }
    public function hasShortcut(string $name)
    {
        return isset($this->shortcuts[$name]);
    }
    public function hasNegation(string $name) : bool
    {
        return isset($this->negations[$name]);
    }
    public function getOptionForShortcut(string $shortcut)
    {
        return $this->getOption($this->shortcutToName($shortcut));
    }
    public function getOptionDefaults()
    {
        $values = [];
        foreach ($this->options as $option) {
            $values[$option->getName()] = $option->getDefault();
        }
        return $values;
    }
    public function shortcutToName(string $shortcut) : string
    {
        if (!isset($this->shortcuts[$shortcut])) {
            throw new InvalidArgumentException(\sprintf('The "-%s" option does not exist.', $shortcut));
        }
        return $this->shortcuts[$shortcut];
    }
    public function negationToName(string $negation) : string
    {
        if (!isset($this->negations[$negation])) {
            throw new InvalidArgumentException(\sprintf('The "--%s" option does not exist.', $negation));
        }
        return $this->negations[$negation];
    }
    public function getSynopsis(bool $short = \false)
    {
        $elements = [];
        if ($short && $this->getOptions()) {
            $elements[] = '[options]';
        } elseif (!$short) {
            foreach ($this->getOptions() as $option) {
                $value = '';
                if ($option->acceptValue()) {
                    $value = \sprintf(' %s%s%s', $option->isValueOptional() ? '[' : '', \strtoupper($option->getName()), $option->isValueOptional() ? ']' : '');
                }
                $shortcut = $option->getShortcut() ? \sprintf('-%s|', $option->getShortcut()) : '';
                $negation = $option->isNegatable() ? \sprintf('|--no-%s', $option->getName()) : '';
                $elements[] = \sprintf('[%s--%s%s%s]', $shortcut, $option->getName(), $value, $negation);
            }
        }
        if (\count($elements) && $this->getArguments()) {
            $elements[] = '[--]';
        }
        $tail = '';
        foreach ($this->getArguments() as $argument) {
            $element = '<' . $argument->getName() . '>';
            if ($argument->isArray()) {
                $element .= '...';
            }
            if (!$argument->isRequired()) {
                $element = '[' . $element;
                $tail .= ']';
            }
            $elements[] = $element;
        }
        return \implode(' ', $elements) . $tail;
    }
}
