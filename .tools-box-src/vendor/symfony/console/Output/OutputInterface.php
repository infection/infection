<?php

namespace _HumbugBoxb47773b41c19\Symfony\Component\Console\Output;

use _HumbugBoxb47773b41c19\Symfony\Component\Console\Formatter\OutputFormatterInterface;
interface OutputInterface
{
    public const VERBOSITY_QUIET = 16;
    public const VERBOSITY_NORMAL = 32;
    public const VERBOSITY_VERBOSE = 64;
    public const VERBOSITY_VERY_VERBOSE = 128;
    public const VERBOSITY_DEBUG = 256;
    public const OUTPUT_NORMAL = 1;
    public const OUTPUT_RAW = 2;
    public const OUTPUT_PLAIN = 4;
    public function write(string|iterable $messages, bool $newline = \false, int $options = 0);
    public function writeln(string|iterable $messages, int $options = 0);
    public function setVerbosity(int $level);
    public function getVerbosity() : int;
    public function isQuiet() : bool;
    public function isVerbose() : bool;
    public function isVeryVerbose() : bool;
    public function isDebug() : bool;
    public function setDecorated(bool $decorated);
    public function isDecorated() : bool;
    public function setFormatter(OutputFormatterInterface $formatter);
    public function getFormatter() : OutputFormatterInterface;
}
