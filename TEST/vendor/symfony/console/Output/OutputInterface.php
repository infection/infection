<?php

namespace _HumbugBox9658796bb9f0\Symfony\Component\Console\Output;

use _HumbugBox9658796bb9f0\Symfony\Component\Console\Formatter\OutputFormatterInterface;
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
    public function write($messages, bool $newline = \false, int $options = 0);
    public function writeln($messages, int $options = 0);
    public function setVerbosity(int $level);
    public function getVerbosity();
    public function isQuiet();
    public function isVerbose();
    public function isVeryVerbose();
    public function isDebug();
    public function setDecorated(bool $decorated);
    public function isDecorated();
    public function setFormatter(OutputFormatterInterface $formatter);
    public function getFormatter();
}
