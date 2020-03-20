<?php

declare(strict_types=1);

namespace Infection\Console;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

final class IO extends SymfonyStyle
{
    private $input;
    private $output;

    public static function createNull(): self
    {
        return new self(
            new StringInput(''),
            new NullOutput()
        );
    }

    public function __construct(InputInterface $input, OutputInterface $output)
    {
        parent::__construct($input, $output);

        $this->input = $input;
        $this->output = $output;
    }

    public function getInput(): InputInterface
    {
        return $this->input;
    }

    public function isInteractive(): bool
    {
        return $this->input->isInteractive();
    }

    public function getOutput(): OutputInterface
    {
        return $this->output;
    }
}
