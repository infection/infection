<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\Console;

use _HumbugBox9658796bb9f0\Symfony\Component\Console\Input\InputInterface;
use _HumbugBox9658796bb9f0\Symfony\Component\Console\Input\StringInput;
use _HumbugBox9658796bb9f0\Symfony\Component\Console\Output\NullOutput;
use _HumbugBox9658796bb9f0\Symfony\Component\Console\Output\OutputInterface;
use _HumbugBox9658796bb9f0\Symfony\Component\Console\Style\SymfonyStyle;
final class IO extends SymfonyStyle
{
    private InputInterface $input;
    private OutputInterface $output;
    public function __construct(InputInterface $input, OutputInterface $output)
    {
        parent::__construct($input, $output);
        $this->input = $input;
        $this->output = $output;
    }
    public static function createNull() : self
    {
        return new self(new StringInput(''), new NullOutput());
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
