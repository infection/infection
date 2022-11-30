<?php

namespace _HumbugBox9658796bb9f0\Symfony\Component\Console\Completion\Output;

use _HumbugBox9658796bb9f0\Symfony\Component\Console\Completion\CompletionSuggestions;
use _HumbugBox9658796bb9f0\Symfony\Component\Console\Output\OutputInterface;
interface CompletionOutputInterface
{
    public function write(CompletionSuggestions $suggestions, OutputInterface $output) : void;
}
