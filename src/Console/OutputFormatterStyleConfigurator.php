<?php

declare(strict_types=1);

namespace Infection\Console;

use Infection\Console\OutputFormatter\OutputFormatter;
use Infection\NotInstantiable;
use Symfony\Component\Console\Formatter\OutputFormatterInterface;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Output\OutputInterface;

final class OutputFormatterStyleConfigurator
{
    use NotInstantiable;

    public static function configure(OutputInterface $output): void
    {
        $formatter = $output->getFormatter();

        self::configureMutantStyle($formatter);
        self::configureDiffStyle($formatter);
        self::configureMutationScoreStyle($formatter);
    }
    
    private static function configureMutantStyle(OutputFormatterInterface $formatter): void
    {
        $formatter->setStyle('with-error', new OutputFormatterStyle('green'));
        $formatter->setStyle(
            'uncovered',
            new OutputFormatterStyle('blue', null, ['bold'])
        );
        $formatter->setStyle('timeout', new OutputFormatterStyle('yellow'));
        $formatter->setStyle(
            'escaped',
            new OutputFormatterStyle('red', null, ['bold'])
        );
        $formatter->setStyle('killed', new OutputFormatterStyle('green'));
        $formatter->setStyle('code', new OutputFormatterStyle('white'));
    }

    private static function configureDiffStyle(OutputFormatterInterface $formatter): void
    {
        $formatter->setStyle('diff-add', new OutputFormatterStyle('green'));
        $formatter->setStyle('diff-del', new OutputFormatterStyle('red'));
    }

    private static function configureMutationScoreStyle(OutputFormatterInterface $formatter): void
    {
        $formatter->setStyle(
            'low',
            new OutputFormatterStyle('red', null, ['bold'])
        );
        $formatter->setStyle(
            'medium',
            new OutputFormatterStyle('yellow', null, ['bold'])
        );
        $formatter->setStyle(
            'high',
            new OutputFormatterStyle('green', null, ['bold'])
        );
    }
}
