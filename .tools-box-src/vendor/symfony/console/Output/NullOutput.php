<?php

namespace _HumbugBoxb47773b41c19\Symfony\Component\Console\Output;

use _HumbugBoxb47773b41c19\Symfony\Component\Console\Formatter\NullOutputFormatter;
use _HumbugBoxb47773b41c19\Symfony\Component\Console\Formatter\OutputFormatterInterface;
class NullOutput implements OutputInterface
{
    private NullOutputFormatter $formatter;
    public function setFormatter(OutputFormatterInterface $formatter)
    {
    }
    public function getFormatter() : OutputFormatterInterface
    {
        return $this->formatter ??= new NullOutputFormatter();
    }
    public function setDecorated(bool $decorated)
    {
    }
    public function isDecorated() : bool
    {
        return \false;
    }
    public function setVerbosity(int $level)
    {
    }
    public function getVerbosity() : int
    {
        return self::VERBOSITY_QUIET;
    }
    public function isQuiet() : bool
    {
        return \true;
    }
    public function isVerbose() : bool
    {
        return \false;
    }
    public function isVeryVerbose() : bool
    {
        return \false;
    }
    public function isDebug() : bool
    {
        return \false;
    }
    public function writeln(string|iterable $messages, int $options = self::OUTPUT_NORMAL)
    {
    }
    public function write(string|iterable $messages, bool $newline = \false, int $options = self::OUTPUT_NORMAL)
    {
    }
}
