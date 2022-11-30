<?php

namespace _HumbugBox9658796bb9f0\Symfony\Component\Console\Input;

use _HumbugBox9658796bb9f0\Symfony\Component\Console\Exception\InvalidArgumentException;
use _HumbugBox9658796bb9f0\Symfony\Component\Console\Exception\LogicException;
class InputOption
{
    public const VALUE_NONE = 1;
    public const VALUE_REQUIRED = 2;
    public const VALUE_OPTIONAL = 4;
    public const VALUE_IS_ARRAY = 8;
    public const VALUE_NEGATABLE = 16;
    private $name;
    private $shortcut;
    private $mode;
    private $default;
    private $description;
    public function __construct(string $name, $shortcut = null, int $mode = null, string $description = '', $default = null)
    {
        if (\str_starts_with($name, '--')) {
            $name = \substr($name, 2);
        }
        if (empty($name)) {
            throw new InvalidArgumentException('An option name cannot be empty.');
        }
        if (empty($shortcut)) {
            $shortcut = null;
        }
        if (null !== $shortcut) {
            if (\is_array($shortcut)) {
                $shortcut = \implode('|', $shortcut);
            }
            $shortcuts = \preg_split('{(\\|)-?}', \ltrim($shortcut, '-'));
            $shortcuts = \array_filter($shortcuts);
            $shortcut = \implode('|', $shortcuts);
            if (empty($shortcut)) {
                throw new InvalidArgumentException('An option shortcut cannot be empty.');
            }
        }
        if (null === $mode) {
            $mode = self::VALUE_NONE;
        } elseif ($mode >= self::VALUE_NEGATABLE << 1 || $mode < 1) {
            throw new InvalidArgumentException(\sprintf('Option mode "%s" is not valid.', $mode));
        }
        $this->name = $name;
        $this->shortcut = $shortcut;
        $this->mode = $mode;
        $this->description = $description;
        if ($this->isArray() && !$this->acceptValue()) {
            throw new InvalidArgumentException('Impossible to have an option mode VALUE_IS_ARRAY if the option does not accept a value.');
        }
        if ($this->isNegatable() && $this->acceptValue()) {
            throw new InvalidArgumentException('Impossible to have an option mode VALUE_NEGATABLE if the option also accepts a value.');
        }
        $this->setDefault($default);
    }
    public function getShortcut()
    {
        return $this->shortcut;
    }
    public function getName()
    {
        return $this->name;
    }
    public function acceptValue()
    {
        return $this->isValueRequired() || $this->isValueOptional();
    }
    public function isValueRequired()
    {
        return self::VALUE_REQUIRED === (self::VALUE_REQUIRED & $this->mode);
    }
    public function isValueOptional()
    {
        return self::VALUE_OPTIONAL === (self::VALUE_OPTIONAL & $this->mode);
    }
    public function isArray()
    {
        return self::VALUE_IS_ARRAY === (self::VALUE_IS_ARRAY & $this->mode);
    }
    public function isNegatable() : bool
    {
        return self::VALUE_NEGATABLE === (self::VALUE_NEGATABLE & $this->mode);
    }
    public function setDefault($default = null)
    {
        if (self::VALUE_NONE === (self::VALUE_NONE & $this->mode) && null !== $default) {
            throw new LogicException('Cannot set a default value when using InputOption::VALUE_NONE mode.');
        }
        if ($this->isArray()) {
            if (null === $default) {
                $default = [];
            } elseif (!\is_array($default)) {
                throw new LogicException('A default value for an array option must be an array.');
            }
        }
        $this->default = $this->acceptValue() || $this->isNegatable() ? $default : \false;
    }
    public function getDefault()
    {
        return $this->default;
    }
    public function getDescription()
    {
        return $this->description;
    }
    public function equals(self $option)
    {
        return $option->getName() === $this->getName() && $option->getShortcut() === $this->getShortcut() && $option->getDefault() === $this->getDefault() && $option->isNegatable() === $this->isNegatable() && $option->isArray() === $this->isArray() && $option->isValueRequired() === $this->isValueRequired() && $option->isValueOptional() === $this->isValueOptional();
    }
}
