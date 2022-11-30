<?php

namespace _HumbugBox9658796bb9f0\Symfony\Component\Console\Input;

use _HumbugBox9658796bb9f0\Symfony\Component\Console\Exception\InvalidArgumentException;
use _HumbugBox9658796bb9f0\Symfony\Component\Console\Exception\InvalidOptionException;
class ArrayInput extends Input
{
    private $parameters;
    public function __construct(array $parameters, InputDefinition $definition = null)
    {
        $this->parameters = $parameters;
        parent::__construct($definition);
    }
    public function getFirstArgument()
    {
        foreach ($this->parameters as $param => $value) {
            if ($param && \is_string($param) && '-' === $param[0]) {
                continue;
            }
            return $value;
        }
        return null;
    }
    public function hasParameterOption($values, bool $onlyParams = \false)
    {
        $values = (array) $values;
        foreach ($this->parameters as $k => $v) {
            if (!\is_int($k)) {
                $v = $k;
            }
            if ($onlyParams && '--' === $v) {
                return \false;
            }
            if (\in_array($v, $values)) {
                return \true;
            }
        }
        return \false;
    }
    public function getParameterOption($values, $default = \false, bool $onlyParams = \false)
    {
        $values = (array) $values;
        foreach ($this->parameters as $k => $v) {
            if ($onlyParams && ('--' === $k || \is_int($k) && '--' === $v)) {
                return $default;
            }
            if (\is_int($k)) {
                if (\in_array($v, $values)) {
                    return \true;
                }
            } elseif (\in_array($k, $values)) {
                return $v;
            }
        }
        return $default;
    }
    public function __toString()
    {
        $params = [];
        foreach ($this->parameters as $param => $val) {
            if ($param && \is_string($param) && '-' === $param[0]) {
                $glue = '-' === $param[1] ? '=' : ' ';
                if (\is_array($val)) {
                    foreach ($val as $v) {
                        $params[] = $param . ('' != $v ? $glue . $this->escapeToken($v) : '');
                    }
                } else {
                    $params[] = $param . ('' != $val ? $glue . $this->escapeToken($val) : '');
                }
            } else {
                $params[] = \is_array($val) ? \implode(' ', \array_map([$this, 'escapeToken'], $val)) : $this->escapeToken($val);
            }
        }
        return \implode(' ', $params);
    }
    protected function parse()
    {
        foreach ($this->parameters as $key => $value) {
            if ('--' === $key) {
                return;
            }
            if (\str_starts_with($key, '--')) {
                $this->addLongOption(\substr($key, 2), $value);
            } elseif (\str_starts_with($key, '-')) {
                $this->addShortOption(\substr($key, 1), $value);
            } else {
                $this->addArgument($key, $value);
            }
        }
    }
    private function addShortOption(string $shortcut, $value)
    {
        if (!$this->definition->hasShortcut($shortcut)) {
            throw new InvalidOptionException(\sprintf('The "-%s" option does not exist.', $shortcut));
        }
        $this->addLongOption($this->definition->getOptionForShortcut($shortcut)->getName(), $value);
    }
    private function addLongOption(string $name, $value)
    {
        if (!$this->definition->hasOption($name)) {
            if (!$this->definition->hasNegation($name)) {
                throw new InvalidOptionException(\sprintf('The "--%s" option does not exist.', $name));
            }
            $optionName = $this->definition->negationToName($name);
            $this->options[$optionName] = \false;
            return;
        }
        $option = $this->definition->getOption($name);
        if (null === $value) {
            if ($option->isValueRequired()) {
                throw new InvalidOptionException(\sprintf('The "--%s" option requires a value.', $name));
            }
            if (!$option->isValueOptional()) {
                $value = \true;
            }
        }
        $this->options[$name] = $value;
    }
    private function addArgument($name, $value)
    {
        if (!$this->definition->hasArgument($name)) {
            throw new InvalidArgumentException(\sprintf('The "%s" argument does not exist.', $name));
        }
        $this->arguments[$name] = $value;
    }
}
