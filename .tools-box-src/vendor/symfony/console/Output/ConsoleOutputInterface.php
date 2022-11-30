<?php

namespace _HumbugBoxb47773b41c19\Symfony\Component\Console\Output;

interface ConsoleOutputInterface extends OutputInterface
{
    public function getErrorOutput() : OutputInterface;
    public function setErrorOutput(OutputInterface $error);
    public function section() : ConsoleSectionOutput;
}
