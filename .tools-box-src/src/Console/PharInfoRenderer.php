<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\KevinGH\Box\Console;

use function array_filter;
use function array_key_last;
use function array_sum;
use function count;
use _HumbugBoxb47773b41c19\Fidry\Console\Input\IO;
use function _HumbugBoxb47773b41c19\KevinGH\Box\format_size;
use _HumbugBoxb47773b41c19\KevinGH\Box\NotInstantiable;
use _HumbugBoxb47773b41c19\KevinGH\Box\PharInfo\PharInfo;
use function key;
use function round;
use function _HumbugBoxb47773b41c19\Safe\filesize;
use function _HumbugBoxb47773b41c19\Safe\sprintf;
final class PharInfoRenderer
{
    use NotInstantiable;
    public static function renderCompression(PharInfo $pharInfo, IO $io) : void
    {
        $count = array_filter($pharInfo->getCompressionCount());
        $totalCount = array_sum($count);
        if (1 === count($count)) {
            $io->writeln(sprintf('<comment>Compression:</comment> %s', key($count)));
            return;
        }
        $io->writeln('<comment>Compression:</comment>');
        $lastAlgorithmName = array_key_last($count);
        $totalPercentage = 100;
        foreach ($count as $algorithmName => $nbrOfFiles) {
            if ($lastAlgorithmName === $algorithmName) {
                $percentage = $totalPercentage;
            } else {
                $percentage = round($nbrOfFiles * 100 / $totalCount, 2);
                $totalPercentage -= $percentage;
            }
            $io->writeln(sprintf('  - %s (%0.2f%%)', $algorithmName, $percentage));
        }
    }
    public static function renderSignature(PharInfo $pharInfo, IO $io) : void
    {
        $signature = $pharInfo->getPhar()->getSignature();
        if (\false === $signature) {
            $io->writeln('<comment>Signature unreadable</comment>');
            return;
        }
        $io->writeln(sprintf('<comment>Signature:</comment> %s', $signature['hash_type']));
        $io->writeln(sprintf('<comment>Signature Hash:</comment> %s', $signature['hash']));
    }
    public static function renderMetadata(PharInfo $pharInfo, IO $io) : void
    {
        $metadata = $pharInfo->getNormalizedMetadata();
        if (null === $metadata) {
            $io->writeln('<comment>Metadata:</comment> None');
        } else {
            $io->writeln('<comment>Metadata:</comment>');
            $io->writeln($metadata);
        }
    }
    public static function renderContentsSummary(PharInfo $pharInfo, IO $io) : void
    {
        $count = array_filter($pharInfo->getCompressionCount());
        $totalCount = array_sum($count);
        $io->writeln(sprintf('<comment>Contents:</comment>%s (%s)', 1 === $totalCount ? ' 1 file' : " {$totalCount} files", format_size(filesize($pharInfo->getPhar()->getPath()))));
    }
}
