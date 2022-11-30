<?php

namespace _HumbugBoxb47773b41c19\Symfony\Component\Console\Input;

use _HumbugBoxb47773b41c19\Symfony\Component\Console\Exception\InvalidArgumentException;
use _HumbugBoxb47773b41c19\Symfony\Component\Console\Exception\RuntimeException;
interface InputInterface
{
    public function getFirstArgument() : ?string;
    public function hasParameterOption(string|array $values, bool $onlyParams = \false) : bool;
    public function getParameterOption(string|array $values, string|bool|int|float|array|null $default = \false, bool $onlyParams = \false);
    public function bind(InputDefinition $definition);
    public function validate();
    public function getArguments() : array;
    public function getArgument(string $name);
    public function setArgument(string $name, mixed $value);
    public function hasArgument(string $name) : bool;
    public function getOptions() : array;
    public function getOption(string $name);
    public function setOption(string $name, mixed $value);
    public function hasOption(string $name) : bool;
    public function isInteractive() : bool;
    public function setInteractive(bool $interactive);
}
