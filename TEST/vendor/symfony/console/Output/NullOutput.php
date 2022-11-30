<?php

namespace _HumbugBox9658796bb9f0\Symfony\Component\Console\Output;

use _HumbugBox9658796bb9f0\Symfony\Component\Console\Formatter\NullOutputFormatter;
use _HumbugBox9658796bb9f0\Symfony\Component\Console\Formatter\OutputFormatterInterface;
class NullOutput implements OutputInterface
{
    private $formatter;
    public function setFormatter(OutputFormatterInterface $formatter)
    {
    }
    public function getFormatter()
    {
        if ($this->formatter) {
            return $this->formatter;
        }
        return $this->formatter = new NullOutputFormatter();
    }
    public function setDecorated(bool $decorated)
    {
    }
    public function isDecorated()
    {
        return \false;
    }
    public function setVerbosity(int $level)
    {
    }
    public function getVerbosity()
    {
        return self::VERBOSITY_QUIET;
    }
    public function isQuiet()
    {
        return \true;
    }
    public function isVerbose()
    {
        return \false;
    }
    public function isVeryVerbose()
    {
        return \false;
    }
    public function isDebug()
    {
        return \false;
    }
    public function writeln($messages, int $options = self::OUTPUT_NORMAL)
    {
    }
    public function write($messages, bool $newline = \false, int $options = self::OUTPUT_NORMAL)
    {
    }
}
