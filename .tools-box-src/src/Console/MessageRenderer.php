<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\KevinGH\Box\Console;

use function array_map;
use function count;
use _HumbugBoxb47773b41c19\Fidry\Console\Input\IO;
use _HumbugBoxb47773b41c19\KevinGH\Box\NotInstantiable;
use function sprintf;
use _HumbugBoxb47773b41c19\Webmozart\Assert\Assert;
final class MessageRenderer
{
    use NotInstantiable;
    public static function render(IO $io, array $recommendations, array $warnings) : void
    {
        Assert::allString($recommendations);
        Assert::allString($warnings);
        $renderMessage = static fn(string $message): string => "    - {$message}";
        if ([] === $recommendations) {
            $io->writeln('No recommendation found.');
        } else {
            $io->writeln(sprintf('💡  <recommendation>%d %s found:</recommendation>', count($recommendations), count($recommendations) > 1 ? 'recommendations' : 'recommendation'));
            $io->writeln(array_map($renderMessage, $recommendations));
        }
        if ([] === $warnings) {
            $io->writeln('No warning found.');
        } else {
            $io->writeln(sprintf('⚠️  <warning>%d %s found:</warning>', count($warnings), count($warnings) > 1 ? 'warnings' : 'warning'));
            $io->writeln(array_map($renderMessage, $warnings));
        }
    }
}
