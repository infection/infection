<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\Config;

use function _HumbugBox9658796bb9f0\Safe\sprintf;
use _HumbugBox9658796bb9f0\Symfony\Component\Console\Helper\FormatterHelper;
use _HumbugBox9658796bb9f0\Symfony\Component\Console\Output\OutputInterface;
class ConsoleHelper
{
    private FormatterHelper $formatterHelper;
    public function __construct(FormatterHelper $formatterHelper)
    {
        $this->formatterHelper = $formatterHelper;
    }
    public function writeSection(OutputInterface $output, string $text, string $style = 'bg=blue;fg=white') : void
    {
        $output->writeln(['', $this->formatterHelper->formatBlock($text, $style, \true), '']);
    }
    public function getQuestion(string $question, ?string $default = null, string $sep = ':') : string
    {
        return $default !== null ? sprintf('<info>%s</info> [<comment>%s</comment>]%s ', $question, $default, $sep) : sprintf('<info>%s</info>%s ', $question, $sep);
    }
}
