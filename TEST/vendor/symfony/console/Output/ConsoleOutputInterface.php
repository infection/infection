<?php

namespace _HumbugBox9658796bb9f0\Symfony\Component\Console\Output;

interface ConsoleOutputInterface extends OutputInterface
{
    public function getErrorOutput();
    public function setErrorOutput(OutputInterface $error);
    public function section() : ConsoleSectionOutput;
}
