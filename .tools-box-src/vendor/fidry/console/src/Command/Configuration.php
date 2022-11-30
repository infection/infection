<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\Fidry\Console\Command;

use _HumbugBoxb47773b41c19\Symfony\Component\Console\Input\InputArgument;
use _HumbugBoxb47773b41c19\Symfony\Component\Console\Input\InputOption;
final class Configuration
{
    private string $name;
    private string $description;
    private string $help;
    private array $arguments;
    private array $options;
    public function __construct(string $name, string $description, string $help, array $arguments = [], array $options = [])
    {
        $this->name = $name;
        $this->description = $description;
        $this->help = $help;
        $this->arguments = $arguments;
        $this->options = $options;
    }
    public function getName() : string
    {
        return $this->name;
    }
    public function getDescription() : string
    {
        return $this->description;
    }
    public function getHelp() : string
    {
        return $this->help;
    }
    public function getArguments() : array
    {
        return $this->arguments;
    }
    public function getOptions() : array
    {
        return $this->options;
    }
}
