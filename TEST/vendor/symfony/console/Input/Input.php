<?php

namespace _HumbugBox9658796bb9f0\Symfony\Component\Console\Input;

use _HumbugBox9658796bb9f0\Symfony\Component\Console\Exception\InvalidArgumentException;
use _HumbugBox9658796bb9f0\Symfony\Component\Console\Exception\RuntimeException;
abstract class Input implements InputInterface, StreamableInputInterface
{
    protected $definition;
    protected $stream;
    protected $options = [];
    protected $arguments = [];
    protected $interactive = \true;
    public function __construct(InputDefinition $definition = null)
    {
        if (null === $definition) {
            $this->definition = new InputDefinition();
        } else {
            $this->bind($definition);
            $this->validate();
        }
    }
    public function bind(InputDefinition $definition)
    {
        $this->arguments = [];
        $this->options = [];
        $this->definition = $definition;
        $this->parse();
    }
    protected abstract function parse();
    public function validate()
    {
        $definition = $this->definition;
        $givenArguments = $this->arguments;
        $missingArguments = \array_filter(\array_keys($definition->getArguments()), function ($argument) use($definition, $givenArguments) {
            return !\array_key_exists($argument, $givenArguments) && $definition->getArgument($argument)->isRequired();
        });
        if (\count($missingArguments) > 0) {
            throw new RuntimeException(\sprintf('Not enough arguments (missing: "%s").', \implode(', ', $missingArguments)));
        }
    }
    public function isInteractive()
    {
        return $this->interactive;
    }
    public function setInteractive(bool $interactive)
    {
        $this->interactive = $interactive;
    }
    public function getArguments()
    {
        return \array_merge($this->definition->getArgumentDefaults(), $this->arguments);
    }
    public function getArgument(string $name)
    {
        if (!$this->definition->hasArgument($name)) {
            throw new InvalidArgumentException(\sprintf('The "%s" argument does not exist.', $name));
        }
        return $this->arguments[$name] ?? $this->definition->getArgument($name)->getDefault();
    }
    public function setArgument(string $name, $value)
    {
        if (!$this->definition->hasArgument($name)) {
            throw new InvalidArgumentException(\sprintf('The "%s" argument does not exist.', $name));
        }
        $this->arguments[$name] = $value;
    }
    public function hasArgument(string $name)
    {
        return $this->definition->hasArgument($name);
    }
    public function getOptions()
    {
        return \array_merge($this->definition->getOptionDefaults(), $this->options);
    }
    public function getOption(string $name)
    {
        if ($this->definition->hasNegation($name)) {
            if (null === ($value = $this->getOption($this->definition->negationToName($name)))) {
                return $value;
            }
            return !$value;
        }
        if (!$this->definition->hasOption($name)) {
            throw new InvalidArgumentException(\sprintf('The "%s" option does not exist.', $name));
        }
        return \array_key_exists($name, $this->options) ? $this->options[$name] : $this->definition->getOption($name)->getDefault();
    }
    public function setOption(string $name, $value)
    {
        if ($this->definition->hasNegation($name)) {
            $this->options[$this->definition->negationToName($name)] = !$value;
            return;
        } elseif (!$this->definition->hasOption($name)) {
            throw new InvalidArgumentException(\sprintf('The "%s" option does not exist.', $name));
        }
        $this->options[$name] = $value;
    }
    public function hasOption(string $name)
    {
        return $this->definition->hasOption($name) || $this->definition->hasNegation($name);
    }
    public function escapeToken(string $token)
    {
        return \preg_match('{^[\\w-]+$}', $token) ? $token : \escapeshellarg($token);
    }
    public function setStream($stream)
    {
        $this->stream = $stream;
    }
    public function getStream()
    {
        return $this->stream;
    }
}
