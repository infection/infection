<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\Console;

use _HumbugBox9658796bb9f0\Infection\CannotBeInstantiated;
use _HumbugBox9658796bb9f0\Symfony\Component\Console\Formatter\OutputFormatterInterface;
use _HumbugBox9658796bb9f0\Symfony\Component\Console\Formatter\OutputFormatterStyle;
use _HumbugBox9658796bb9f0\Symfony\Component\Console\Output\OutputInterface;
final class OutputFormatterStyleConfigurator
{
    use CannotBeInstantiated;
    public static function configure(OutputInterface $output) : void
    {
        $formatter = $output->getFormatter();
        self::configureMutantStyle($formatter);
        self::configureDiffStyle($formatter);
        self::configureMutationScoreStyle($formatter);
    }
    private static function configureMutantStyle(OutputFormatterInterface $formatter) : void
    {
        $formatter->setStyle('with-error', new OutputFormatterStyle('green'));
        $formatter->setStyle('with-syntax-error', new OutputFormatterStyle('red', null, ['bold']));
        $formatter->setStyle('uncovered', new OutputFormatterStyle('blue', null, ['bold']));
        $formatter->setStyle('timeout', new OutputFormatterStyle('yellow'));
        $formatter->setStyle('escaped', new OutputFormatterStyle('red', null, ['bold']));
        $formatter->setStyle('killed', new OutputFormatterStyle('green'));
        $formatter->setStyle('skipped', new OutputFormatterStyle('magenta'));
        $formatter->setStyle('ignored', new OutputFormatterStyle('white'));
        $formatter->setStyle('code', new OutputFormatterStyle('white'));
    }
    private static function configureDiffStyle(OutputFormatterInterface $formatter) : void
    {
        $formatter->setStyle('diff-add', new OutputFormatterStyle('green'));
        $formatter->setStyle('diff-del', new OutputFormatterStyle('red'));
    }
    private static function configureMutationScoreStyle(OutputFormatterInterface $formatter) : void
    {
        $formatter->setStyle('low', new OutputFormatterStyle('red', null, ['bold']));
        $formatter->setStyle('medium', new OutputFormatterStyle('yellow', null, ['bold']));
        $formatter->setStyle('high', new OutputFormatterStyle('green', null, ['bold']));
    }
}
