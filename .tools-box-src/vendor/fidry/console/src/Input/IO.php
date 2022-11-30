<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\Fidry\Console\Input;

use _HumbugBoxb47773b41c19\Symfony\Component\Console\Input\ArgvInput;
use _HumbugBoxb47773b41c19\Symfony\Component\Console\Input\InputInterface;
use _HumbugBoxb47773b41c19\Symfony\Component\Console\Input\StringInput;
use _HumbugBoxb47773b41c19\Symfony\Component\Console\Output\ConsoleOutput;
use _HumbugBoxb47773b41c19\Symfony\Component\Console\Output\NullOutput;
use _HumbugBoxb47773b41c19\Symfony\Component\Console\Output\OutputInterface;
use _HumbugBoxb47773b41c19\Symfony\Component\Console\Style\SymfonyStyle;
class IO extends SymfonyStyle
{
    private InputInterface $input;
    private OutputInterface $output;
    public function __construct(InputInterface $input, OutputInterface $output)
    {
        parent::__construct($input, $output);
        $this->input = $input;
        $this->output = $output;
    }
    public static function createDefault() : self
    {
        return new self(new ArgvInput(), new ConsoleOutput());
    }
    public static function createNull() : self
    {
        return new self(new StringInput(''), new NullOutput());
    }
    public function getErrorIO() : self
    {
        return new self($this->input, $this->getErrorOutput());
    }
    public function withInput(InputInterface $input) : self
    {
        return new self($input, $this->output);
    }
    public function getInput() : InputInterface
    {
        return $this->input;
    }
    public function isInteractive() : bool
    {
        return $this->input->isInteractive();
    }
    public function withOutput(OutputInterface $output) : self
    {
        return new self($this->input, $output);
    }
    public function getOutput() : OutputInterface
    {
        return $this->output;
    }
    public function getArgument(string $name) : TypedInput
    {
        return TypedInput::fromArgument($this->input->getArgument($name), $name);
    }
    public function getOption(string $name) : TypedInput
    {
        return TypedInput::fromOption($this->input->getOption($name), $name);
    }
    public function hasOption(string $name, bool $onlyRealParams = \false) : bool
    {
        return $this->input->hasParameterOption($name, $onlyRealParams);
    }
}
