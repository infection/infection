<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\KevinGH\Box\Console\IO;

use _HumbugBoxb47773b41c19\Symfony\Component\Console\Input\ArgvInput;
use _HumbugBoxb47773b41c19\Symfony\Component\Console\Input\InputInterface;
use _HumbugBoxb47773b41c19\Symfony\Component\Console\Input\StringInput;
use _HumbugBoxb47773b41c19\Symfony\Component\Console\Output\ConsoleOutput;
use _HumbugBoxb47773b41c19\Symfony\Component\Console\Output\NullOutput;
use _HumbugBoxb47773b41c19\Symfony\Component\Console\Output\OutputInterface;
use _HumbugBoxb47773b41c19\Symfony\Component\Console\Style\SymfonyStyle;
final class IO extends SymfonyStyle
{
    private InputInterface $input;
    private OutputInterface $output;
    public static function createDefault() : self
    {
        return new self(new ArgvInput(), new ConsoleOutput());
    }
    public static function createNull() : self
    {
        return new self(new StringInput(''), new NullOutput());
    }
    public function __construct(InputInterface $input, OutputInterface $output)
    {
        parent::__construct($input, $output);
        $this->input = $input;
        $this->output = $output;
    }
    public function getInput() : InputInterface
    {
        return $this->input;
    }
    public function isInteractive() : bool
    {
        return $this->input->isInteractive();
    }
    public function getOutput() : OutputInterface
    {
        return $this->output;
    }
}
