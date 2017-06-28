<?php
/**
 * Copyright © 2017 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Config;

use Symfony\Component\Console\Helper\FormatterHelper;
use Symfony\Component\Console\Output\OutputInterface;

class ConsoleHelper
{
    /**
     * @var FormatterHelper
     */
    private $formatterHelper;

    public function __construct(FormatterHelper $formatterHelper)
    {
        $this->formatterHelper = $formatterHelper;
    }

    public function writeSection(OutputInterface $output, $text, $style = 'bg=blue;fg=white')
    {
        $output->writeln([
            '',
            $this->formatterHelper->formatBlock($text, $style, true),
            '',
        ]);
    }

    public function getQuestion($question, $default, $sep = ':')
    {
        return $default
            ? sprintf('<info>%s</info> [<comment>%s</comment>]%s ', $question, $default, $sep)
            : sprintf('<info>%s</info>%s ', $question, $sep);
    }
}