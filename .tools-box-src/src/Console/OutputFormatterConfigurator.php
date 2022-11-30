<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\KevinGH\Box\Console;

use _HumbugBoxb47773b41c19\Fidry\Console\Input\IO;
use _HumbugBoxb47773b41c19\KevinGH\Box\NotInstantiable;
use _HumbugBoxb47773b41c19\Symfony\Component\Console\Formatter\OutputFormatterInterface;
use _HumbugBoxb47773b41c19\Symfony\Component\Console\Formatter\OutputFormatterStyle;
final class OutputFormatterConfigurator
{
    use NotInstantiable;
    public static function configure(IO $io) : void
    {
        self::configureFormatter($io->getOutput()->getFormatter());
    }
    public static function configureFormatter(OutputFormatterInterface $outputFormatter) : void
    {
        $outputFormatter->setStyle('recommendation', new OutputFormatterStyle('black', 'yellow'));
        $outputFormatter->setStyle('warning', new OutputFormatterStyle('white', 'red'));
    }
}
