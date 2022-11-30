<?php

namespace _HumbugBoxb47773b41c19\Symfony\Component\Console\Completion\Output;

use _HumbugBoxb47773b41c19\Symfony\Component\Console\Completion\CompletionSuggestions;
use _HumbugBoxb47773b41c19\Symfony\Component\Console\Output\OutputInterface;
interface CompletionOutputInterface
{
    public function write(CompletionSuggestions $suggestions, OutputInterface $output) : void;
}
